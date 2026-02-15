<template lang="pug">
  div.sidebar-inner
    div.logo-container
      img.logo(src="@/assets/logo.svg" alt="Logo")
      //- span.logo-text MyAdmin
    el-menu(:default-active="activeMenu" class="el-menu-vertical-demo" background-color="transparent" text-color="#6b7280" active-text-color="#111827" router)
      // Generar items dinámicamente desde las rutas del router
      el-menu-item(v-for="item in menuItems" :key="item.path" :index="item.path")
        el-icon
          component(:is="icons[item.icon]" )
        span {{ item.title }}
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
import { ref, onMounted, reactive, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import userAvatar from '@/assets/user.png'
import * as Icons from '@element-plus/icons-vue'

const route = useRoute()
const router = useRouter()
const activeMenu = ref(route.path)

const auth = useAuthStore()

const user = reactive({ name: 'John Doe', avatar: userAvatar })

const icons: Record<string, any> = Icons as any

const menuItems = computed(() => {
  const all = router.getRoutes()
  const items = all
    .filter(r => r.meta && (r.meta as any).icon && (r.meta as any).requiresAuth)
    .map(r => ({ path: r.path, name: r.name, meta: r.meta }))
    // remove duplicates and only root-level menu entries
    .filter((v, i, a) => v.path && a.findIndex(x => x.path === v.path) === i && v.path !== '/' )

  // filtrar por roles
  const userRoles = (auth.user && auth.user.roles) ? auth.user.roles.map((rr: string) => rr.toLowerCase().replace(/^role_/, '')) : []
  const isAdmin = userRoles.includes('admin') || userRoles.includes('role_admin')

  return items
    .filter(it => {
      const roles = (it.meta && (it.meta as any).roles) as string[] | undefined
      if (!roles) return true
      if (isAdmin) return true
      const allowed = roles.map(r => r.toLowerCase())
      return userRoles.some((ur: string) => allowed.includes(ur))
    })
    .map(it => ({ path: it.path, title: (it.meta && (it.meta as any).title) || String(it.name), icon: (it.meta && (it.meta as any).icon) || 'Document' }))
})

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
