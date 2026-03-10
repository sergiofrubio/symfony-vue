<template lang="pug">
.user-form-view
  .form-header
    .header-copy
      h2 {{ isEditMode ? 'Editar usuario' : 'Nuevo usuario' }}
      p {{ isEditMode ? 'Actualiza los datos del usuario manteniendo un diseño limpio y consistente.' : 'Crea una nueva cuenta con los permisos y estado adecuados.' }}
    el-button(link @click="goBack") ← Volver al listado

  el-card.form-card(shadow="never")
    el-form(
      ref="formRef"
      :model="form"
      :rules="rules"
      label-position="top"
      class="user-form"
      v-loading="loading"
    )
      .form-grid
        el-form-item(label="Email" prop="email")
          el-input(v-model="form.email" placeholder="usuario@empresa.com")

        el-form-item(label="Estado" prop="is_active")
          el-switch(v-model="form.is_active" active-text="Activo" inactive-text="Inactivo")

      el-form-item(label="Roles" prop="roles")
        el-select(v-model="form.roles" multiple collapse-tags collapse-tags-tooltip placeholder="Selecciona los roles")
          el-option(v-for="role in rolesOptions" :key="role" :label="role" :value="role")

      .form-grid
        el-form-item(:label="isEditMode ? 'Nueva contraseña (opcional)' : 'Contraseña'" prop="password")
          el-input(v-model="form.password" type="password" show-password :placeholder="isEditMode ? 'Solo si deseas cambiarla' : 'Mínimo 6 caracteres'")

        el-form-item(label="Confirmar contraseña" prop="passwordConfirm")
          el-input(v-model="form.passwordConfirm" type="password" show-password placeholder="Repite la contraseña")

      .form-actions
        el-button(type="primary" :loading="saving" @click="onSubmit")
          | {{ isEditMode ? 'Guardar cambios' : 'Crear usuario' }}
        el-button(@click="goBack") Cancelar
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { getAuthHeaders } from '@/api/auth'

defineOptions({
  name: 'UserFormView',
})

type UserPayload = {
  id: number
  email: string
  roles: string[]
  is_active: boolean
}

const router = useRouter()
const route = useRoute()
const formRef = ref<FormInstance>()
const loading = ref(false)
const saving = ref(false)

const rolesOptions = ['ROLE_USER', 'ROLE_ADMIN']

const form = reactive({
  email: '',
  roles: ['ROLE_USER'] as string[],
  is_active: true,
  password: '',
  passwordConfirm: '',
})

const userId = computed(() => Number(route.params.id))
const isEditMode = computed(() => Number.isFinite(userId.value) && userId.value > 0)

const rules = computed<FormRules>(() => ({
  email: [
    { required: true, message: 'El email es obligatorio', trigger: 'blur' },
    { type: 'email', message: 'Introduce un email válido', trigger: ['blur', 'change'] },
  ],
  roles: [{ type: 'array', required: true, min: 1, message: 'Selecciona al menos un rol', trigger: 'change' }],
  password: [
    {
      validator: (_rule, value, callback) => {
        if (!value && isEditMode.value) return callback()
        if (!value || String(value).length < 6) return callback(new Error('La contraseña debe tener al menos 6 caracteres'))
        callback()
      },
      trigger: 'blur',
    },
  ],
  passwordConfirm: [
    {
      validator: (_rule, value, callback) => {
        if (!form.password && !value && isEditMode.value) return callback()
        if (value !== form.password) return callback(new Error('Las contraseñas no coinciden'))
        callback()
      },
      trigger: 'blur',
    },
  ],
}))

function goBack() {
  router.push('/users')
}

async function fetchUserForEdit() {
  if (!isEditMode.value) return

  loading.value = true
  try {
    const res = await fetch(`/api/users?search=${userId.value}&page=1&limit=1`, {
      headers: getAuthHeaders(),
    })

    if (res.status === 401) {
      router.push('/login')
      return
    }

    if (!res.ok) throw new Error('No se pudo cargar el usuario')

    const data = await res.json()
    const user = (Array.isArray(data.items) ? data.items : []).find((item: UserPayload) => item.id === userId.value)

    if (!user) {
      ElMessage.warning('Usuario no encontrado')
      goBack()
      return
    }

    form.email = user.email ?? ''
    form.roles = Array.isArray(user.roles) && user.roles.length ? user.roles : ['ROLE_USER']
    form.is_active = Boolean(user.is_active)
  } catch {
    ElMessage.error('Error al cargar usuario')
  } finally {
    loading.value = false
  }
}

async function onSubmit() {
  try {
    await formRef.value?.validate()
  } catch {
    return
  }

  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      email: form.email,
      roles: form.roles,
      is_active: form.is_active,
    }

    if (form.password) payload.password = form.password

    const endpoint = isEditMode.value ? `/api/users/${userId.value}` : '/api/users'
    const method = isEditMode.value ? 'PUT' : 'POST'

    const res = await fetch(endpoint, {
      method,
      headers: {
        ...getAuthHeaders(),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    if (res.status === 401) {
      router.push('/login')
      return
    }

    if (!res.ok) throw new Error('No se pudo guardar')

    ElMessage.success(isEditMode.value ? 'Usuario actualizado' : 'Usuario creado')
    goBack()
  } catch {
    ElMessage.error('No fue posible guardar el usuario')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchUserForEdit()
})
</script>

<style scoped>
.user-form-view {
  max-width: 920px;
  margin: 0 auto;
  padding: 8px 12px;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 14px;
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

.form-card {
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 12px;
}

.user-form {
  width: 100%;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 6px;
}

@media (max-width: 900px) {
  .form-header {
    flex-direction: column;
    align-items: stretch;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 0;
  }
}
</style>
