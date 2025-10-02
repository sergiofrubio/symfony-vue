<template lang="pug">
  div.logo-container
    img.logo(src="" alt="Logo")
    span.logo-text MyAdmin
  el-menu(:default-active="activeMenu", class="el-menu-vertical-demo", background-color="#1f2d3d", text-color="#bfcbd9", active-text-color="#409EFF", router)
    el-menu-item(index="/home")
      el-icon
        House
      span Dashboard
    el-menu-item(index="/users")
      el-icon
        User
      span Usuarios
    //- el-sub-menu(index="users")
    //-   template(#title)
    //-     el-icon
    //-       User
    //-     span Users
    //-   el-menu-item(index="/users") Users List
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const activeMenu = ref(route.path)

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
  justify-content: center;
  height: 60px;
  padding: 10px;
  /* background-color: #1f2a38; */
}

.logo {
  width: 30px;
  height: 30px;
  margin-right: 10px;
}

.logo-text {
  font-size: 18px;
  font-weight: bold;
  color: #fff;
}

.el-menu-vertical-demo {
  flex: 1;
  border-right: none;
}
</style>
