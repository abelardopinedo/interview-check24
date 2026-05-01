<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { adminApi } from '../../api/admin';
import PerformanceStats from '../../components/admin/PerformanceStats.vue';
import LogTable from '../../components/admin/LogTable.vue';
import LogWaterfall from '../../components/admin/LogWaterfall.vue';

const logs = ref([]);
const stats = ref([]);
const selectedLog = ref<any>(null);
const isLoading = ref(true);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [logsData, statsData] = await Promise.all([
      adminApi.getLogs({ limit: 5 }),
      adminApi.getPerformanceStats()
    ]);
    logs.value = logsData.data;
    stats.value = statsData;
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    isLoading.value = false;
  }
};

const selectLog = async (id: number) => {
  try {
    selectedLog.value = await adminApi.getLogDetails(id);
    setTimeout(() => {
      document.querySelector('.details-section')?.scrollIntoView({ behavior: 'smooth' });
    }, 100);
  } catch (error) {
    console.error('Error fetching log details:', error);
  }
};

onMounted(fetchData);
</script>

<template>
  <div class="dashboard-view">
    <header class="page-header">
      <div class="title-area">
        <h1>Resumen General</h1>
        <p>Vista rápida del estado del sistema y logs recientes.</p>
      </div>
    </header>

    <div v-if="isLoading && !logs.length" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <template v-else>
      <section class="stats-section">
        <div class="section-header">
          <h2>Rendimiento de Proveedores</h2>
        </div>
        <PerformanceStats :stats="stats" />
      </section>

      <section class="logs-section">
        <div class="section-header">
          <h2>Últimos Logs</h2>
        </div>
        <LogTable :logs="logs" @select="selectLog" />
      </section>

      <section v-if="selectedLog" class="details-section glass-panel">
        <button class="close-btn" @click="selectedLog = null">✕</button>
        <LogWaterfall :log="selectedLog" />
      </section>
    </template>
  </div>
</template>

<style scoped>
.dashboard-view {
  display: flex;
  flex-direction: column;
  gap: 3rem;
}

.page-header {
  margin-bottom: 1rem;
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

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

h2 {
  font-size: 1.25rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0;
}

h2::before {
  content: '';
  width: 4px;
  height: 20px;
  background: var(--primary-color);
  border-radius: 2px;
}

.btn-link {
  color: var(--primary-color);
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
  transition: opacity 0.3s;
}

.btn-link:hover {
  opacity: 0.8;
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
