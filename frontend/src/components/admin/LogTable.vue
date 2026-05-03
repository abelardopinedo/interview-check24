<script setup lang="ts">
import type { LogSummary } from '@/types/admin';

defineProps<{
  logs: LogSummary[]
}>();

defineEmits(['select']);

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleString();
};

const getStatusClass = (code: number | null) => {
  if (code === null) return 'status-warn';
  if (code >= 200 && code < 300) return 'status-ok';
  if (code >= 400 && code < 500) return 'status-warn';
  return 'status-error';
};
</script>

<template>
  <div class="log-table-container glass-panel">
    <table class="log-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Método</th>
          <th>Endpoint</th>
          <th>Estado</th>
          <th>Latencia</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="log in logs" :key="log.id">
          <td>{{ log.id }}</td>
          <td><span class="method-badge">{{ log.httpMethod }}</span></td>
          <td><code>{{ log.endpoint }}</code></td>
          <td>
            <span :class="['status-badge', getStatusClass(log.statusCode)]">
              {{ log.statusCode }}
            </span>
          </td>
          <td>{{ log.latency }}ms</td>
          <td>{{ formatDate(log.createdAt) }}</td>
          <td>
            <button class="btn-icon" @click="$emit('select', log.id)">
              🔍
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.log-table-container {
  overflow-x: auto;
  border-radius: 1rem;
}

.log-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.log-table th {
  padding: 1.2rem 1rem;
  font-size: 0.9rem;
  color: var(--text-muted);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-weight: 600;
}

.log-table td {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.log-table tr:hover {
  background: rgba(255, 255, 255, 0.02);
}

.method-badge {
  font-size: 0.75rem;
  font-weight: 800;
  padding: 0.2rem 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.status-badge {
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 1rem;
  font-size: 0.85rem;
}

.status-ok { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.status-warn { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.status-error { background: rgba(239, 68, 68, 0.2); color: #f87171; }

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  transition: transform 0.2s;
}

.btn-icon:hover {
  transform: scale(1.2);
}

code {
  background: rgba(0, 0, 0, 0.2);
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  font-family: monospace;
}
</style>
