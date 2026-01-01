<template lang="pug">
  el-container.login-container
    .bg-decor
    el-card.login-card.shadow
      .login-header
        img.logo(src="../assets/Aquiles-logo.png" alt="Logo")
        h2.title Bienvenido de vuelta
        p.subtitle Ingresa a tu cuenta para continuar

      el-form(:model="form" :rules="rules" ref="formRef" class="login-form")
        el-alert(v-if="showError" title="Credenciales inválidas" type="error" class="login-alert")

        el-form-item(prop="email")
          el-input(v-model="form.email" placeholder="Correo electrónico" clearable)

        el-form-item(prop="password")
          el-input(v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Contraseña" show-password)

        el-form-item
          .form-row
            el-checkbox(v-model="remember") Recuérdame
            //- a.forgot(@click.prevent="onForgot") ¿Olvidaste tu contraseña?

        el-form-item
          el-button(type="primary" @click="onSubmit" class="login-button" block) Iniciar sesión

        //- .social-section
        //-   p.social-text O inicia con
        //-   .social-buttons
        //-     el-button(plain icon="google") Google
        //-     el-button(plain icon="facebook") Facebook

</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'

const router = useRouter()
const auth = useAuthStore()
const formRef = ref<any>(null)
const showError = ref(false)
const showPassword = ref(false)
const remember = ref(false)

const form = reactive({
  email: '',
  password: '',
})

const rules = {
  email: [{ required: true, message: 'El correo es requerido', trigger: 'blur' }, { type: 'email', message: 'Correo inválido', trigger: 'blur' }],
  password: [{ required: true, message: 'La contraseña es requerida', trigger: 'blur' }, { min: 4, message: 'La contraseña debe tener al menos 4 caracteres', trigger: 'blur' }],
}

const onSubmit = () => {
  formRef.value.validate((valid: boolean) => {
    if (valid) {
      fetch('/api/login_check', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: form.email, password: form.password }),
      })
        .then((response) => {
          if (!response.ok) {
            showError.value = true
            throw new Error('Login fallido')
          }
          return response.json()
        })
        .then(async (data) => {
          if (data.token) {
            try {
              auth.setToken(data.token)
              await auth.fetchMe()
              ElMessage.success('¡Inicio de sesión exitoso!')
              showError.value = false
              router.push('/home')
            } catch (err) {
              auth.logout()
              showError.value = true
              ElMessage.error('No se pudo obtener datos del usuario')
            }
          } else {
            showError.value = true
          }
        })
        .catch(() => {
          showError.value = true
        })
    }
  })
}

const onForgot = () => {
  router.push('/auth/forgot')
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(180deg, #f5f7fb 0%, #ffffff 100%);
  position: relative;
  padding: 40px 16px;
  color: #0f172a;
}

.bg-decor {
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    radial-gradient(circle at 10% 20%, rgba(99,102,241,0.06), transparent 8%),
    radial-gradient(circle at 80% 80%, rgba(6,182,212,0.04), transparent 12%);
  opacity: 1;
}

.login-card {
  width: 420px;
  max-width: 95%;
  padding: 28px;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(16,24,40,0.08);
  background: rgba(255,255,255,0.98);
  border: 1px solid rgba(15,23,42,0.06);
  color: #0f172a;
}

.login-header {
  text-align: center;
  margin-bottom: 18px;
}
.logo {
  height: 62px;
  margin-bottom: 12px;
  filter: none;
}
.title {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #0b1220;
}
.subtitle {
  margin: 6px 0 0;
  font-size: 13px;
  color: #6b7280;
}

.login-form {
  margin-top: 6px;
}
.login-alert {
  margin-bottom: 12px;
}

.form-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.forgot {
  color: #2563eb;
  font-size: 13px;
  cursor: pointer;
}
.forgot:hover { text-decoration: underline }

.login-button {
  background: linear-gradient(90deg,#2563eb,#06b6d4);
  border: none;
  color: #ffffff;
  font-weight: 600;
  box-shadow: 0 6px 18px rgba(37,99,235,0.18);
}

.social-section { margin-top: 12px; text-align: center }
.social-text { color: #6b7280; margin-bottom: 8px }
.social-buttons el-button { margin: 0 6px }

.el-input__inner {
  background: #ffffff;
  color: #0f172a;
  border-radius: 8px;
}

@media (max-width: 420px) {
  .login-card { padding: 18px }
  .title { font-size: 18px }
}
</style>
