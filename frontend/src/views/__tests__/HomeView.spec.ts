import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import HomeView from '../HomeView.vue'
import axios from 'axios'

vi.mock('axios')
const mockedAxios = vi.mocked(axios, true)

describe('HomeView.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('shows loading state and fetches quotes on form submit', async () => {
    mockedAxios.post.mockResolvedValueOnce({
      data: [
        { provider: 'Provider A', price: 100, currency: 'EUR' }
      ]
    })

    const wrapper = mount(HomeView)
    
    // Simulate form submission emitted by QuoteForm
    const quoteForm = wrapper.findComponent({ name: 'QuoteForm' })
    await quoteForm.vm.$emit('submit', { driverBirthday: '1990-01-01', carType: 'SUV', carUse: 'Privado' })

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
    mockedAxios.post.mockRejectedValueOnce({
      response: {
        status: 422,
        data: {
          detail: 'Validation errors',
          violations: [
            { propertyPath: 'driverBirthday', title: 'Invalid date' }
          ]
        }
      }
    })

    const wrapper = mount(HomeView)
    
    const quoteForm = wrapper.findComponent({ name: 'QuoteForm' })
    await quoteForm.vm.$emit('submit', { driverBirthday: 'invalid', carType: 'SUV', carUse: 'Privado' })

    await flushPromises()

    // Error message should be visible
    expect(wrapper.text()).toContain('driverBirthday: Invalid date')
    expect(wrapper.find('.error-alert').exists()).toBe(true)
  })
})
