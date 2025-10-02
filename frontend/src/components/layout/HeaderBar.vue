<template lang="pug">
  div.header-bar
    div.header-left
      span.logo Panel de control
    div.header-right
      el-dropdown
        div.custom-trigger
          el-avatar(size="small" :src="user.avatar")
          span.username {{ user.name }}
        template(#dropdown)
          el-dropdown-menu
            el-dropdown-item(@click="goProfile") Perfil
            el-dropdown-item(@click="settings") Configuración
            el-dropdown-item(divided @click="logout") Cerrar sesión
</template>

<script lang="ts" setup>
import { reactive } from 'vue'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'

const router = useRouter()

// Datos de prueba
const user = reactive({
  name: 'John Doe',
  avatar: 'https://i.pravatar.cc/40',
})

/* function goProfile() {

}

function settings() {

} */

function logout() {
  ElMessage({
    message: 'Sesión cerrada',
    type: 'success',
  })
  router.push('/login')
}
</script>

<style>
.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
  padding: 0 20px;
  background-color: #fff;
  border-bottom: 1px solid #ebeef5;
}

.header-left .logo {
  font-weight: bold;
  font-size: 20px;
  color: #409eff;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* trigger customizado */
.custom-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.username {
  font-weight: 500;
  color: #333;
}

.el-dropdown-menu__item {
  font-family: 'Roboto', sans-serif;
}
</style>
