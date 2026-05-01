<script setup lang="ts">
import type { Quote } from '../types/Quote';
import { ref, computed } from 'vue';

const props = defineProps<{
  quotes: Quote[];
  hasSearched: boolean;
}>();

const getFinalPrice = (quote: Quote): number => {
  return quote.discount_price ?? quote.price ?? 0;
};

const cheapestPrice = computed<number | null>(() => {
  if (!props.quotes || props.quotes.length === 0) return null;
  const prices = props.quotes.map(getFinalPrice);
  return prices.length > 0 ? Math.min(...prices) : null;
});

const sortAscending = ref(true);

const toggleSort = () => {
  sortAscending.value = !sortAscending.value;
};

const sortedQuotes = computed<Quote[]>(() => {
  // Create a copy so we don't mutate the prop directly
  return [...props.quotes].sort((a, b) => {
    const priceA = getFinalPrice(a);
    const priceB = getFinalPrice(b);
    return sortAscending.value ? priceA - priceB : priceB - priceA;
  });
});
</script>

<template>
  <div class="quote-list-container" v-if="hasSearched">
    <div v-if="quotes.length === 0" class="empty-state glass-panel">
      <p>No hay ofertas disponibles.</p>
    </div>

    <div v-else>
      <!-- Table View (Desktop/Tablet) -->
      <div class="table-container glass-panel desktop-only">
        <table class="quotes-table">
          <thead>
            <tr>
              <th>Proveedor</th>
              <th>Precio Base</th>
              <th class="sortable-header" @click="toggleSort">
                Precio Final
                <span class="sort-icon">{{ sortAscending ? '↑' : '↓' }}</span>
              </th>
              <th>Descuento</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(quote, index) in sortedQuotes" :key="index" :class="{ 'cheapest-row': getFinalPrice(quote) === cheapestPrice }">
              <td class="provider-name">
                <div class="provider-name-content">
                  {{ quote.provider }}
                  <span v-if="getFinalPrice(quote) === cheapestPrice" class="best-price-badge">¡Mejor Precio!</span>
                </div>
              </td>
              <td :class="{ 'old-price-td': quote.discount_price }">
                <span>{{ quote.price }} {{ quote.currency }}</span>
              </td>
              <td class="final-price" :class="{ 'cheapest-price-text': getFinalPrice(quote) === cheapestPrice }">
                <span v-if="quote.discount_price">{{ quote.discount_price }} {{ quote.currency }}</span>
                <span v-else>{{ quote.price }} {{ quote.currency }}</span>
              </td>
              <td>
                <span v-if="quote.discount_price" class="discount-badge" :class="{ 'muted-discount': getFinalPrice(quote) !== cheapestPrice }">Campaña Activa</span>
                <span v-else class="text-muted">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Card View (Mobile) -->
      <div class="mobile-cards mobile-only">
        <div class="mobile-sort-toggle glass-panel" @click="toggleSort">
          Ordenar por precio: {{ sortAscending ? 'Menor a Mayor ↑' : 'Mayor a Menor ↓' }}
        </div>
        
        <div 
          v-for="(quote, index) in sortedQuotes" 
          :key="'card-' + index" 
          class="quote-card glass-panel"
          :class="{ 'cheapest-card': getFinalPrice(quote) === cheapestPrice }"
        >
          <div class="card-header">
            <span class="card-provider">{{ quote.provider }}</span>
            <span v-if="getFinalPrice(quote) === cheapestPrice" class="best-price-badge">¡Mejor Precio!</span>
          </div>
          
          <div class="card-body">
            <div class="card-row">
              <span class="row-label">Precio Base:</span>
              <span :class="{ 'old-price-td': quote.discount_price }">{{ quote.price }} {{ quote.currency }}</span>
            </div>
            <div class="card-row">
              <span class="row-label">Descuento:</span>
              <span v-if="quote.discount_price" class="discount-badge">Campaña 5%</span>
              <span v-else>-</span>
            </div>
            <div class="card-divider"></div>
            <div class="card-row final">
              <span class="row-label">Precio Final:</span>
              <span class="final-price" :class="{ 'cheapest-price-text': getFinalPrice(quote) === cheapestPrice }">
                {{ getFinalPrice(quote) }} {{ quote.currency }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quote-list-container {
  width: 100%;
  max-width: 1000px;
  margin: 2rem auto;
}

.empty-state {
  padding: 3rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 1.1rem;
}

.table-container {
  overflow-x: auto;
  padding: 1rem;
}

.quotes-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.quotes-table th {
  padding: 1rem;
  border-bottom: 2px solid var(--border-color);
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  white-space: nowrap; /* Prevent any header from wrapping */
}

.quotes-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--panel-border);
  vertical-align: middle;
}

