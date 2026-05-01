import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import { useAuth } from './store/auth'

const { initializeAuth } = useAuth()
initializeAuth()

const app = createApp(App)
app.use(router)
app.mount('#app')
