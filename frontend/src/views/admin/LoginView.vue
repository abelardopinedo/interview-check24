<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../store/auth';

const router = useRouter();
const { login } = useAuth();

const username = ref('');
const password = ref('');
const isLoading = ref(false);
const error = ref('');

const handleSubmit = async () => {
  if (!username.value || !password.value) {
    error.value = 'Por favor, rellene todos los campos.';
    return;
  }

  isLoading.value = true;
  error.value = '';
  
  try {
    const success = await login(username.value, password.value);
    if (success) {
      router.push('/admin');
    }
  } catch (err: unknown) {
    error.value = 'Usuario o contraseña incorrectos.';
    // eslint-disable-next-line no-console
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="login-view">
    <div class="login-card glass-panel">
      <div class="login-header">
        <div class="logo">🛡️</div>
        <h1>Admin Portal</h1>
        <p>Inicie sesión para continuar</p>
      </div>

      <form @submit.prevent="handleSubmit" class="login-form">
        <div class="form-group">
          <label for="username">Usuario</label>
          <div class="input-wrapper">
            <span class="input-icon">👤</span>
            <input 
              id="username" 
              v-model="username" 
              type="text" 
              placeholder="Introduce tu usuario"
              required
              :disabled="isLoading"
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <div class="input-wrapper">
            <span class="input-icon">🔑</span>
            <input 
              id="password" 
              v-model="password" 
              type="password" 
              placeholder="Introduce tu contraseña"
              required
              :disabled="isLoading"
            />
          </div>
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" class="submit-btn" :disabled="isLoading">
          <span v-if="isLoading" class="spinner"></span>
          <span v-else>Entrar</span>
        </button>
      </form>
    </div>
    
    <div class="bg-decoration">
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
    </div>
  </div>
</template>

<style scoped>
.login-view {
  min-height: 100vh;
  width: 100vw;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  position: relative;
  overflow: hidden;
}

.login-card {
  width: 100%;
  max-width: 420px;
  padding: 3rem;
  z-index: 10;
  border: 1px solid rgba(255, 255, 255, 0.1);
  animation: fadeIn 0.8s ease-out;
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.logo {
  font-size: 3rem;
  margin-bottom: 1rem;
}

h1 {
  font-size: 1.75rem;
  font-weight: 800;
  color: white;
  margin: 0;
  letter-spacing: -0.025em;
}

p {
  color: #94a3b8;
  margin-top: 0.5rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #cbd5e1;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  font-size: 1rem;
  opacity: 0.5;
}

input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 3rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  color: white;
  transition: all 0.3s;
}

input:focus {
  outline: none;
  border-color: #3b82f6;
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.submit-btn {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.error-message {
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 0.5rem;
  color: #f87171;
  font-size: 0.875rem;
  text-align: center;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-left-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } }

.bg-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.blob {
  position: absolute;
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  filter: blur(80px);
  opacity: 0.15;
  border-radius: 50%;
}

.blob-1 { top: -100px; right: -100px; animation: float 20s infinite alternate; }
.blob-2 { bottom: -100px; left: -100px; animation: float 25s infinite alternate-reverse; }

@keyframes float {
  from { transform: translate(0, 0); }
  to { transform: translate(50px, 50px); }
}
</style>
