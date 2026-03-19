<template lang="pug">
div.header-bar
  .header-inner
    .header-spacer
    .header-actions
      el-dropdown(trigger="click" @command="onNotifCommand")
        div.bell-button(:title="unreadCount ? `${unreadCount} no leídas` : 'Notificaciones'")
          el-badge(:value="unreadCount" v-if="unreadCount > 0" class="notif-badge")
            el-icon
              BellFilled
          el-icon(v-else)
            Bell
        template(#dropdown)
          el-dropdown-menu
            el-dropdown-item(disabled v-if="notifications.length === 0" class="notif-empty") No hay notificaciones
            el-dropdown-item(v-for="n in notifications" :key="n.id" :command="n.id" class="notif-item")
              .notif-title {{ n.title }}
              .notif-meta {{ formatDate(n.date) }}
              el-tag(v-if="!n.read" size="mini" type="danger" plain) Nuevo
            el-dropdown-item(divided @click="markAllRead") Marcar todas como leídas
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'

type Notification = { id: number; title: string; body?: string; date: string; read: boolean; url?: string }

const router = useRouter()
const notifications = ref<Notification[]>([])

// ejemplo base
// notifications.value = [
//   { id: 1, title: 'Nueva orden recibida', date: new Date().toISOString(), read: false, url: '/orders/123' },
//   { id: 2, title: 'Backup completado', date: new Date(Date.now() - 3600_000).toISOString(), read: true },
// ]

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

function formatDate(v: string) {
  try { return new Date(v).toLocaleString() } catch { return v }
}

function onNotifCommand(command: string | number) {
  const id = Number(command)
  if (Number.isNaN(id)) return
  const n = notifications.value.find(x => x.id === id)
  if (!n) return
  n.read = true
  if (n.url) router.push(n.url)
  else ElMessage({ message: n.title, type: 'info' })
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
    // silencioso
  }
}

onMounted(fetchNotificationsFromApi)
</script>

<style scoped>
.header-bar {
  height: 72px;
  width: 100%;
  box-sizing: border-box;
  background: transparent; /* sin color, sólo botones visibles */
  display: flex;
  align-items: center;
  padding: 0 16px;
  /* box-shadow: 0 8px 20px rgba(15,23,42,0.06); */
  /* border-bottom: 1px solid rgba(15,23,42,0.04); */
}

.header-inner {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-spacer { width: 220px; /* coincide con el ancho del sidebar para evitar solapamiento visual */ }

.header-actions { display:flex; align-items:center; justify-content:flex-end }

.bell-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px; /* aumentado para mayor destaque */
  height: 56px; /* aumentado para mayor destaque */
  border-radius: 12px;
  background: transparent; /* header sigue transparente */
  cursor: pointer;
  transition: box-shadow .18s ease, background .12s ease, transform .12s ease;
}

.bell-button:hover {
  background: #ffffff; /* destaca sobre header transparente */
  box-shadow: 0 10px 24px rgba(15,23,42,0.08);
  transform: translateY(-1px);
}

.notif-badge { margin-right: 6px }

.bell-button .el-icon, .bell-button svg { width: 26px; height: 26px }

.notif-item { padding: 8px 12px; display:flex; flex-direction:column; gap:6px }
.notif-title { font-weight: 600; font-size: 13px }
.notif-meta { font-size: 12px; color: #6b7280 }
.notif-empty { color: #9ca3af; cursor: default; padding: 8px 12px }

.el-dropdown-menu { min-width: 240px }
</style>
