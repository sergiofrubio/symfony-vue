<template lang="pug">
el-aside(:width="collapsed ? '64px' : '200px'" class="sidebar")
  el-menu(:default-active="$route.path" class="el-menu-vertical-demo" router)
    el-menu-item(index="/dashboard")
      i.el-icon-menu
      span(v-if="!collapsed") Dashboard
    el-menu-item(index="/dashboard/profile")
      i.el-icon-user
      span(v-if="!collapsed") Perfil
    el-menu-item(index="/dashboard/settings")
      i.el-icon-setting
      span(v-if="!collapsed") Configuración
  el-button.icon-only.toggle(@click="$emit('toggle')")
    i.el-icon-s-fold
</template>

<script setup>
defineProps({ collapsed: Boolean })
</script>

<style scoped>
.sidebar {
  transition: width 0.2s;
}
.toggle {
  position: absolute;
  bottom: 10px;
  left: 10px;
}
@media (max-width: 768px) {
  .sidebar {
    position: absolute;
    z-index: 1000;
    height: 100%;
  }
}
</style>
