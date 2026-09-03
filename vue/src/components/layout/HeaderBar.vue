<template lang="pug">
.header-bar
  .header-left
    .brand-section
      img.brand-logo(src="@/assets/logo.svg" alt="Logo Corporativo")
      span.brand-title Enterprise Portal

    el-button.toggle-sidebar-btn(
      type="primary"
      link
      :title="isCollapse ? 'Expandir menú lateral' : 'Contraer menú lateral'"
      @click="$emit('toggle-collapse')"
    )
      el-icon(:size="20")
        component(:is="isCollapse ? 'Expand' : 'Fold'")

    .breadcrumbs-wrapper
      el-breadcrumb(separator="/")
        el-breadcrumb-item(:to="{ path: '/home' }")
          el-icon.breadcrumb-icon
            House
          span Inicio
        el-breadcrumb-item(
          v-for="(crumb, index) in breadcrumbs"
          :key="index"
          :to="crumb.path ? { path: crumb.path } : undefined"
        )
          span {{ crumb.title }}

  .header-right
    CompanySwitcher
    // Campana de Notificaciones
    el-dropdown(trigger="click" @command="onNotifCommand")
      .header-icon-btn(:title="unreadCount ? `${unreadCount} notificaciones sin leer` : 'Notificaciones'")
        el-badge(:value="unreadCount" :max="99" :hidden="unreadCount === 0" class="notif-badge")
          el-icon(:size="20")
            Bell
      template(#dropdown)
        el-dropdown-menu.notif-dropdown
          .notif-header
            span.notif-header-title Notificaciones
            el-tag(v-if="unreadCount > 0" size="small" type="primary" effect="plain") {{ unreadCount }} nuevas
          el-dropdown-item(disabled v-if="notifications.length === 0" class="notif-empty")
            span No tienes notificaciones pendientes
          el-dropdown-item(
            v-for="n in notifications"
            :key="n.id"
            :command="n.id"
            class="notif-item-row"
          )
            .notif-item-content
              .notif-item-top
                span.notif-title {{ n.title }}
                el-tag(v-if="!n.read" size="small" type="danger" effect="light") Nueva
              span.notif-meta {{ formatDate(n.date) }}
          .notif-footer
            el-button(type="primary" link size="small" @click="markAllRead" :disabled="unreadCount === 0") Marcar todas como leídas

    // Información del Usuario Logueado
    el-dropdown(trigger="click" @command="handleUserCommand")
      .user-profile-trigger
        el-avatar(:size="36" :src="userAvatar" class="user-avatar")
          el-icon
            UserFilled
        .user-info
          span.user-name {{ userName }}
          span.user-role {{ userRoleLabel }}
        el-icon.arrow-icon
          ArrowDown
      template(#dropdown)
        el-dropdown-menu.user-dropdown
          .user-dropdown-header
            p.user-dropdown-email {{ userEmail }}
          el-dropdown-item(command="profile")
            el-icon
              User
            span Mi Perfil
          el-dropdown-item(divided command="logout" class="logout-item")
            el-icon
              SwitchButton
            span Cerrar Sesión
</template>

<script lang="ts" setup>
import { computed, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import CompanySwitcher from '@/components/common/CompanySwitcher.vue'
import userAvatarImg from '@/assets/user.png'
import {
  Expand,
  Fold,
  House,
  Bell,
  UserFilled,
  User,
  SwitchButton,
  ArrowDown,
} from '@element-plus/icons-vue'

defineProps<{
  isCollapse: boolean
}>()

defineEmits<{
  (e: 'toggle-collapse'): void
}>()

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const userAvatar = userAvatarImg

// Breadcrumbs dinámicos basados en la ruta actual
const breadcrumbs = computed(() => {
  const currentPath = route.path
  if (currentPath === '/home' || currentPath === '/') {
    return []
  }

  const crumbs: { title: string; path?: string }[] = []

  if (currentPath.startsWith('/users')) {
    crumbs.push({ title: 'Usuarios', path: currentPath === '/users' ? undefined : '/users' })
    if (currentPath.endsWith('/new')) {
      crumbs.push({ title: 'Nuevo usuario' })
    } else if (currentPath.includes('/edit')) {
      crumbs.push({ title: 'Editar usuario' })
    }
  } else if (currentPath.startsWith('/invoices')) {
    crumbs.push({ title: 'Facturas', path: currentPath === '/invoices' ? undefined : '/invoices' })
    if (currentPath.endsWith('/new')) {
      crumbs.push({ title: 'Nueva factura' })
    } else if (currentPath.includes('/edit')) {
      crumbs.push({ title: 'Editar factura' })
    }
  } else if (currentPath === '/profile') {
    crumbs.push({ title: 'Mi Perfil' })
  } else {
    const metaTitle = (route.meta && (route.meta as any).title) || String(route.name || '')
    if (metaTitle && metaTitle !== 'Dashboard') {
      crumbs.push({ title: metaTitle })
    }
  }

  return crumbs
})

// Datos del usuario logeado
const userName = computed(() => {
  if (authStore.user?.email) {
    const prefix = authStore.user.email.split('@')[0]
    return prefix.charAt(0).toUpperCase() + prefix.slice(1)
  }
  return 'Usuario'
})

const userEmail = computed(() => authStore.user?.email || 'usuario@empresa.com')

const userRoleLabel = computed(() => {
  const roles = authStore.user?.roles || []
  if (roles.some((r: string) => r.toUpperCase().includes('ADMIN'))) {
    return 'Administrador'
  }
  if (roles.some((r: string) => r.toUpperCase().includes('MANAGER'))) {
    return 'Gestor'
  }
  return 'Personal'
})

// Manejo de notificaciones
type Notification = { id: number; title: string; date: string; read: boolean; url?: string }
const notifications = ref<Notification[]>([
  { id: 1, title: 'Nueva factura emitida con éxito', date: new Date().toISOString(), read: false, url: '/invoices' },
  { id: 2, title: 'Bienvenido al sistema corporativo', date: new Date(Date.now() - 3600000).toISOString(), read: false },
])

const unreadCount = computed(() => notifications.value.filter((n) => !n.read).length)

function formatDate(v: string) {
  try {
    const d = new Date(v)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' · ' + d.toLocaleDateString()
  } catch {
    return v
  }
}

function onNotifCommand(command: string | number) {
  const id = Number(command)
  if (Number.isNaN(id)) return
  const n = notifications.value.find((x) => x.id === id)
  if (!n) return
  n.read = true
  if (n.url) router.push(n.url)
  else ElMessage.info(n.title)
}

function markAllRead() {
  notifications.value.forEach((n) => (n.read = true))
  ElMessage.success('Notificaciones marcadas como leídas')
}

async function fetchNotificationsFromApi() {
  try {
    const token = localStorage.getItem('jwt_token')
    const headers: Record<string, string> = token
      ? { Authorization: `Bearer ${token}`, Accept: 'application/json' }
      : { Accept: 'application/json' }
    const res = await fetch('/api/notifications', { headers })
    if (res.ok) {
      const data = await res.json()
      if (Array.isArray(data) && data.length > 0) {
        notifications.value = data
      }
    }
  } catch {
    // silencioso
  }
}

function handleUserCommand(command: string) {
  if (command === 'profile') {
    router.push('/profile')
  } else if (command === 'logout') {
    authStore.logout()
    ElMessage.success('Sesión cerrada correctamente')
    router.push('/login')
  }
}

onMounted(() => {
  fetchNotificationsFromApi()
})
</script>

<style scoped>
.header-bar {
  height: 60px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  box-sizing: border-box;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 10px;
  padding-right: 14px;
  border-right: 1px solid #e2e8f0;
}

.brand-logo {
  height: 28px;
  width: 28px;
}

.brand-title {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  letter-spacing: -0.2px;
}

.toggle-sidebar-btn {
  font-size: 18px;
  color: #64748b;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.2s;
}

.toggle-sidebar-btn:hover {
  color: #0f172a;
  background-color: #f1f5f9;
}

.breadcrumbs-wrapper {
  display: flex;
  align-items: center;
}

.breadcrumb-icon {
  margin-right: 4px;
  vertical-align: -2px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon-btn {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  color: #64748b;
  cursor: pointer;
  transition: background-color 0.2s, color 0.2s;
}

.header-icon-btn:hover {
  background-color: #f1f5f9;
  color: #0f172a;
}

.user-profile-trigger {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.user-profile-trigger:hover {
  background-color: #f1f5f9;
}

.user-avatar {
  background-color: #e2e8f0;
  border: 1px solid #cbd5e1;
}

.user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
  text-align: left;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.user-role {
  font-size: 11px;
  color: #64748b;
}

.arrow-icon {
  font-size: 12px;
  color: #94a3b8;
}

/* Dropdown de Notificaciones */
.notif-dropdown {
  width: 300px;
  padding: 0;
}

.notif-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-weight: 600;
  color: #1e293b;
  font-size: 13px;
}

.notif-empty {
  padding: 20px 16px;
  text-align: center;
  color: #94a3b8;
  font-size: 13px;
}

.notif-item-row {
  padding: 10px 16px;
  border-bottom: 1px solid #f8fafc;
}

.notif-item-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 100%;
}

.notif-item-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notif-title {
  font-size: 13px;
  color: #1e293b;
  font-weight: 500;
}

.notif-meta {
  font-size: 11px;
  color: #94a3b8;
}

.notif-footer {
  padding: 8px 16px;
  text-align: center;
  border-top: 1px solid #f1f5f9;
}

/* Dropdown de Usuario */
.user-dropdown {
  min-width: 180px;
}

.user-dropdown-header {
  padding: 8px 16px;
  border-bottom: 1px solid #f1f5f9;
}

.user-dropdown-email {
  margin: 0;
  font-size: 12px;
  color: #64748b;
  word-break: break-all;
}

.logout-item {
  color: #dc2626 !important;
}

@media (max-width: 768px) {
  .brand-title,
  .breadcrumbs-wrapper,
  .user-role {
    display: none;
  }
}
</style>
