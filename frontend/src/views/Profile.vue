<template lang="pug">
el-card(shadow="hover")
  h2 Perfil de Usuario
  el-form(:model="form" label-position="top" class="mt-4")
    el-form-item(label="Nombre")
      el-input(v-model="form.name")
    el-form-item(label="Correo electrónico")
      el-input(v-model="form.email")
    el-form-item(label="Contraseña")
      el-input(v-model="form.password" type="password")
    el-form-item
      el-button(type="primary" @click="saveProfile") Guardar Cambios
</template>

<script setup>
import { reactive } from 'vue'

const form = reactive({
  name: 'Sergio Fisica Rubio',
  email: 'sergio@example.com',
  password: ''
})

const saveProfile = () => {
  // lógica para guardar cambios
  console.log('Perfil guardado:', form)
}
</script>

<style scoped>
.mt-4 {
  margin-top: 1rem;
}
</style>
