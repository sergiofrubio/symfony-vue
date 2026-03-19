<template lang="pug">
.user-form-view
  .header
      h2 {{ isEdit ? 'Editar usuario' : 'Añadir usuario' }}
      p.small Despliega los detalles del usuario y gestiona roles y acceso.
  el-card.users-card(shadow="never")
    el-form(:model="form" :rules="rules" ref="formRef" label-width="120px")
      el-form-item(label="Email" prop="email")
        el-input(v-model="form.email" placeholder="usuario@ejemplo.com")

      el-form-item(label="Contraseña" prop="password")
        el-input(type="password" v-model="form.password" placeholder="Dejar vacío para no cambiar la contraseña (edición)")

      el-form-item(label="Roles" prop="roles")
        el-select(v-model="form.roles" multiple placeholder="Selecciona roles")
          el-option(v-for="r in rolesOptions" :key="r" :label="r" :value="r")

      el-form-item(label="Activo" prop="is_active")
        el-switch(v-model="form.is_active")

      .form-actions
        el-button(type="primary" :loading="loading" @click="onSubmit") Guardar
        el-button(@click="onCancel") Cancelar
        el-button(type="danger" v-if="isEdit" @click="onDelete" :loading="loading") Eliminar

</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getAuthHeaders } from '@/api/auth'

const router = useRouter()
const route = useRoute()
const id = route.params.id ? String(route.params.id) : null
const isEdit = computed(() => !!id)

const formRef = ref<any>(null)
const loading = ref(false)

const rolesOptions = ref<string[]>(['ROLE_USER', 'ROLE_ADMIN'])

const form = reactive({
  email: '',
  password: '',
  roles: [] as string[],
  is_active: true,
})

const rules = {
  email: [{ required: true, message: 'El email es obligatorio', trigger: 'blur' }],
  password: [
    { required: false },
  ],
  roles: [{ type: 'array', required: true, message: 'Selecciona al menos un rol', trigger: 'change' }],
}

async function fetchUser() {
  if (!id) return
  loading.value = true
  try {
    const res = await fetch(`/api/users/${id}`, { headers: getAuthHeaders() })
    if (res.status === 401) {
      router.push('/login')
      return
    }
    if (!res.ok) throw new Error('Error fetching user')
    const data = await res.json()
    form.email = data.email ?? ''
    form.roles = Array.isArray(data.roles) ? data.roles : []
    form.is_active = !!data.is_active
  } catch (e) {
    alert('No se pudo cargar el usuario')
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  if (!formRef.value) return
  await formRef.value.validate(async (valid: boolean) => {
    if (!valid) return
    loading.value = true
    try {
      const payload: any = {
        email: form.email,
        roles: form.roles,
        is_active: form.is_active,
      }
      if (!isEdit.value || (isEdit.value && form.password)) {
        payload.password = form.password
      }

      const method = isEdit.value ? 'PUT' : 'POST'
      const url = isEdit.value ? `/api/users/${id}` : '/api/users'

      const res = await fetch(url, {
        method,
        headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })

      if (res.status === 401) {
        router.push('/login')
        return
      }
      if (!res.ok) {
        const err = await res.json().catch(() => null)
        throw new Error(err?.message || 'Error al guardar')
      }

      router.push('/users')
    } catch (e: any) {
      alert(e.message || 'Error al guardar')
    } finally {
      loading.value = false
    }
  })
}

function onCancel() {
  router.push('/users')
}

async function onDelete() {
  if (!id) return
  const ok = confirm('Eliminar usuario?')
  if (!ok) return
  loading.value = true
  try {
    const res = await fetch(`/api/users/${id}`, {
      method: 'DELETE',
      headers: getAuthHeaders(),
    })
    if (res.status === 401) {
      router.push('/login')
      return
    }
    if (!res.ok) throw new Error('No se pudo eliminar')
    router.push('/users')
  } catch {
    alert('Error al eliminar usuario')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!isEdit.value) return

  const passedUser = (window.history && (window.history.state as any)?.user) || null
  if (passedUser) {
    form.email = passedUser.email ?? ''
    form.roles = Array.isArray(passedUser.roles) ? passedUser.roles : []
    form.is_active = !!passedUser.is_active
    return
  }

  try {
    const s = sessionStorage.getItem('editingUser')
    if (s) {
      const su = JSON.parse(s)
      form.email = su.email ?? ''
      form.roles = Array.isArray(su.roles) ? su.roles : []
      form.is_active = !!su.is_active
      sessionStorage.removeItem('editingUser')
      return
    }
  } catch (e) {
    // ignore
  }

  fetchUser()
})
</script>

<style scoped>
.user-form-view {
  max-width: 840px;
  margin: 0 auto;
  padding: 12px;
}

.header {
  display: block;
  /* justify-content: space-between;
  align-items: flex-start; */
  margin-bottom: 14px;
  gap: 12px;
}

.header h2 {
  margin: 0 0 6px;
}

.header p {
  margin: 4px 0 0;
  color: #6b7280;
  font-size: 13px;
}

.small { color: #6b7280; font-size: 13px; margin-bottom: 12px; }

.users-card { border-radius: 12px; border: 1px solid rgba(15,23,42,0.06); padding: 16px; }

.form-actions { display: flex; gap: 8px; margin-top: 18px; }
</style>
