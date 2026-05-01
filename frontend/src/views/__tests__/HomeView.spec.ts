import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import HomeView from '../HomeView.vue'
import { insuranceApi } from '../../api/insurance'

vi.mock('../../api/insurance', () => ({
  insuranceApi: {
    calculateQuotes: vi.fn()
  }
}))

describe('HomeView.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('shows loading state and fetches quotes on form submit', async () => {
    vi.mocked(insuranceApi.calculateQuotes).mockResolvedValueOnce([
      { provider: 'Provider A', price: 100, currency: 'EUR' }
    ])

    const wrapper = mount(HomeView)
    
    // Simulate form submission emitted by QuoteForm
    const quoteForm = wrapper.findComponent({ name: 'QuoteForm' })
    await quoteForm.vm.$emit('submit', { driver_birthday: '1990-01-01', car_type: 'SUV', car_use: 'Privado' })

    // Check loading state
    expect(wrapper.find('.loading-spinner').exists()).toBe(true)

    await flushPromises()

    // Loading should be gone
    expect(wrapper.find('.loading-spinner').exists()).toBe(false)
    
    // QuoteList should receive the quotes
    const quoteList = wrapper.findComponent({ name: 'QuoteList' })
    expect(quoteList.props('quotes')).toHaveLength(1)
    expect(quoteList.props('quotes')[0].provider).toBe('Provider A')
  })

  it('displays validation errors on 422 response', async () => {
    vi.mocked(insuranceApi.calculateQuotes).mockRejectedValueOnce({
      response: {
        status: 422,
        data: {
          detail: 'Validation errors',
          violations: [
            { propertyPath: 'driver_birthday', title: 'Invalid date' }
          ]
        }
      }
    })

    const wrapper = mount(HomeView)
    
    const quoteForm = wrapper.findComponent({ name: 'QuoteForm' })
    await quoteForm.vm.$emit('submit', { driver_birthday: 'invalid', car_type: 'SUV', car_use: 'Privado' })

    await flushPromises()

    // Error message should be visible
    expect(wrapper.text()).toContain('driver_birthday: Invalid date')
    expect(wrapper.find('.error-alert').exists()).toBe(true)
  })
})
