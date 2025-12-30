<template lang="pug">
  el-container.login-container
    el-form(:model="form" :rules="rules" ref="formRef" class="login-form")
      div.logo-wrapper
        img.logo(src="../assets/Aquiles-logo.png" alt="Logo")
      el-alert(v-if="showError" title="Credenciales inválidas" type="error" class="login-alert")
      el-form-item(prop="email")
        el-input(v-model="form.email" placeholder="Usuario")
      el-form-item(prop="password")
        el-input(v-model="form.password" type="password" placeholder="Contraseña")
      el-form-item
        el-button(type="primary" @click="onSubmit" block) Iniciar sesión
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'

const router = useRouter()
const formRef = ref(null)
const showError = ref(false)

const form = reactive({
  email: '',
  password: '',
})

const rules = {
  email: [
    { required: true, message: 'Email is required', trigger: 'blur' },
    { type: 'email', message: 'Invalid email', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Password is required', trigger: 'blur' },
    { min: 4, message: 'Password must be at least 4 characters', trigger: 'blur' },
  ],
}

const onSubmit = () => {
  formRef.value.validate((valid) => {
    if (valid) {
      fetch('/api/login_check', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: form.email,
          password: form.password,
        }),
      })
        .then((response) => {
          if (!response.ok) {
            showError.value = true
            throw new Error('Login fallido')
          }
          return response.json()
        })
        .then((data) => {
          if (data.token) {
            localStorage.setItem('jwt_token', data.token)
            ElMessage.success('¡Inicio de sesión exitoso!')
            showError.value = false
            router.push('/home')
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
</script>

<style scoped>
.login-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
.login-form {
  width: 300px;
  padding: 20px;
}
.login-alert {
  margin-bottom: 20px;
  width: 100%;
}

.logo-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
  width: 100%;
}
.logo {
  max-width: 220px;
  width: 50%;
  height: auto;
}
</style>
