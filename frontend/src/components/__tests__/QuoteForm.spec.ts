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

  it('shows error for underage user (age < 18)', async () => {
    const wrapper = mount(QuoteForm)
    
    // Set birthday to 10 years ago
    const tenYearsAgo = new Date()
    tenYearsAgo.setFullYear(tenYearsAgo.getFullYear() - 10)
    const dateString = tenYearsAgo.toISOString().split('T')[0]
    
    await wrapper.find('input[type="date"][name="driverBirthday"]').setValue(dateString)
    await wrapper.find('form').trigger('submit')
    
    // Check if error was emitted
    expect(wrapper.emitted()).toHaveProperty('error')
    const errorEvent = wrapper.emitted('error')
    expect(errorEvent![0][0]).toContain('Debes tener al menos 18 años para solicitar un seguro.')
    
    // Should still be on step 1
    expect(wrapper.find('p.subtitle').text()).toContain('Paso 1')
  })

  it('shows error for overage user (age >= 123)', async () => {
    const wrapper = mount(QuoteForm)
    
    // Set birthday to 130 years ago
    const oldPerson = new Date()
    oldPerson.setFullYear(oldPerson.getFullYear() - 130)
    const dateString = oldPerson.toISOString().split('T')[0]
    
    await wrapper.find('input[type="date"][name="driverBirthday"]').setValue(dateString)
    await wrapper.find('form').trigger('submit')
    
    // Check if error was emitted
    expect(wrapper.emitted()).toHaveProperty('error')
    const errorEvent = wrapper.emitted('error')
    expect(errorEvent![0][0]).toContain('Por favor, introduce una edad válida (menor de 123 años).')
    
    // Should still be on step 1
    expect(wrapper.find('p.subtitle').text()).toContain('Paso 1')
  })

  it('shows error for future birth date', async () => {
    const wrapper = mount(QuoteForm)
    
    // Set birthday to tomorrow
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    const dateString = tomorrow.toISOString().split('T')[0]
    
    await wrapper.find('input[type="date"][name="driverBirthday"]').setValue(dateString)
    await wrapper.find('form').trigger('submit')
    
    // Check if error was emitted
    expect(wrapper.emitted()).toHaveProperty('error')
    const errorEvent = wrapper.emitted('error')
    expect(errorEvent![0][0]).toContain('La fecha de nacimiento no puede estar en el futuro.')
    
    // Should still be on step 1
    expect(wrapper.find('p.subtitle').text()).toContain('Paso 1')
  })
})
