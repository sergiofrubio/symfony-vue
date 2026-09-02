<template lang="pug">
el-container.dashboard-layout
  el-header.layout-header(height="60px")
    HeaderBar(:is-collapse="isCollapse" @toggle-collapse="isCollapse = !isCollapse")
  el-container.layout-body
    el-aside.layout-aside(:width="isCollapse ? '64px' : '230px'")
      SidebarMenu(:is-collapse="isCollapse")
    el-main.layout-main
      .main-content-wrapper
        router-view
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import HeaderBar from '@/components/layout/HeaderBar.vue'
import SidebarMenu from '@/components/layout/SidebarMenu.vue'

const isCollapse = ref(false)
</script>

<style scoped>
.dashboard-layout {
  height: 100vh;
  width: 100vw;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background-color: #f8fafc;
}

.layout-header {
  padding: 0;
  z-index: 10;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.layout-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.layout-aside {
  height: 100%;
  transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  overflow-x: hidden;
  overflow-y: auto;
  background-color: #ffffff;
}

.layout-main {
  flex: 1;
  background-color: #f8fafc;
  padding: 24px;
  overflow-y: auto;
  box-sizing: border-box;
}

.main-content-wrapper {
  max-width: 1300px;
  margin: 0 auto;
}
</style>
