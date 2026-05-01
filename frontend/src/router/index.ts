import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useAuth } from '../store/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('../views/admin/LoginView.vue'),
      meta: { public: true }
    },
    {
      path: '/admin',
      component: () => import('../layouts/AdminLayout.vue'),
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('../views/admin/DashboardView.vue')
        },
        {
          path: 'logs',
          name: 'admin-logs',
          component: () => import('../views/admin/LogsListView.vue')
        },
        {
          path: 'providers',
          name: 'admin-providers',
          component: () => import('../views/admin/ProvidersListView.vue')
        }
      ]
    }
  ]
})

router.beforeEach((to, _from, next) => {
  const { isAuthenticated } = useAuth()
  
  if (to.path.startsWith('/admin') && !to.meta.public && !isAuthenticated.value) {
    next('/admin/login')
  } else if (to.name === 'admin-login' && isAuthenticated.value) {
    next('/admin')
  } else {
    next()
  }
})

export default router
