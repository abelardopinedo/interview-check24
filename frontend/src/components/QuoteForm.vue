<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import type { RequestQuote } from '../types/RequestQuote';

const emit = defineEmits<{
  (e: 'submit', payload: RequestQuote): void;
  (e: 'error', errors: string[]): void;
}>();

const currentStep = ref(1);
const transitionName = ref('slide-next');

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

watch(formState, (newState) => {
  sessionStorage.setItem('quoteFormState', JSON.stringify(newState));
}, { deep: true });

const nextStep = () => {
  const errors: string[] = [];
  if (currentStep.value === 1 && !formState.driver_birthday) {
    errors.push('La fecha de nacimiento es obligatoria.');
  } else if (currentStep.value === 2 && !formState.car_type) {
    errors.push('El tipo de coche es obligatorio.');
  }

  if (errors.length > 0) {
    emit('error', errors);
    return;
  }

  emit('error', []); // Clear errors on progress
  if (currentStep.value < 3) {
    transitionName.value = 'slide-next';
    currentStep.value++;
  } else {
    handleSubmit();
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    transitionName.value = 'slide-prev';
    currentStep.value--;
  }
};

const handleSubmit = () => {
  const errors: string[] = [];
  if (!formState.car_use) {
    errors.push('El uso del coche es obligatorio.');
  }

  if (errors.length > 0) {
    emit('error', errors);
    return;
  }

  emit('submit', { ...formState });
};

// Reset step to 1 when component is mounted or as needed
const resetStep = () => {
  currentStep.value = 1;
};

defineExpose({ resetStep });
</script>

<template>
  <div class="quote-form-container glass-panel">
    <div class="step-indicator">
      <div v-for="step in 3" :key="step" :class="['step-dot', { active: currentStep === step, completed: currentStep > step }]"></div>
    </div>
    
    <h2>Comparador de seguros</h2>
    
    <div class="step-content">
      <Transition :name="transitionName">
        <div :key="currentStep" class="form-step">
          <p class="subtitle" v-if="currentStep === 1">Paso 1: Tu fecha de nacimiento</p>
          <p class="subtitle" v-if="currentStep === 2">Paso 2: Tipo de coche</p>
          <p class="subtitle" v-if="currentStep === 3">Paso 3: Uso del coche</p>

          <form @submit.prevent="nextStep" class="quote-form" novalidate>
            
            <div v-if="currentStep === 1" class="form-group">
              <label for="driverBirthday">Fecha de nacimiento</label>
              <input 
                id="driverBirthday"
                type="date" 
                name="driverBirthday" 
                v-model="formState.driver_birthday" 
                required 
              />
            </div>

            <div v-if="currentStep === 2" class="form-group">
              <label for="carType">Tipo de coche</label>
              <select id="carType" name="carType" v-model="formState.car_type" required>
                <option value="" disabled>Seleccionar tipo de coche</option>
                <option value="Turismo">Turismo</option>
                <option value="SUV">SUV</option>
                <option value="Compacto">Compacto</option>
              </select>
            </div>

            <div v-if="currentStep === 3" class="form-group">
              <label>Uso del coche</label>
              <div class="radio-group">
                <label :class="['radio-label', { selected: formState.car_use === 'Privado' }]">
                  <input type="radio" name="carUse" value="Privado" v-model="formState.car_use" required>
                  <span>Privado</span>
                </label>
                <label :class="['radio-label', { selected: formState.car_use === 'Comercial' }]">
                  <input type="radio" name="carUse" value="Comercial" v-model="formState.car_use" required>
                  <span>Comercial</span>
                </label>
              </div>
            </div>

            <div class="form-navigation">
              <button v-if="currentStep > 1" type="button" @click="prevStep" class="nav-btn secondary">
                Anterior
              </button>
              <button type="submit" class="nav-btn primary">
                {{ currentStep === 3 ? 'Calcular' : 'Continuar' }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
.quote-form-container {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  padding: 2rem;
  box-sizing: border-box;
  overflow: hidden; /* Prevent horizontal scroll during transitions */
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

input:not([type="radio"]), select {
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

input:not([type="radio"]):focus, select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px var(--primary-color-alpha);
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  background-color: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  transition: all 0.2s ease;
}

.radio-label:hover {
  background-color: rgba(255, 255, 255, 0.08);
  border-color: var(--primary-color);
}

.radio-label.selected {
  background-color: var(--primary-color-alpha);
  border-color: var(--primary-color);
}

/* Custom Radio Button Styling */
.radio-label input[type="radio"] {
  appearance: none;
  width: 20px;
  height: 20px;
  border: 2px solid var(--border-color);
  border-radius: 50%;
  margin: 0;
  display: grid;
  place-content: center;
  transition: all 0.2s ease;
  cursor: pointer;
}

.radio-label input[type="radio"]::before {
  content: "";
  width: 10px;
  height: 10px;
  border-radius: 50%;
  transform: scale(0);
  transition: 120ms transform ease-in-out;
  box-shadow: inset 1em 1em var(--primary-color);
}

.radio-label input[type="radio"]:checked {
  border-color: var(--primary-color);
}

.radio-label input[type="radio"]:checked::before {
  transform: scale(1);
}

.step-indicator {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.step-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: var(--border-color);
  transition: all 0.3s ease;
}

.step-dot.active {
  background-color: var(--primary-color);
  transform: scale(1.2);
}

.step-dot.completed {
  background-color: var(--success-color, #10b981);
}

.form-navigation {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.nav-btn {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.nav-btn.primary {
  background-color: var(--primary-color);
  color: white;
}

.nav-btn.primary:hover {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.nav-btn.secondary {
  background-color: rgba(255, 255, 255, 0.05);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.nav-btn.secondary:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* iOS-style Lateral Transitions */
.slide-next-enter-active,
.slide-next-leave-active,
.slide-prev-enter-active,
.slide-prev-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Going Forward: Next step slides in from right, current step slides out to left */
.slide-next-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.slide-next-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

/* Going Backward: Prev step slides in from left, current step slides out to right */
.slide-prev-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}
.slide-prev-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

.step-content {
  display: grid;
  grid-template-areas: "stack";
  overflow: hidden;
}

.form-step {
  grid-area: stack;
  width: 100%;
}
@media (max-width: 768px) {
  .quote-form-container {
    padding: 1.5rem;
  }

  h2 {
    font-size: 1.5rem;
  }

  .nav-btn {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }

  .radio-label {
    padding: 0.75rem;
    font-size: 0.9rem;
  }
}
</style>
