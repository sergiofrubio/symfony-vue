<template lang="pug">
.login-page
  .login-card-container
    el-card.login-card(shadow="always")
      .login-header
        img.login-logo(src="@/assets/logo.svg" alt="Logo Corporativo")
        h1.login-title Acceso Corporativo
        p.login-subtitle Introduce tus credenciales para acceder al sistema

      el-alert.login-alert(
        v-if="showError"
        title="Credenciales incorrectas o usuario no autorizado"
        type="error"
        show-icon
        :closable="false"
      )

      el-form(
        :model="form"
        :rules="rules"
        ref="formRef"
        class="login-form"
        label-position="top"
        @keyup.enter="onSubmit"
      )
        el-form-item(label="Correo electrónico" prop="email")
          el-input(
            v-model="form.email"
            placeholder="usuario@empresa.com"
            :prefix-icon="Message"
            size="large"
            clearable
          )

        el-form-item(label="Contraseña" prop="password")
          el-input(
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            :prefix-icon="Lock"
            size="large"
            show-password
          )

        .form-options
          el-checkbox(v-model="remember") Recordar mi sesión

        el-form-item.submit-item
          el-button(
            type="primary"
            size="large"
            class="submit-button"
            :loading="loading"
            :disabled="loading"
            @click="onSubmit"
          ) Iniciar Sesión

      .login-footer
        p.footer-text © 2026 Enterprise Portal · Acceso seguro y cifrado
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import { Message, Lock } from '@element-plus/icons-vue'

const router = useRouter()
const auth = useAuthStore()
const formRef = ref<any>(null)
const showError = ref(false)
const remember = ref(false)
const loading = ref(false)

const form = reactive({
  email: '',
  password: '',
})

const rules = {
  email: [
    { required: true, message: 'El correo electrónico es requerido', trigger: 'blur' },
    { type: 'email', message: 'Ingresa un correo electrónico válido', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'La contraseña es requerida', trigger: 'blur' },
    { min: 4, message: 'La contraseña debe tener al menos 4 caracteres', trigger: 'blur' },
  ],
}

const onSubmit = async () => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
  } catch {
    return
  }

  loading.value = true
  showError.value = false

  try {
    const response = await fetch('/api/login_check', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: form.email, password: form.password }),
    })

    if (!response.ok) {
      showError.value = true
      throw new Error('Login fallido')
    }

    const data = await response.json()
    if (data.token) {
      auth.setToken(data.token)
      try {
        await auth.fetchMe()
        ElMessage.success('Sesión iniciada correctamente')
        showError.value = false
        router.push('/home')
      } catch {
        auth.logout()
        showError.value = true
        ElMessage.error('No se pudo cargar la información del usuario')
      }
    } else {
      showError.value = true
    }
  } catch {
    showError.value = true
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f1f5f9;
  background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
  background-size: 24px 24px;
  padding: 24px;
  box-sizing: border-box;
}

.login-card-container {
  width: 100%;
  max-width: 440px;
}

.login-card {
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 12px 10px;
}

.login-header {
  text-align: center;
  margin-bottom: 24px;
}

.login-logo {
  height: 48px;
  width: 48px;
  margin-bottom: 12px;
}

.login-title {
  margin: 0 0 6px 0;
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
}

.login-subtitle {
  margin: 0;
  font-size: 13px;
  color: #64748b;
  line-height: 1.4;
}

.login-alert {
  margin-bottom: 20px;
}

.login-form {
  display: flex;
  flex-direction: column;
}

:deep(.el-form-item__label) {
  font-weight: 500;
  color: #334155;
  padding-bottom: 4px;
  font-size: 13px;
}

.form-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.submit-item {
  margin-bottom: 8px;
}

.submit-button {
  width: 100%;
  font-weight: 600;
  font-size: 14px;
  height: 44px;
  border-radius: 8px;
}

.login-footer {
  margin-top: 20px;
  text-align: center;
  border-top: 1px solid #f1f5f9;
  padding-top: 16px;
}

.footer-text {
  margin: 0;
  font-size: 12px;
  color: #94a3b8;
}
</style>
