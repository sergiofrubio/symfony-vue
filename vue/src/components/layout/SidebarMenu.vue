<template lang="pug">
.sidebar-wrapper
  el-menu.sidebar-menu(
    :default-active="activeMenu"
    :collapse="isCollapse"
    :collapse-transition="true"
    router
  )
    el-menu-item(
      v-for="item in menuItems"
      :key="item.path"
      :index="item.path"
    )
      el-icon
        component(:is="icons[item.icon] || icons.Document")
      template(#title)
        span {{ item.title }}
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import * as Icons from '@element-plus/icons-vue'

defineProps<{
  isCollapse: boolean
}>()

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const activeMenu = ref(route.path)

watch(
  () => route.path,
  (newPath) => {
    // Si estamos en subrutas de facturas o usuarios, iluminar el elemento padre correspondiente
    if (newPath.startsWith('/users')) {
      activeMenu.value = '/users'
    } else if (newPath.startsWith('/invoices')) {
      activeMenu.value = '/invoices'
    } else {
      activeMenu.value = newPath
    }
  },
  { immediate: true }
)

const icons: Record<string, any> = Icons as any

const menuItems = computed(() => {
  const all = router.getRoutes()
  const items = all
    .filter(
      (r) =>
        r.meta &&
        (r.meta as any).show === true &&
        (r.meta as any).icon &&
        (r.meta as any).requiresAuth
    )
    .map((r) => ({ path: r.path, name: r.name, meta: r.meta }))
    .filter(
      (v, i, a) =>
        v.path &&
        a.findIndex((x) => x.path === v.path) === i &&
        v.path !== '/'
    )

  // Filtrar por roles
  const userRoles = authStore.user?.roles
    ? authStore.user.roles.map((rr: string) => rr.toLowerCase().replace(/^role_/, ''))
    : []
  const isAdmin = userRoles.includes('admin') || userRoles.includes('role_admin')

  return items
    .filter((it) => {
      const roles = (it.meta && (it.meta as any).roles) as string[] | undefined
      if (!roles) return true
      if (isAdmin) return true
      const allowed = roles.map((r) => r.toLowerCase())
      return userRoles.some((ur: string) => allowed.includes(ur))
    })
    .map((it) => ({
      path: it.path,
      title: (it.meta && (it.meta as any).title) || String(it.name),
      icon: (it.meta && (it.meta as any).icon) || 'Document',
    }))
})
</script>

<style scoped>
.sidebar-wrapper {
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  border-right: 1px solid #e2e8f0;
  box-sizing: border-box;
}

.sidebar-menu {
  flex: 1;
  border-right: none;
  background-color: transparent;
  padding-top: 12px;
}

/* Ancho fijo cuando no está colapsado */
.sidebar-menu:not(.el-menu--collapse) {
  width: 230px;
}

:deep(.el-menu-item) {
  margin: 4px 10px;
  border-radius: 8px;
  height: 44px;
  line-height: 44px;
  font-weight: 500;
  color: #475569;
  transition: all 0.15s ease;
}

:deep(.el-menu-item:hover) {
  background-color: #f1f5f9;
  color: #0f172a;
}

:deep(.el-menu-item.is-active) {
  background-color: #ecfdf5;
  color: #059669;
  font-weight: 600;
}

:deep(.el-menu--collapse .el-menu-item) {
  margin: 4px 6px;
  padding: 0 !important;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
