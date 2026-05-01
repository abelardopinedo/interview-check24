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

    <div v-else class="table-container glass-panel">
      <table class="quotes-table">
        <thead>
          <tr>
            <th>Proveedor</th>
            <th>Precio</th>
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
              <span v-if="quote.discount_price" class="discount-badge" :class="{ 'muted-discount': getFinalPrice(quote) !== cheapestPrice }">Campaña del 5%</span>
              <span v-else class="text-muted">-</span>
            </td>
          </tr>
        </tbody>
      </table>
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
}

.quotes-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--panel-border);
  vertical-align: middle;
}

/* Fix widths for consistent alignment */
.quotes-table th:nth-child(2),
.quotes-table td:nth-child(2) {
  width: 110px;
  text-align: right;
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
</style>
