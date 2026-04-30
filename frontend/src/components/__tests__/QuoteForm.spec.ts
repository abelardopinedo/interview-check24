import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import QuoteForm from '../QuoteForm.vue'

describe('QuoteForm.vue', () => {
  it('renders correctly with all required inputs', () => {
    const wrapper = mount(QuoteForm)
    
    // Check for the presence of the three required inputs
    expect(wrapper.find('input[type="date"][name="driverBirthday"]').exists()).toBe(true)
    expect(wrapper.find('select[name="carType"]').exists()).toBe(true)
    expect(wrapper.find('input[type="radio"][name="carUse"]').exists()).toBe(true)
    
    // Check for submit button
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
  })

  it('emits "submit" event with correct payload on valid form submission', async () => {
    const wrapper = mount(QuoteForm)

    // Fill out the form
    await wrapper.find('input[type="date"][name="driverBirthday"]').setValue('1990-05-15')
    
    const carTypeSelect = wrapper.find('select[name="carType"]')
    await carTypeSelect.setValue('Turismo')
    
    const carUseRadio = wrapper.find('input[type="radio"][name="carUse"][value="Privado"]')
    await carUseRadio.setValue()

    // Submit form
    await wrapper.find('form').trigger('submit.prevent')

    // Check if the event was emitted
    expect(wrapper.emitted()).toHaveProperty('submit')
    
    // Check the payload of the emitted event
    const submitEvent = wrapper.emitted('submit')
    expect(submitEvent![0][0]).toEqual({
      driver_birthday: '1990-05-15',
      car_type: 'Turismo',
      car_use: 'Privado'
    })
  })
})
