<script setup lang="ts">
import { reactive, watch } from 'vue';
import type { RequestQuote } from '../types/RequestQuote';

const emit = defineEmits<{
  (e: 'submit', payload: RequestQuote): void;
  (e: 'error', errors: string[]): void;
}>();

// For 60+ fields, initializing a single state object is much more scalable.
// We also parse/stringify the entire object for sessionStorage.
const getInitialState = (): RequestQuote => {
  const stored = sessionStorage.getItem('quoteFormState');
  if (stored) {
    try {
      return JSON.parse(stored);
    } catch (e) {
      console.error('Failed to parse stored form state', e);
    }
  }
  return {
    driver_birthday: '',
    car_type: '',
    car_use: ''
  };
};

const formState = reactive<RequestQuote>(getInitialState());

// A single deep watcher replaces having 60+ individual watchers
watch(formState, (newState) => {
  sessionStorage.setItem('quoteFormState', JSON.stringify(newState));
}, { deep: true });

const handleSubmit = () => {
  const errors: string[] = [];
  
  if (!formState.driver_birthday) {
    errors.push('El campo Fecha de nacimiento es obligatorio.');
  }
  if (!formState.car_type) {
    errors.push('El campo Tipo de coche es obligatorio.');
  }
  if (!formState.car_use) {
    errors.push('El campo Uso del coche es obligatorio.');
  }

  if (errors.length > 0) {
    emit('error', errors);
    return;
  }

  // Clear errors locally (if we had them) and submit
  emit('submit', { ...formState });
};
</script>

<template>
  <div class="quote-form-container glass-panel">
    <h2>Comparador de seguros de coche</h2>
    <p class="subtitle">Ingresa tu informacion para comparar precios.</p>

    <form @submit.prevent="handleSubmit" class="quote-form" novalidate>
      
      <div class="form-group">
        <label for="driverBirthday">Fecha de nacimiento</label>
        <input 
          id="driverBirthday"
          type="date" 
          name="driverBirthday" 
          v-model="formState.driver_birthday" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="carType">Tipo de coche</label>
        <select id="carType" name="carType" v-model="formState.car_type" required>
          <option value="" disabled>Seleccionar tipo de coche</option>
          <option value="Turismo">Turismo</option>
          <option value="SUV">SUV</option>
          <option value="Compacto">Compacto</option>
        </select>
      </div>

      <div class="form-group">
        <label>Uso del coche</label>
        <div class="radio-group">
          <label class="radio-label">
            <input type="radio" name="carUse" value="Privado" v-model="formState.car_use" required>
            Privado
          </label>
          <label class="radio-label">
            <input type="radio" name="carUse" value="Comercial" v-model="formState.car_use" required>
            Comercial
          </label>
        </div>
      </div>

      <button type="submit" class="submit-btn">
        Calcular
      </button>
    </form>
  </div>
</template>

<style scoped>
.quote-form-container {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
}

h2 {
  margin-top: 0;
  margin-bottom: 0.5rem;
  color: var(--text-color);
  font-size: 1.75rem;
  font-weight: 600;
}

.subtitle {
  color: var(--text-muted);
  margin-bottom: 2rem;
  font-size: 0.9rem;
}

.quote-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-weight: 500;
  font-size: 0.9rem;
  color: var(--text-color);
}

input, select {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-color);
  background-color: var(--input-bg);
  color: var(--text-color);
  font-size: 1rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
  appearance: none;
}

input:focus, select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px var(--primary-color-alpha);
}

.submit-btn {
  margin-top: 1rem;
  padding: 0.875rem 1.5rem;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.submit-btn:active:not(:disabled) {
  transform: translateY(1px);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Custom dropdown arrow for selects */
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1.2rem;
  padding-right: 2.5rem;
}

.radio-group {
  display: flex;
  gap: 1.5rem;
  padding: 0.5rem 0;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 400;
  cursor: pointer;
}

.radio-label input[type="radio"] {
  width: auto;
  appearance: auto;
  padding: 0;
  margin: 0;
}
</style>
