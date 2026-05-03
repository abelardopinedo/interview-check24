<script setup lang="ts">
import type { PerformanceStats } from '@/types/admin';

defineProps<{
  stats: PerformanceStats
}>();
</script>

<template>
  <div class="stats-grid">
    <div v-for="provider in stats" :key="provider.providerName" class="stat-card glass-panel">
      <div class="provider-header">
        <h3>{{ provider.providerName }}</h3>
        <span :class="['status-indicator', provider.errorCount > 0 ? 'warning' : 'healthy']"></span>
      </div>
      
      <div class="metrics">
        <div class="metric">
          <span class="label">Latencia Media</span>
          <span class="value">{{ Math.round(provider.avgLatency) }}ms</span>
        </div>
        <div class="metric">
          <span class="label">Éxito</span>
          <span class="value success">{{ ((provider.totalCount - provider.errorCount) / provider.totalCount * 100).toFixed(1) }}%</span>
        </div>
        <div class="metric">
          <span class="label">Total Llamadas</span>
          <span class="value">{{ provider.totalCount }}</span>
        </div>
      </div>

      <div class="latency-bar-container">
        <div 
          class="latency-bar" 
          :style="{ width: Math.min(provider.avgLatency / 100, 100) + '%' }"
          :class="{ 'slow': provider.avgLatency > 5000 }"
        ></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.provider-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.provider-header h3 {
  margin: 0;
  text-transform: capitalize;
  font-size: 1.2rem;
}

.status-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  box-shadow: 0 0 10px currentColor;
}

.healthy { color: #10b981; background: #10b981; }
.warning { color: #f59e0b; background: #f59e0b; }

.metrics {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
}

.metric {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.label {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.value {
  font-weight: 700;
  font-size: 1.1rem;
}

.success { color: #10b981; }

.latency-bar-container {
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
}

.latency-bar {
  height: 100%;
  background: var(--primary-color);
  transition: width 0.5s ease;
}

.latency-bar.slow {
  background: var(--error-color);
}
</style>
