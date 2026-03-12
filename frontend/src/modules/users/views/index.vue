<template lang="pug">
.user-view
  .users-header
    .header-copy
      h2 Gestión de usuarios
      p Administra cuentas, accesos y estado de actividad desde un solo lugar.
    .actions
      el-button(type="primary" @click="fetchUsers" :loading="loading") Añadir nuevo usuario
      el-button(type="primary" @click="fetchUsers" :loading="loading") Actualizar

  el-card.users-card(shadow="never")
    .toolbar
      el-input.toolbar-input(
        v-model="search"
        placeholder="Buscar por email o texto..."
        clearable
        @clear="onClear"
        @input="onSearchInput"
      )
      el-select(v-model="selectedRole" placeholder="Filtrar por rol" clearable)
        el-option(key="all" label="Todos" :value="''")
        el-option(v-for="r in rolesOptions" :key="r" :label="r" :value="r")
      el-select(v-model="selectedActive" placeholder="Estado" clearable)
        el-option(label="Todos" :value="''")
        el-option(label="Activos" :value="true")
        el-option(label="Inactivos" :value="false")

    el-table.users-table(:data="users" v-loading="loading")
      el-table-column(prop="id" label="ID" width="80")
      el-table-column(prop="email" label="Email" min-width="220")
      el-table-column(label="Roles" min-width="200")
        template(#default="{ row }")
          .role-tags
            el-tag(v-for="(role, idx) in row.roles" :key="idx" size="small" effect="plain") {{ role }}
      el-table-column(prop="is_active" label="Activo" width="100")
        template(#default="{ row }")
          el-switch(:model-value="row.is_active" disabled)
      el-table-column(prop="last_login" label="Último login" min-width="190")
        template(#default="{ row }")
          span.last-login {{ formatDate(row.last_login) }}
      el-table-column(label="Acciones" width="180" fixed="right")
        template(#default="{ row }")
          .actions
            el-button(type="primary" link @click="onEdit(row)") Editar
            el-button(type="danger" link @click="onDelete(row)") Eliminar

    .footer
      span.results Mostrando {{ meta.from }} - {{ meta.to }} de {{ meta.total }} usuarios
      el-pagination(
        background
        :page-size="pageSize"
        :current-page="page"
        :total="meta.total"
        @current-change="onPageChange"
        @size-change="onPageSizeChange"
        layout="sizes, prev, pager, next"
        :page-sizes="[10, 20, 50]"
      )
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getAuthHeaders } from '@/api/auth'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'

defineOptions({
  name: 'UsersView',
})

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
  } catch {
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
  } catch {
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 8px 12px;
}

.users-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 14px;
  gap: 12px;
}

.header-copy h2 {
  margin: 0;
  font-size: 22px;
  color: #0f172a;
}

.header-copy p {
  margin: 4px 0 0;
  color: #6b7280;
  font-size: 13px;
}

.users-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 12px;
}

.toolbar {
  display: grid;
  grid-template-columns: minmax(250px, 1.4fr) 1fr 1fr;
  gap: 12px;
  margin-bottom: 14px;
}

.toolbar-input {
  width: 100%;
}

.users-table {
  width: 100%;
}

.role-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.last-login {
  color: #475569;
}

.actions {
  display: flex;
  /* gap: 10px; */
}

.footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 14px;
  gap: 12px;
}

.results {
  color: #6b7280;
  font-size: 13px;
}

@media (max-width: 900px) {
  .users-header,
  .footer {
    flex-direction: column;
    align-items: stretch;
  }

  .toolbar {
    grid-template-columns: 1fr;
  }
}
</style>
