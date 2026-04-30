import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import QuoteList from '../QuoteList.vue'

describe('QuoteList.vue', () => {
  it('renders an empty state when no quotes are provided', () => {
    const wrapper = mount(QuoteList, {
      props: {
        quotes: [],
        hasSearched: true
      }
    })

    expect(wrapper.text()).toContain('No hay ofertas disponibles.')
  })

  it('renders quote cards correctly', () => {
    const quotes = [
      { provider: 'Provider A', price: 250, currency: 'EUR' },
      { provider: 'Provider B', price: 300, currency: 'EUR', discount_price: 285 }
    ]

    const wrapper = mount(QuoteList, {
      props: { quotes, hasSearched: true }
    })

    // Check if the correct number of quote rows are rendered
    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(2)

    // Check first row content
    expect(rows[0].text()).toContain('Provider A')
    expect(rows[0].text()).toContain('250 EUR')

    // Check second row content (has discount)
    expect(rows[1].text()).toContain('Provider B')
    expect(rows[1].text()).toContain('285 EUR') // Display the discounted price primarily
    expect(rows[1].find('.discount-badge').exists()).toBe(true)
    expect(rows[1].text()).toContain('300') // Display the old price somewhere
  })

  it('sorts quotes by final price when clicking the header', async () => {
    const quotes = [
      { provider: 'Provider B', price: 300, currency: 'EUR', discount_price: 285 },
      { provider: 'Provider A', price: 250, currency: 'EUR' },
      { provider: 'Provider C', price: 350, currency: 'EUR' }
    ]

    const wrapper = mount(QuoteList, {
      props: { quotes, hasSearched: true }
    })

    // By default, it should be sorted ascending by final price: A(250), B(285), C(350)
    let rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('Provider A')
    expect(rows[1].text()).toContain('Provider B')
    expect(rows[2].text()).toContain('Provider C')

    // Click the header to sort descending
    await wrapper.find('.sortable-header').trigger('click')

    // Now it should be sorted descending: C(350), B(285), A(250)
    rows = wrapper.findAll('tbody tr')
    expect(rows[0].text()).toContain('Provider C')
    expect(rows[1].text()).toContain('Provider B')
    expect(rows[2].text()).toContain('Provider A')
  })
})
