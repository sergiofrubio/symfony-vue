<template lang="pug">
  div.sidebar-inner
    div.logo-container
      img.logo(src="/favicon.ico" alt="Logo")
      //- span.logo-text MyAdmin
    el-menu(:default-active="activeMenu", class="el-menu-vertical-demo", background-color="transparent", text-color="#6b7280", active-text-color="#111827", router)
      el-menu-item(index="/home")
        el-icon
          House
        span Dashboard
      el-menu-item(index="/users")
        el-icon
          User
        span Usuarios
      el-menu-item(index="/invoices")
        el-icon
          Document
        span Facturas
    div.sidebar-footer
      // Usuario (avatar + nombre) como botón desplegable
      el-dropdown(trigger="click")
        el-button(type="text" class="sidebar-footer-btn")
          el-avatar(size="small" :src="user.avatar")
          span.username {{ user.name }}
        template(#dropdown)
          el-dropdown-menu
            el-dropdown-item(@click="goProfile") Perfil
            el-dropdown-item(class="logout-item" divided @click="logout") Cerrar sesión
</template>

<script lang="ts" setup>
import { ref, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'

const route = useRoute()
const router = useRouter()
const activeMenu = ref(route.path)

const auth = useAuthStore()

const user = reactive({ name: 'John Doe', avatar: '/user.png' })

function goProfile() {
  router.push('/profile')
}

function logout() {
  auth.logout()
  ElMessage({ message: 'Sesión cerrada', type: 'success' })
  router.push('/login')
}

onMounted(() => {
  // Escucha cambios de ruta
  router.afterEach((to) => {
    activeMenu.value = to.path
  })
})
</script>

<style>
.logo-container {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  height: 60px;
  padding: 0px 20px;
}

.logo {
  width: 30px;
  height: 30px;
  margin-right: 10px;
}

.logo-text {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
}

.el-menu-vertical-demo {
  flex: 1;
  border-right: none;
  margin-top: 8px;
  overflow: visible;
}

.sidebar-inner {
  display: flex;
  flex-direction: column;
  height: 100%;
  justify-content: space-between; /* asegurar footer siempre visible */
}

.sidebar-footer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(15, 23, 42, 0.04);
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.sidebar-footer-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  justify-content: flex-start;
  padding: 8px 12px;
  border-radius: 8px;
  color: #111827;
}

.sidebar-footer-btn:hover {
  background: rgba(15,23,42,0.04);
}

.sidebar-footer .el-avatar { margin-right: 6px }

.custom-trigger-notif {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 6px;
}
</style>
