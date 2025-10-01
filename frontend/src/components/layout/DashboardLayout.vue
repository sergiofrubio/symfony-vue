<template lang="pug">
div.default-layout
  aside.sidebar
    SidebarMenu
  header.header
    HeaderBar
  main.content
    router-view
</template>

<script setup>
import SidebarMenu from '@/components/layout/SidebarMenu.vue'
import HeaderBar from '@/components/layout/HeaderBar.vue'
</script>

<style lang="scss">
.default-layout{
  display: grid;
  grid-template-areas: "sidebar header" "sidebar content";
  grid-template-columns: 250px 1fr;
  grid-template-rows: 60px 1fr;
  height: 100vh;
}
.sidebar{
  grid-area: sidebar;
  background-color: #1f2d3d;
}
.header{
  grid-area: header;
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.content{
  grid-area: content;
  padding: 20px;
  overflow-y: auto;
}
</style>
