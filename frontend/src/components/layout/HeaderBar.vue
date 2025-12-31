<template lang="pug">
  div.header-bar
    div.header-left
      span.logo Panel de control
    div.header-right
      //- Notificaciones
      el-dropdown(trigger="click" @command="onNotifCommand")
        div.custom-trigger-notif
          el-badge(:value="unreadCount" class="notif-badge" v-if="unreadCount > 0")
            el-icon
              BellFilled
          el-icon(v-else)
            Bell
        template(#dropdown)
          el-dropdown-menu
            el-dropdown-item(disabled v-if="notifications.length === 0" class="notif-empty")
              | No hay notificaciones
            el-dropdown-item(
              v-for="n in notifications"
              :key="n.id"
              :command="n.id"
              class="notif-item"
            )
              .notif-title {{ n.title }}
              .notif-meta {{ formatDate(n.date) }}
              el-tag(v-if="!n.read" size="mini" type="danger" style="margin-left:6px" plain) Nuevo
            el-dropdown-item(divided @click="markAllRead")
              | Marcar todas como leídas

      //- Usuario
      el-dropdown
        div.custom-trigger
          el-avatar(size="small" :src="user.avatar")
          span.username {{ user.name }}
        template(#dropdown)
          el-dropdown-menu
            el-dropdown-item(@click="goProfile") Perfil
            //- el-dropdown-item(@click="settings") Configuración
            el-dropdown-item(class="logout-item" divided @click="logout") Cerrar sesión
</template>

<script lang="ts" setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'

const router = useRouter()

const user = reactive({
  name: 'John Doe',
  avatar: 'https://i.pravatar.cc/40',
})

type Notification = {
  id: number
  title: string
  body?: string
  date: string
  read: boolean
  url?: string
}

const notifications = ref<Notification[]>([
  { id: 1, title: 'Nueva orden recibida', date: new Date().toISOString(), read: false, url: '/orders/123' },
  { id: 2, title: 'Backup completado', date: new Date(Date.now() - 3600_000).toISOString(), read: true },
])

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

function formatDate(v: string) {
  try { return new Date(v).toLocaleString() } catch { return v }
}

function goProfile() {
  router.push('/profile')
}

function logout() {
  localStorage.removeItem('jwt_token')
  sessionStorage.removeItem('jwt_token')
  ElMessage({
    message: 'Sesión cerrada',
    type: 'success',
  })
  router.push('/login')
}

// Manejo de notificaciones
function onNotifCommand(command: string | number) {
  const id = Number(command)
  if (Number.isNaN(id)) return
  const n = notifications.value.find(x => x.id === id)
  if (!n) return
  n.read = true
  if (n.url) {
    router.push(n.url)
  } else {
    ElMessage({ message: n.title, type: 'info' })
  }
}

function markAllRead() {
  notifications.value.forEach(n => (n.read = true))
  ElMessage({ message: 'Todas las notificaciones marcadas como leídas', type: 'success' })
}

async function fetchNotificationsFromApi() {
  try {
    const token = localStorage.getItem('jwt_token')
    const headers: Record<string,string> = token ? { Authorization: `Bearer ${token}`, Accept: 'application/json' } : { Accept: 'application/json' }
    const res = await fetch('/api/notifications', { headers })
    if (res.ok) {
      const data = await res.json()
      if (Array.isArray(data)) notifications.value = data
    }
  } catch (e) {
    // no mostrar error en header
  }
}

onMounted(fetchNotificationsFromApi)
</script>

<style>
.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
  padding: 0 20px;
  background-color: #fff;
  border-bottom: 1px solid #ebeef5;
}

.header-left .logo {
  font-weight: bold;
  font-size: 20px;
  color: #409eff;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 18px;
}

.custom-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.custom-trigger-notif {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 6px;
}

.username {
  font-weight: 500;
  color: #333;
}

.el-dropdown-menu__item.logout-item {
  color: #f56c6c;
}

.el-dropdown-menu__item.logout-item:hover {
  background-color: #f56c6c;
  color: #fff;
}

.notif-item {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 8px 12px;
}

.notif-title {
  font-weight: 500;
  font-size: 13px;
}

.notif-meta {
  font-size: 11px;
  color: #999;
  margin-top: 4px;
}

.notif-empty {
  color: #666;
  cursor: default;
}

.notif-badge {
  margin-right: 6px;
}
</style>
