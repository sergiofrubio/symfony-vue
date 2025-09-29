<template lang="pug">
el-header.topbar
  div.flex-grow
  NotificationBell
  el-button(type="danger" @click="$emit('logout')") Cerrar sesión
</template>

<script setup>
import NotificationBell from '../common/NotificationBell.vue'
</script>

<style scoped>
.topbar {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 0 20px;
  background-color: #fff;
  border-bottom: 1px solid #eee;
}
.flex-grow {
  flex-grow: 1;
}
</style>
