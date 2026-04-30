import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
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

export default router
