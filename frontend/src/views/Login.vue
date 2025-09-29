<template lang="pug">
el-container.login-container
  el-form(:model="form" class="login-form" @submit.prevent="login")
    el-form-item
      el-input(v-model="form.username" placeholder="Usuario")
    el-form-item
      el-input(v-model="form.password" type="password" placeholder="Contraseña")
    el-form-item
      el-button(type="primary" native-type="submit" block) Iniciar sesión
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({ username: '', password: '' })

const login = () => {
  // lógica de autenticación
  router.push('/dashboard')
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
}
</style>
