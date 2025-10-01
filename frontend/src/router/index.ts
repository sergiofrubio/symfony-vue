import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/LoginView.vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Home from '@/views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  {
    path: '/home',
    component: DashboardLayout,
    children: [
      { path: '', component: Home },
    ]
  }
]

})

export default router
