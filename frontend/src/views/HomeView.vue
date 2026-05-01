<script setup lang="ts">
import { ref } from 'vue';
import { insuranceApi } from '../api/insurance';
import QuoteForm from '../components/QuoteForm.vue';
import QuoteList from '../components/QuoteList.vue';
import type { Quote } from '../types/Quote';
import type { RequestQuote } from '../types/RequestQuote';

const quotes = ref<Quote[]>([]);
const isLoading = ref(false);
const hasSearched = ref(false);
const formErrors = ref<string[]>([]);
const quoteFormRef = ref<any>(null);

const calculateQuotes = async (payload: RequestQuote) => {
  isLoading.value = true;
  hasSearched.value = true;
  formErrors.value = [];
  quotes.value = [];

  try {
    quotes.value = await insuranceApi.calculateQuotes(payload);
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
      const violations = error.response.data.violations;
      if (violations && Array.isArray(violations)) {
        formErrors.value = violations.map((v: any) => `${v.propertyPath}: ${v.title}`);
      } else {
        formErrors.value = ['Ocurrio un error al obtener las cotizaciones.'];
      }
    } else {
      formErrors.value = ['Ocurrio un error al obtener las cotizaciones.'];
    }
  } finally {
    isLoading.value = false;
    // When done, reset the form to step 1
    if (quoteFormRef.value) {
      quoteFormRef.value.resetStep();
    }
  }
};
</script>

<template>
  <div class="view-container">
    <header class="app-header">
      <h1>Check24</h1>
    </header>

    <div v-if="formErrors.length > 0" class="error-alert glass-panel">
      <h3>Error:</h3>
      <ul>
        <li v-for="error in formErrors" :key="error">{{ error }}</li>
      </ul>
    </div>

    <!-- The form disappears during loading -->
    <QuoteForm 
      v-if="!isLoading"
      ref="quoteFormRef"
      @submit="calculateQuotes" 
      @error="(errors) => formErrors = errors" 
    />

    <div v-if="isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Obteniendo resultados de nuestros proveedores...</p>
    </div>

    <!-- Results show when not loading and search is performed -->
    <QuoteList v-if="!isLoading && hasSearched" :quotes="quotes" :has-searched="hasSearched" />
  </div>
</template>

<style scoped>
.view-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.app-header {
  text-align: center;
  margin-bottom: 1rem;
}

.app-header h1 {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(to right, #60a5fa, #a78bfa);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.error-alert {
  background-color: rgba(239, 68, 68, 0.1);
  border-left: 4px solid var(--error-color);
  padding: 1rem 1.5rem;
  max-width: 500px;
  margin: 0 auto;
}

.error-alert h3 {
  color: #fca5a5;
  margin-top: 0;
  margin-bottom: 0.5rem;
}

.error-alert ul {
  margin: 0;
  padding-left: 1.5rem;
  color: #fee2e2;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 0;
  gap: 1.5rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-left-color: var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
@media (max-width: 768px) {
  .view-container {
    padding: 1rem 0.5rem;
    gap: 1rem;
  }

  .app-header h1 {
    font-size: 1.75rem;
  }

  .loading-state {
    padding: 2rem 0;
  }
}
</style>
