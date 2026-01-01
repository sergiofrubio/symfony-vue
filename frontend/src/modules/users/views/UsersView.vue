<template lang="pug">
div.user-view
  .toolbar(style="display:flex;gap:12px;align-items:center;margin-bottom:12px;")
    el-input(
      v-model="search"
      placeholder="Buscar por email o texto..."
      clearable
      @clear="onClear"
      @input="onSearchInput"
      style="width:320px"
    )
      template(#prefix)
        el-icon
          i.el-icon-search
    el-select(v-model="selectedRole" placeholder="Filtrar por rol" clearable style="width:180px")
      el-option(key="all" label="Todos" :value="''")
      el-option(v-for="r in rolesOptions" :key="r" :label="r" :value="r")
    el-select(v-model="selectedActive" placeholder="Estado" clearable style="width:140px")
      el-option(label="Todos" :value="''")
      el-option(label="Activos" :value="true")
      el-option(label="Inactivos" :value="false")
    el-button(type="primary" @click="fetchUsers") Actualizar

  el-table(:data="users" v-loading="loading" style="width:100%")
    el-table-column(prop="id" label="ID" width="70")
    el-table-column(prop="email" label="Email")
    el-table-column(label="Roles")
      template(#default="{row}")
        span(v-for="(role, idx) in row.roles" :key="idx")
          el-tag(size="small" style="margin-right:6px") {{ role }}
    el-table-column(prop="is_active" label="Activo" width="100")
      template(#default="{row}")
        el-switch(:model-value="row.is_active" disabled)
    el-table-column(prop="last_login" label="Último login" width="180")
      template(#default="{row}")
        span {{ formatDate(row.last_login) }}
    el-table-column(label="Acciones" width="160")
      template(#default="{row}")
        el-button(type="primary" size="small" @click="onEdit(row)") Editar
        el-button(type="danger" size="small" @click="onDelete(row)" style="margin-left:6px") Eliminar

  .footer(style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;")
    div
      span(style="color:var(--el-text-color-secondary)") Mostrando {{ meta.from }} - {{ meta.to }} de {{ meta.total }} usuarios
    el-pagination(
      background
      :page-size="pageSize"
      :current-page="page"
      :total="meta.total"
      @current-change="onPageChange"
      @size-change="onPageSizeChange"
      layout="sizes, prev, pager, next, jumper"
      :page-sizes="[10,20,50]"
    )
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getAuthHeaders } from '@/api/auth'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'

type User = {
  id: number
  email: string
  roles: string[]
  is_active: boolean
  last_login: string | null
}

const auth = useAuthStore()
const router = useRouter()
const users = ref<User[]>([])
const loading = ref(false)
const search = ref('')
const selectedRole = ref<string | ''>('')
const selectedActive = ref<string | boolean | ''>('')
const page = ref(1)
const pageSize = ref(10)
const meta = reactive({ total: 0, from: 0, to: 0 })

const rolesOptions = ref<string[]>(['ROLE_USER', 'ROLE_ADMIN'])

let searchTimer: number | undefined

// function getAuthHeaders(): Record<string, string> {
//   const token = localStorage.getItem('jwt_token')
//   if (!token) {
//     // si no hay token, redirige a login
//     router.push('/login')
//     return {}
//   }
//   return {
//     Authorization: `Bearer ${token}`,
//     Accept: 'application/json',
//   }
// }

function buildQueryParams() {
  const params: Record<string, string> = {}
  if (search.value) params.search = String(search.value)
  if (selectedRole.value) params.role = String(selectedRole.value)
  if (selectedActive.value !== '') params.is_active = String(selectedActive.value)
  params.page = String(page.value)
  params.limit = String(pageSize.value)
  return new URLSearchParams(params).toString()
}

async function fetchUsers() {
  loading.value = true
  try {
    const qs = buildQueryParams()
    const res = await fetch(`/api/users?${qs}`, {
      headers: getAuthHeaders(),
    })
    if (res.status === 401) {
      router.push('/login')
      return
    }
    if (!res.ok) throw new Error('Error fetching users')
    const data = await res.json()
    users.value = Array.isArray(data.items) ? data.items : []
    meta.total = data.total ?? users.value.length
    meta.from = users.value.length ? (page.value - 1) * pageSize.value + 1 : 0
    meta.to = users.value.length ? meta.from + users.value.length - 1 : 0
  } catch (e) {
    users.value = []
    meta.total = 0
    meta.from = 0
    meta.to = 0
  } finally {
    loading.value = false
  }
}

function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = window.setTimeout(() => {
    page.value = 1
    fetchUsers()
  }, 450)
}

function onClear() {
  page.value = 1
  fetchUsers()
}

function onPageChange(p: number) {
  page.value = p
  fetchUsers()
}

function onPageSizeChange(size: number) {
  pageSize.value = size
  page.value = 1
  fetchUsers()
}

function onEdit(row: User) {
  console.log('Editar', row)
}

async function onDelete(row: User) {
  const confirmed = confirm(`Eliminar usuario ${row.email}?`)
  if (!confirmed) return
  try {
    loading.value = true
    const res = await fetch(`/api/users/${row.id}`, {
      method: 'DELETE',
      headers: {
        ...getAuthHeaders(),
        'Content-Type': 'application/json',
      },
    })
    if (res.status === 401) {
      auth.logout()
      router.push('/login')
      return
    }
    if (!res.ok) throw new Error('No se pudo eliminar')
    fetchUsers()
  } catch (e) {
    alert('Error al eliminar usuario')
  } finally {
    loading.value = false
  }
}

function formatDate(v: string | null) {
  if (!v) return '-'
  try {
    const d = new Date(v)
    return d.toLocaleString()
  } catch {
    return v
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.user-view {
  padding: 12px;
  background: var(--el-bg-color);
  border-radius: 6px;
}
</style>
