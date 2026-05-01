import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import QuoteForm from '../QuoteForm.vue'

describe('QuoteForm.vue', () => {
  it('renders correctly with all required inputs (step by step)', async () => {
    const wrapper = mount(QuoteForm)

    // Step 1: Birthday
    expect(wrapper.find('input[type="date"][name="driverBirthday"]').exists()).toBe(true)
    await wrapper.find('input[type="date"][name="driverBirthday"]').setValue('1990-05-15')
    await wrapper.find('form').trigger('submit') // Continue to step 2

    // Step 2: Car Type
    expect(wrapper.find('select[name="carType"]').exists()).toBe(true)
    await wrapper.find('select[name="carType"]').setValue('Turismo')
    await wrapper.find('form').trigger('submit') // Continue to step 3

    // Step 3: Car Use
    expect(wrapper.find('input[type="radio"][name="carUse"]').exists()).toBe(true)

    // Check for submit button
    expect(wrapper.find('button[type="submit"]').text()).toBe('Calcular')
  })

  it('emits "submit" event with correct payload after finishing steps', async () => {
    const wrapper = mount(QuoteForm)

    // Step 1
    await wrapper.find('input[type="date"][name="driverBirthday"]').setValue('1990-05-15')
    await wrapper.find('form').trigger('submit')

    // Step 2
    await wrapper.find('select[name="carType"]').setValue('Turismo')
    await wrapper.find('form').trigger('submit')

    // Step 3
    await wrapper.find('input[type="radio"][name="carUse"][value="Privado"]').setValue()
    await wrapper.find('form').trigger('submit')

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
