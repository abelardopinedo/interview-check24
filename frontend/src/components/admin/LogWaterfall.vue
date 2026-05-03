<script setup lang="ts">
import { computed, ref } from 'vue';
import type { LogDetail } from '@/types/admin';

const props = defineProps<{
  log: LogDetail
}>();

const activePayload = ref<{ title: string; content: string } | null>(null);

const maxLatency = computed(() => {
  return Math.max(props.log.latency ?? 0, ...props.log.providerLogs.map(p => p.latency ?? 0));
});

const formatPayload = (payload: string | null) => {
  if (!payload) return 'Sin datos';
  
  // Try JSON
  try {
    const obj = JSON.parse(payload);
    return JSON.stringify(obj, null, 2);
  } catch {
    // Check if XML
    if (payload.trim().startsWith('<')) {
      return formatXml(payload);
    }
    return payload;
  }
};

const formatXml = (xml: string) => {
  let formatted = '';
  const reg = /(>)(<)(\/*)/g;
  xml = xml.replace(reg, '$1\n$2$3');
  let pad = 0;
  xml.split('\n').forEach((node) => {
    let indent = 0;
    if (node.match(/.+<\/\w[^>]*>$/)) {
      indent = 0;
    } else if (node.match(/^<\/\w/)) {
      if (pad !== 0) pad -= 1;
    } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
      indent = 1;
    } else {
      indent = 0;
    }

    formatted += '  '.repeat(pad) + node + '\n';
    pad += indent;
  });
  return formatted.trim();
};

const openPayload = (title: string, content: string) => {
  activePayload.value = { title, content: formatPayload(content) };
};
</script>

<template>
  <div class="waterfall-container">
    <div class="waterfall-header">
      <h2>Waterfall de Proveedores - Log #{{ log.id }}</h2>
      <div class="global-actions">
        <button class="btn-primary" @click="openPayload('Petición Original', log.requestPayload)">
          Request
        </button>
        <button class="btn-primary" @click="openPayload('Respuesta Final', log.responsePayload)">
          Response
        </button>
      </div>
    </div>
    
    <div class="timeline">
      <div class="timeline-header">
        <span class="label">Total ({{ log.latency ?? 0 }}ms)</span>
        <div class="full-bar-container">
          <div class="bar total-bar" :style="{ width: ((log.latency ?? 0) / maxLatency * 100) + '%' }"></div>
        </div>
      </div>

      <div v-for="pLog in log.providerLogs" :key="pLog.providerName" class="provider-row">
        <div class="provider-info">
          <span class="name">{{ pLog.providerName }}</span>
          <span class="ms">{{ pLog.latency ?? 0 }}ms</span>
        </div>
        <div class="bar-container">
          <div 
            class="bar provider-bar" 
            :style="{ width: ((pLog.latency ?? 0) / maxLatency * 100) + '%' }"
            :class="pLog.status"
          ></div>
        </div>
        <div class="provider-actions">
          <button class="btn-outline" @click="openPayload(pLog.providerName + ' - Request', pLog.requestPayload ?? '')">
            Request
          </button>
          <button class="btn-outline" @click="openPayload(pLog.providerName + ' - Response', pLog.responsePayload ?? '')">
            Response
          </button>
        </div>
      </div>
    </div>

    <!-- Payload Modal -->
    <Teleport to="body">
      <div v-if="activePayload" class="modal-overlay" @click.self="activePayload = null">
        <div class="payload-modal glass-panel">
          <div class="modal-header">
            <h3>{{ activePayload.title }}</h3>
            <button class="close-btn" @click="activePayload = null">✕</button>
          </div>
          <div class="modal-body">
            <pre><code>{{ activePayload.content }}</code></pre>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.waterfall-container {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.waterfall-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.global-actions {
  display: flex;
  gap: 1rem;
}

.btn-primary {
  padding: 0.6rem 1.2rem;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 700;
  background: var(--primary-color);
  color: white;
  border-radius: 0.5rem;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

.btn-primary:hover {
  filter: brightness(1.2);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
}

.btn-outline {
  padding: 0.4rem 0.8rem;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 0.4rem;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: white;
  transform: scale(1.05);
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 2rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 1rem;
}

.timeline-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.label { font-weight: 700; min-width: 150px; }

.provider-row {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.provider-info {
  min-width: 150px;
  display: flex;
  flex-direction: column;
}

.name { font-size: 0.95rem; text-transform: capitalize; font-weight: 600; }
.ms { font-size: 0.8rem; color: var(--text-muted); }

.bar-container, .full-bar-container {
  flex: 1;
  height: 28px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 6px;
  position: relative;
}

.bar {
  height: 100%;
  border-radius: 6px;
  transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.total-bar {
  background: linear-gradient(90deg, #60a5fa, #a78bfa);
  box-shadow: 0 0 15px rgba(96, 165, 250, 0.3);
}

.provider-bar { background: #3b82f6; }
.provider-bar.completed { background: #10b981; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2); }
.provider-bar.failed { background: #ef4444; box-shadow: 0 0 10px rgba(239, 68, 68, 0.2); }

.provider-actions {
  display: flex;
  gap: 0.5rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.payload-modal {
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
  animation: modalAppear 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes modalAppear {
  from { opacity: 0; transform: scale(0.95) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h3 {
  margin: 0;
  text-transform: capitalize;
}

.modal-body {
  overflow-y: auto;
  background: rgba(0, 0, 0, 0.3);
  padding: 1rem;
  border-radius: 0.5rem;
}

pre {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
  font-size: 0.9rem;
  font-family: 'Fira Code', 'Courier New', Courier, monospace;
  color: #e5e7eb;
  line-height: 1.5;
}

.close-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  font-size: 1.5rem;
  cursor: pointer;
}

.close-btn:hover {
  color: #ef4444;
}
</style>
