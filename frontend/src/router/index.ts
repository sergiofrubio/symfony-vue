import { createRouter, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import DashboardLayout from '../components/layout/DashboardLayout.vue'
import DashboardHome from '../views/DashboardHome.vue'
import Settings from '../views/Settings.vue'
import Profile from '../views/Profile.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  {
    path: '/dashboard',
    component: DashboardLayout,
    children: [
      { path: '', component: DashboardHome },
      { path: 'settings', component: Settings },
      { path: 'profile', component: Profile },
    ]
  }
]

})

export default router
