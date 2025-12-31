<template lang="pug">
div.profile-view
  el-card
    div.header(style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;")
      h3 Perfil
      el-button(type="primary" @click="onRefresh") Refrescar

    el-form(:model="form" :rules="rules" ref="formRef" label-position="top" label-width="120px")
      el-form-item(label="Email" prop="email")
        el-input(v-model="form.email" placeholder="Email")

      el-form-item(label="Roles")
        div
          el-tag(v-for="role in form.roles" :key="role" style="margin-right:6px") {{ role }}

      el-form-item(label="Activo")
        el-switch(v-model="form.is_active")

      el-form-item(label="Último login")
        span {{ formatDate(form.last_login) }}

      .actions(style="margin-top:12px;display:flex;gap:8px;")
        el-button(type="primary" @click="onSave" :loading="saving") Guardar cambios
        el-button(@click="onLogout") Cerrar sesión
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

const router = useRouter()
const formRef = ref(null)
const loading = ref(false)
const saving = ref(false)

const form = reactive({
  id: null as number | null,
  email: '',
  roles: [] as string[],
  is_active: false,
  last_login: null as string | null,
})

const rules = {
  email: [
    { required: true, message: 'Email requerido', trigger: 'blur' },
    { type: 'email', message: 'Email inválido', trigger: ['blur', 'change'] }
  ]
}

function getAuthHeaders() {
  const token = localStorage.getItem('jwt_token')
  if (!token) {
    router.push('/login')
    return {}
  }
  return { Authorization: `Bearer ${token}`, Accept: 'application/json' }
}

async function fetchProfile() {
  loading.value = true
  try {
    const res = await fetch('/api/me', { headers: getAuthHeaders() })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('Error fetching profile')
    const data = await res.json()
    form.id = data.id ?? null
    form.email = data.email ?? ''
    form.roles = Array.isArray(data.roles) ? data.roles : []
    form.is_active = Boolean(data.is_active)
    form.last_login = data.last_login ?? null
  } catch (e) {
    ElMessage.error('No se pudo cargar el perfil')
  } finally { loading.value = false }
}

async function onSave() {
  // validar
  // @ts-ignore
  formRef.value.validate(async (valid: boolean) => {
    if (!valid) return
    saving.value = true
    try {
      const payload = { email: form.email, is_active: form.is_active }
      const res = await fetch(`/api/users/${form.id}`, {
        method: 'PUT',
        headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
      if (res.status === 401) { router.push('/login'); return }
      if (!res.ok) throw new Error('No se pudo guardar')
      ElMessage.success('Perfil actualizado')
      fetchProfile()
    } catch (e) {
      ElMessage.error('Error al guardar')
    } finally { saving.value = false }
  })
}

function formatDate(v: string | null) {
  if (!v) return '-'
  try { return new Date(v).toLocaleString() } catch { return v }
}

function onLogout() {
  localStorage.removeItem('jwt_token')
  sessionStorage.removeItem('jwt_token')
  router.push('/login')
}

function onRefresh() { fetchProfile() }

onMounted(() => { fetchProfile() })
</script>

<style scoped>
.profile-view { padding: 16px }
.header { margin-bottom: 8px }
.actions { margin-top: 16px }
</style>
