import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import Login from '@/modules/auth/views/LoginView.vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Home from '@/modules/home/views/HomeView.vue'
import Users from '@/modules/users/views/UsersView.vue'
import Profile from '@/modules/auth/views/ProfileView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'Login', component: Login },
    {
      path: '/home',
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [{ path: '', name: 'Home', component: Home }],
    },
     {
      path: '/users',
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [{ path: '', name: 'Users', component: Users }],
    },
    {
      path: '/profile',
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [{ path: '', name: 'Profile', component: Profile }],
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  // Rutas protegidas
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  // Si ya está loggeado, evitar volver al login
  if (to.path === '/login' && auth.isAuthenticated) {
    return { path: '/home' }
  }
})

export default router