/* Fix widths for consistent alignment */
.quotes-table th:nth-child(2),
.quotes-table td:nth-child(2) {
  width: 150px; /* Increased from 110px */
  text-align: right;
  white-space: nowrap;
}

.quotes-table th:nth-child(3),
.quotes-table td:nth-child(3) {
  width: 140px;
  text-align: right;
}

.quotes-table th:nth-child(4),
.quotes-table td:nth-child(4) {
  width: 180px;
  text-align: right;
}

.quotes-table tbody tr:last-child td {
  border-bottom: none;
}

.quotes-table tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.03);
}

.provider-name {
  font-weight: 600;
  color: var(--text-color);
  text-transform: capitalize;
  max-width: 350px;
  white-space: normal;
  line-height: 1.4;
}

.provider-name-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.old-price-td {
  color: var(--error-color);
  text-decoration: line-through;
  font-size: 0.9rem;
  white-space: nowrap;
}

.final-price {
  font-weight: 700;
  color: var(--primary-color);
  font-size: 1.1rem;
  white-space: nowrap;
}

.cheapest-row {
  background-color: rgba(16, 185, 129, 0.05);
}

.cheapest-row:hover {
  background-color: rgba(16, 185, 129, 0.1) !important;
}

.cheapest-price-text {
  color: var(--success-color);
  font-size: 1.25rem;
}

.best-price-badge {
  background-color: var(--success-color);
  color: white;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.discount-badge {
  background-color: var(--success-color);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: bold;
  white-space: nowrap;
}

.muted-discount {
  background-color: transparent;
  color: var(--text-muted);
  border: 1px solid var(--panel-border);
}

.text-muted {
  color: var(--text-muted);
}

.sortable-header {
  cursor: pointer;
  user-select: none;
  transition: color 0.2s;
}

.sortable-header:hover {
  color: var(--primary-color);
}

.sort-icon {
  display: inline-block;
  margin-left: 0.25rem;
  font-weight: bold;
}


.desktop-only {
  display: block !important;
}

.mobile-only {
  display: none !important;
}

@media (max-width: 768px) {
  .desktop-only {
    display: none !important;
  }
  
  .mobile-only {
    display: flex !important;
    flex-direction: column;
  }

  .quote-list-container {
    padding: 0 1rem;
    margin: 1rem auto;
  }
}

/* Card View Styles */
.mobile-cards {
  display: flex;
  flex-direction: column;
  gap: 1.5rem; /* Increased spacing between cards */
}

.mobile-sort-toggle {
  padding: 0.875rem;
  text-align: center;
  font-weight: 600;
  color: var(--primary-color);
  cursor: pointer;
  font-size: 0.9rem;
  margin-bottom: 1rem; /* Space between toggle and first card */
}

.quote-card {
  padding: 0.75rem 1rem; /* Even more compact padding */
  display: flex;
  flex-direction: column;
  gap: 0.5rem; /* Reduced internal gap */
}

.cheapest-card {
  border: 1px solid var(--success-color);
  background-color: rgba(16, 185, 129, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-provider {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-color);
  text-transform: capitalize;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 0.4rem; /* Tightened body spacing */
}

.card-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-row.final {
  margin-top: 0.5rem;
}

.row-label {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.card-divider {
  height: 1px;
  background-color: var(--panel-border);
  margin: 0.25rem 0;
}
</style>
