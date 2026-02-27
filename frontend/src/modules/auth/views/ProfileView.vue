<template lang="pug">
div.profile-view
  .profile-container
    el-card.profile-card
      .profile-grid
        .left-column
          .avatar-wrap
            img.avatar(:src="avatarPreview || form.avatar || avatarPhoto" alt="avatar")
            div.avatar-actions
              input(type="file" accept="image/*" @change="onAvatarChange" id="avatarInput" hidden)
              el-button(type="primary" plain size="small" @click="triggerAvatarSelect") Cambiar foto
              el-button(type="text" size="small" @click="removeAvatar") Eliminar
          .info-block
            h3 {{ form.email }}
            div.roles
              el-tag(v-for="r in form.roles" :key="r" style="margin-right:6px") {{ r }}
            div.meta Último login: {{ formatDate(form.last_login) }}

        .right-column
          el-form(:model="form" :rules="rules" ref="formRef" label-position="top")
            el-form-item(label="Nombre" prop="name")
              el-input(v-model="form.name" placeholder="Nombre")

            el-form-item(label="Email" prop="email")
              el-input(v-model="form.email" placeholder="Correo electrónico")

            el-form-item(label="Activo")
              el-switch(v-model="form.is_active")

            el-divider
            h4 Cambio de contraseña
            el-form-item(label="Nueva contraseña" prop="password")
              el-input(v-model="password" type="password" placeholder="Dejar vacío para no cambiar" show-password)
            el-form-item(label="Confirmar contraseña" prop="passwordConfirm")
              el-input(v-model="passwordConfirm" type="password" placeholder="Confirmar nueva contraseña" show-password)

            .actions(style="display:flex;gap:10px;margin-top:14px")
              el-button(type="primary" @click="onSave" :loading="saving") Guardar cambios
              el-button(type="warning" @click="onRefresh" :disabled="loading") Refrescar
              //- el-button(type="danger" @click="onLogout") Cerrar sesión
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import userAvatar from '@/assets/user.png'

const router = useRouter()
const formRef = ref<any>(null)
const loading = ref(false)
const saving = ref(false)

const form = reactive({
  id: null as number | null,
  name: '',
  email: '',
  avatar: '' as string | null,
  roles: [] as string[],
  is_active: false,
  last_login: null as string | null,
})

const password = ref('')
const passwordConfirm = ref('')
const avatarFile = ref<File | null>(null)
const avatarPreview = ref<string | null>(null)
const avatarPhoto = userAvatar // imagen por defecto si no hay avatar

const rules = {
  name: [{ required: true, message: 'Nombre requerido', trigger: 'blur' }],
  email: [
    { required: true, message: 'Email requerido', trigger: 'blur' },
    { type: 'email', message: 'Email inválido', trigger: ['blur', 'change'] }
  ],
  password: [{ min: 4, message: 'La contraseña debe tener al menos 4 caracteres', trigger: 'blur' }],
  passwordConfirm: [{ validator: (rule: any, value: string) => {
    if (!password.value && !value) return true
    return value === password.value
  }, message: 'Las contraseñas no coinciden', trigger: 'blur' }]
}

function getAuthHeaders() {
  const token = localStorage.getItem('jwt_token')
  if (!token) { router.push('/login'); return {} }
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
    form.name = data.name ?? ''
    form.email = data.email ?? ''
    form.avatar = data.avatar ?? null
    if (Array.isArray(data.roles)) {
      form.roles = data.roles.map((r: any) => typeof r === 'string' ? r : (r?.slug ?? r?.name ?? '')).filter((v: string) => !!v)
    } else {
      form.roles = []
    }
    form.is_active = Boolean(data.is_active)
    form.last_login = data.last_login ?? null
  } catch (e) {
    ElMessage.error('No se pudo cargar el perfil')
  } finally { loading.value = false }
}

function triggerAvatarSelect() {
  const input = document.getElementById('avatarInput') as HTMLInputElement | null
  input?.click()
}

function onAvatarChange(e: Event) {
  const target = e.target as HTMLInputElement
  const f = target.files && target.files[0]
  if (!f) return
  avatarFile.value = f
  try { avatarPreview.value = URL.createObjectURL(f) } catch { avatarPreview.value = null }
}

function removeAvatar() {
  avatarFile.value = null
  avatarPreview.value = null
  form.avatar = ''
}

async function uploadAvatarIfNeeded() {
  if (!avatarFile.value || !form.id) return
  try {
    const fd = new FormData()
    fd.append('avatar', avatarFile.value)
    const res = await fetch(`/api/users/${form.id}/avatar`, { method: 'POST', headers: { Authorization: localStorage.getItem('jwt_token') ? `Bearer ${localStorage.getItem('jwt_token')}` : '' }, body: fd })
    if (res.ok) {
      const data = await res.json()
      form.avatar = data.avatar ?? form.avatar
    }
  } catch {}
}

async function onSave() {
  // validar formulario
  try { await formRef.value.validate() } catch { return }

  if (password.value && password.value.length < 4) { ElMessage.error('La contraseña es muy corta'); return }

  saving.value = true
  try {
    const payload: any = { name: form.name, email: form.email, is_active: form.is_active }
    if (password.value) payload.password = password.value

    const res = await fetch(`/api/users/${form.id}`, {
      method: 'PUT',
      headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('No se pudo guardar')

    // subir avatar si corresponde
    await uploadAvatarIfNeeded()

    ElMessage.success('Perfil actualizado')
    password.value = ''
    passwordConfirm.value = ''
    await fetchProfile()
  } catch (e) { ElMessage.error('Error al guardar') } finally { saving.value = false }
}

function formatDate(v: string | null) { if (!v) return '-'; try { return new Date(v).toLocaleString() } catch { return v } }

function onLogout() { localStorage.removeItem('jwt_token'); sessionStorage.removeItem('jwt_token'); router.push('/login') }

function onRefresh() { fetchProfile() }

onMounted(() => { fetchProfile() })
</script>

<style scoped>
.profile-view { padding: 12px; margin: 0 auto }
.profile-card { padding: 18px; border-radius: 12px; background: #fff; box-shadow: 0 8px 24px rgba(15,23,42,0.06) }
.profile-grid { display: grid; grid-template-columns: 280px 1fr; gap: 20px; align-items: start }
.avatar-wrap { display:flex; flex-direction:column; align-items:center; gap:10px }
.avatar { width: 140px; height: 140px; object-fit:cover; border-radius: 12px; border: 1px solid rgba(15,23,42,0.06) }
.avatar-actions { display:flex; gap:8px }
.info-block { text-align:center }
.info-block h3 { margin: 6px 0 4px; font-size:18px }
.info-block .roles { margin:8px 0 }
.info-block .meta { color: #6b7280; font-size:13px }

.right-column .el-form { width:100% }
.right-column h4 { margin: 12px 0 6px }
.actions { margin-top: 12px }

@media (max-width: 880px) {
  .profile-grid { grid-template-columns: 1fr; }
  .avatar { width: 110px; height: 110px }
  .header { flex-direction: column }
}
</style>
