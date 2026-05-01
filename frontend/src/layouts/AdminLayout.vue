<template>
  <div class="admin-layout">
    <aside class="sidebar glass-panel">
      <div class="sidebar-header">
        <div class="logo">
          <span class="logo-icon">🛡️</span>
          <span class="logo-text">Admin Panel</span>
        </div>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/admin" class="nav-item" exact-active-class="active">
          <span class="icon">📊</span>
          <span class="text">Dashboard</span>
        </router-link>
        <router-link to="/admin/logs" class="nav-item" active-class="active">
          <span class="icon">📜</span>
          <span class="text">Logs Explorer</span>
        </router-link>
        <router-link to="/admin/providers" class="nav-item" active-class="active">
          <span class="icon">🏢</span>
          <span class="text">Providers</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <router-link to="/" class="nav-item back-home">
          <span class="icon">🏠</span>
          <span class="text">Volver a Web</span>
        </router-link>
        <button @click="handleLogout" class="nav-item logout-btn">
          <span class="icon">🚪</span>
          <span class="text">Cerrar Sesión</span>
        </button>
      </div>
    </aside>

    <main class="admin-content">
      <router-view />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuth } from '../store/auth';

const router = useRouter();
const { logout } = useAuth();

const handleLogout = async () => {
  await logout();
  router.push('/admin/login');
};
</script>

<style scoped>
.admin-layout {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background: var(--bg-color);
  color: var(--text-color);
}

.sidebar {
  width: 280px;
  height: 100%;
  display: flex;
  flex-direction: column;
  padding: 2rem 1.5rem;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0;
  background: rgba(15, 23, 42, 0.8);
  z-index: 10;
}

.sidebar-header {
  margin-bottom: 3rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 0.75rem;
  text-decoration: none;
  color: var(--text-muted);
  transition: all 0.3s ease;
  font-weight: 500;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.nav-item.active {
  background: var(--primary-color);
  color: white;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.sidebar-footer {
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.back-home {
  margin-top: auto;
}

.logout-btn {
  width: 100%;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
  margin-top: 0.5rem;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #f87171;
}

.admin-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

@media (max-width: 1024px) {
  .sidebar {
    width: 80px;
    padding: 1rem;
  }
  .logo-text, .text {
    display: none;
  }
  .nav-item {
    justify-content: center;
  }
}
</style>
