<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface Provider {
  id: number;
  name: string;
  url: string;
  has_discount: boolean;
}

const providers = ref<Provider[]>([]);
const isLoading = ref(true);
const isSaving = ref(false);
const editingProvider = ref<Provider | null>(null);
const message = ref({ text: '', type: '' });

const fetchProviders = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/providers');
    providers.value = response.data;
  } catch (error) {
    console.error('Error fetching providers:', error);
  } finally {
    isLoading.value = false;
  }
};

const editProvider = (provider: Provider) => {
  editingProvider.value = { ...provider };
};

const saveProvider = async () => {
  if (!editingProvider.value) return;
  
  isSaving.value = true;
  message.value = { text: '', type: '' };
  
  try {
    const response = await axios.patch(`/api/providers/${editingProvider.value.id}`, {
      name: editingProvider.value.name,
      url: editingProvider.value.url,
      has_discount: editingProvider.value.has_discount
    });
    
    // Update local list
    const index = providers.value.findIndex(p => p.id === editingProvider.value?.id);
    if (index !== -1) {
      providers.value[index] = response.data;
    }
    
    message.value = { text: 'Proveedor actualizado con éxito', type: 'success' };
    setTimeout(() => {
      editingProvider.value = null;
      message.value = { text: '', type: '' };
    }, 1500);
  } catch (error) {
    console.error('Error saving provider:', error);
    message.value = { text: 'Error al actualizar el proveedor', type: 'error' };
  } finally {
    isSaving.value = false;
  }
};

onMounted(fetchProviders);
</script>

<template>
  <div class="providers-view">
    <header class="page-header">
      <div class="title-area">
        <h1>Gestión de Proveedores</h1>
        <p>Configura los endpoints y campañas de descuento de los proveedores de seguros.</p>
      </div>
    </header>

    <div v-if="isLoading" class="loading-overlay">
      <div class="spinner"></div>
    </div>

    <div v-else class="providers-grid">
      <div v-for="provider in providers" :key="provider.id" class="provider-card glass-panel">
        <div class="card-header">
          <div class="provider-badge">
            <span class="icon">🏢</span>
          </div>
          <div class="provider-info">
            <h3>{{ provider.name }}</h3>
            <span class="id">ID: #{{ provider.id }}</span>
          </div>
          <div class="status-indicator" :class="{ active: provider.has_discount }">
            {{ provider.has_discount ? 'Campaña Activa' : 'Sin Campaña' }}
          </div>
        </div>
        
        <div class="card-body">
          <div class="data-row">
            <span class="label">URL Endpoint:</span>
            <span class="value">{{ provider.url }}</span>
          </div>
        </div>

        <div class="card-footer">
          <button class="btn-edit" @click="editProvider(provider)">
            Editar Configuración
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <Teleport to="body">
      <div v-if="editingProvider" class="modal-overlay" @click.self="editingProvider = null">
        <div class="edit-modal glass-panel">
          <div class="modal-header">
            <h2>Editar Proveedor</h2>
            <button class="close-btn" @click="editingProvider = null">✕</button>
          </div>

          <form @submit.prevent="saveProvider" class="edit-form">
            <div class="form-group">
              <label>Nombre del Proveedor</label>
              <input v-model="editingProvider.name" type="text" required>
            </div>

            <div class="form-group">
              <label>URL del Endpoint</label>
              <input v-model="editingProvider.url" type="url" required>
            </div>

            <div class="form-group checkbox-group">
              <label class="switch">
                <input type="checkbox" v-model="editingProvider.has_discount">
                <span class="slider round"></span>
              </label>
              <span class="checkbox-label">Habilitar Descuento de Campaña (5%)</span>
            </div>

            <div v-if="message.text" class="form-message" :class="message.type">
              {{ message.text }}
            </div>

            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="editingProvider = null">Cancelar</button>
              <button type="submit" class="btn-primary" :disabled="isSaving">
                {{ isSaving ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.providers-view {
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

.providers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 2rem;
}

.provider-card {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.3s ease;
}

.provider-card:hover {
  border-color: var(--primary-color);
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.provider-badge {
  width: 48px;
  height: 48px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.provider-info h3 {
  margin: 0;
  font-size: 1.25rem;
}

.provider-info .id {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.status-indicator {
  margin-left: auto;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.4rem 0.8rem;
  border-radius: 2rem;
  background: rgba(255, 255, 255, 0.05);
  color: var(--text-muted);
  text-transform: uppercase;
}

.status-indicator.active {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.data-row {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
}

.value {
  font-family: 'Fira Code', monospace;
  font-size: 0.85rem;
  background: rgba(0, 0, 0, 0.2);
  padding: 0.75rem;
  border-radius: 0.5rem;
  word-break: break-all;
}

.btn-edit {
  width: 100%;
  padding: 0.8rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-edit:hover {
  background: var(--primary-color);
  border-color: var(--primary-color);
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.edit-modal {
  width: 90%;
  max-width: 500px;
  padding: 2.5rem;
  animation: modalAppear 0.3s ease-out;
}

@keyframes modalAppear {
  from { opacity: 0; transform: scale(0.95) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-muted);
}

input[type="text"], input[type="url"] {
  padding: 0.8rem 1rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  color: white;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.checkbox-group {
  flex-direction: row;
  align-items: center;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.03);
  padding: 1rem;
  border-radius: 0.5rem;
}

.checkbox-label {
  font-size: 0.9rem;
  font-weight: 500;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-primary, .btn-secondary {
  flex: 1;
  padding: 0.8rem;
  border-radius: 0.5rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: var(--primary-color);
  border: none;
  color: white;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
}

.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.form-message {
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  text-align: center;
}

.form-message.success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.form-message.error { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

/* Toggle Switch Styles */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.switch input { opacity: 0; width: 0; height: 0; }

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #334155;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: .4s;
}

input:checked + .slider { background-color: var(--primary-color); }
input:focus + .slider { box-shadow: 0 0 1px var(--primary-color); }
input:checked + .slider:before { transform: translateX(26px); }
.slider.round { border-radius: 34px; }
.slider.round:before { border-radius: 50%; }

.loading-overlay {
  display: flex;
  justify-content: center;
  padding: 5rem;
}

.spinner {
  width: 40px; height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.1);
  border-left-color: var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.close-btn {
  background: none; border: none;
  color: var(--text-muted); font-size: 1.5rem;
  cursor: pointer;
}
</style>
