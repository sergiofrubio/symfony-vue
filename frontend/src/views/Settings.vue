<template lang="pug">
el-card(shadow="hover")
  h2 Configuración
  el-form(:model="settings" label-position="top" class="mt-4")
    el-form-item(label="Idioma")
      el-select(v-model="settings.language" placeholder="Selecciona un idioma")
        el-option(label="Español" value="es")
        el-option(label="Inglés" value="en")
    el-form-item(label="Tema")
      el-switch(v-model="settings.darkMode" active-text="Oscuro" inactive-text="Claro")
    el-form-item
      el-button(type="primary" @click="saveSettings") Guardar Configuración
</template>

<script setup>
import { reactive } from 'vue'

const settings = reactive({
  language: 'es',
  darkMode: false
})

const saveSettings = () => {
  // lógica para guardar configuración
  console.log('Configuración guardada:', settings)
}
</script>

<style scoped>
.mt-4 {
  margin-top: 1rem;
}
</style>
