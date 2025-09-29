<template lang="pug">
el-container.dashboard-layout
  el-aside.sidebar(width="200px")
    el-menu(:default-active="$route.path" router)
      el-menu-item(index="/dashboard") Dashboard
      el-menu-item(index="/dashboard/profile") Perfil
      el-menu-item(index="/dashboard/settings") Configuración
  el-container
    el-header.topbar
      div.flex-grow
      el-button(type="danger" size="small" @click="logout") Cerrar sesión

    el-main.main-content
      router-view
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
const logout = () => {
  router.push('/login')
}
</script>

<style scoped>
.dashboard-layout {
  height: 100vh;
}

.sidebar {
  background-color: #2d3a4b;
  color: white;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 20px;
  background-color: #fff;
  border-bottom: 1px solid #e0e0e0;
  height: 60px;
}

.flex-grow {
  flex-grow: 1;
}

.main-content {
  padding: 30px;
  background-color: #f5f7fa;
  height: calc(100vh - 60px);
  overflow-y: auto;
}
</style>
