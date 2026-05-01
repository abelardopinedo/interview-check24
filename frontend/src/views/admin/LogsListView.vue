<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import LogTable from '../../components/admin/LogTable.vue';
import LogWaterfall from '../../components/admin/LogWaterfall.vue';

const logs = ref([]);
const meta = ref({ page: 1, pages: 1, total: 0 });
const selectedLog = ref<any>(null);
const isLoading = ref(true);

// Filters
const searchQuery = ref('');
const statusFilter = ref('');
const sortBy = ref('recent');
const startDate = ref('');
const endDate = ref('');
const currentPage = ref(1);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/admin/logs', {
      params: {
        page: currentPage.value,
        query: searchQuery.value || undefined,
        status: statusFilter.value || undefined,
        sort: sortBy.value,
        startDate: startDate.value || undefined,
        endDate: endDate.value || undefined
      }
    });
    logs.value = response.data.data;
    meta.value = response.data.meta;
  } catch (error) {
    console.error('Error fetching logs:', error);
  } finally {
    isLoading.value = false;
  }
};

const selectLog = async (id: number) => {
  try {
    const response = await axios.get(`/api/admin/logs/${id}`);
    selectedLog.value = response.data;
    setTimeout(() => {
      document.querySelector('.details-section')?.scrollIntoView({ behavior: 'smooth' });
    }, 100);
  } catch (error) {
    console.error('Error fetching log details:', error);
  }
};

// Handle pagination
const setPage = (p: number) => {
  if (p >= 1 && p <= meta.value.pages) {
    currentPage.value = p;
    fetchData();
  }
};

// Debounce search
let searchTimeout: any;
watch([searchQuery, statusFilter, sortBy, startDate, endDate], () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    fetchData();
  }, 300);
});

onMounted(fetchData);
</script>

<template>
  <div class="logs-list-view">
    <header class="page-header">
      <div class="title-area">
        <h1>Explorador de Logs</h1>
        <p>Busca, filtra y analiza el historial completo de peticiones.</p>
      </div>
    </header>

    <section class="filters-bar glass-panel">
      <div class="filter-group search">
        <label>Buscar</label>
        <div class="input-with-icon">
          <span class="icon">🔍</span>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="ID de petición..."
          >
        </div>
      </div>

      <div class="filter-group">
        <label>Desde</label>
        <input v-model="startDate" type="date">
      </div>

      <div class="filter-group">
        <label>Hasta</label>
        <input v-model="endDate" type="date">
      </div>

      <div class="filter-group">
        <label>Estado</label>
        <select v-model="statusFilter">
          <option value="">Todos</option>
          <option value="200">200 OK</option>
          <option value="422">422 Validation Error</option>
          <option value="400">400 Bad Request</option>
          <option value="500">500 Server Error</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Ordenar por</label>
        <select v-model="sortBy">
          <option value="recent">Más recientes</option>
          <option value="latency">Mayor latencia</option>
        </select>
      </div>
    </section>

    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <template v-else>
      <section class="logs-table-section">
        <LogTable :logs="logs" @select="selectLog" />
        
        <div class="pagination">
          <button 
            class="btn-page" 
            :disabled="currentPage === 1"
            @click="setPage(currentPage - 1)"
          >Anterior</button>
          
          <span class="page-info">
            Página {{ currentPage }} de {{ meta.pages }} 
            <small>({{ meta.total }} resultados)</small>
          </span>

          <button 
            class="btn-page" 
            :disabled="currentPage === meta.pages"
            @click="setPage(currentPage + 1)"
          >Siguiente</button>
        </div>
      </section>

      <section v-if="selectedLog" class="details-section glass-panel">
        <button class="close-btn" @click="selectedLog = null">✕</button>
        <LogWaterfall :log="selectedLog" />
      </section>
    </template>
  </div>
</template>

<style scoped>
.logs-list-view {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.page-header h1 {
  font-size: 2rem;
  margin: 0;
  color: #ffffff;
}

.title-area p {
  color: var(--text-muted);
  margin-top: 0.5rem;
}

.filters-bar {
  display: flex;
  gap: 2rem;
  padding: 1.5rem;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group.search {
  flex: 1;
  min-width: 250px;
}

.filter-group label {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--text-muted);
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.input-with-icon .icon {
  position: absolute;
  left: 1rem;
  font-size: 1rem;
  opacity: 0.5;
}

input, select {
  width: 100%;
  padding: 0.75rem 1rem;
  padding-left: 2.75rem;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  color: white;
  font-size: 0.95rem;
  transition: border-color 0.3s;
}

select {
  padding-left: 1rem;
  min-width: 150px;
  cursor: pointer;
}

input:focus, select:focus {
  outline: none;
  border-color: var(--primary-color);
}

.pagination {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
}

.btn-page {
  padding: 0.6rem 1.2rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-page:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
  border-color: var(--primary-color);
}

.btn-page:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.9rem;
  color: var(--text-muted);
}

.details-section {
  position: relative;
  padding: 2rem;
  border: 1px solid rgba(96, 165, 250, 0.3);
  scroll-margin-top: 2rem;
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 1.5rem;
  cursor: pointer;
}

.loading-overlay {
  display: flex;
  justify-content: center;
  padding: 5rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-left-color: var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
