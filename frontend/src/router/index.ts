import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import Login from '@/modules/auth/views/LoginView.vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Home from '@/modules/home/views/HomeView.vue'
import Users from '@/modules/users/views/index.vue'
import UserForm from '@/modules/users/views/Form.vue'
import Profile from '@/modules/auth/views/ProfileView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'Login', component: Login },
    {
      path: '/home',
      component: DashboardLayout,
      children: [{ path: '', name: 'Home', component: Home, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'House', title: 'Dashboard', show: true } }],
    },
    {
      path: '/users',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Users', component: Users, meta: { requiresAuth: true, roles: ['admin'], icon: 'User', title: 'Usuarios', show: true } },
        { path: 'new', name: 'UserNew', component: UserForm, meta: { requiresAuth: true, roles: ['admin'], icon: 'User', title: 'Nuevo usuario', show: false } },
        { path: ':id/edit', name: 'UserEdit', component: UserForm, meta: { requiresAuth: true, roles: ['admin'], icon: 'User', title: 'Editar usuario', show: false } },
      ],
    },
    {
      path: '/profile',
      component: DashboardLayout,
      children: [{ path: '', name: 'Profile', component: Profile, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'User', title: 'Perfil', show: false } }],
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Si tenemos token pero no user, intentar restaurarlo desde el backend
  if (auth.isAuthenticated && !auth.user) {
    try {
      await auth.fetchMe()
    } catch (e) {
      // si falla, dejamos que las comprobaciones siguientes redirijan
    }
  }

  // Rutas protegidas
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  // Verificar roles si la ruta declara meta.roles
  const routeRoles = (to.meta && (to.meta as any).roles) as string[] | undefined
  if (routeRoles && auth.user) {
    const userRoles = (auth.user.roles || []).map((r: string) => r.toLowerCase().replace(/^role_/, ''))
    const allowed = routeRoles.map(r => r.toLowerCase())
    const isAdmin = userRoles.includes('admin') || userRoles.includes('role_admin')
    const intersects = userRoles.some((ur: string) => allowed.includes(ur))
    if (!isAdmin && !intersects) {
      return { path: '/home' }
    }
  }

  // Si ya está loggeado, evitar volver al login
  if (to.path === '/login' && auth.isAuthenticated) {
    return { path: '/home' }
  }
})

export default router
