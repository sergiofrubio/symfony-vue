<template lang="pug">
  div.default-layout
    aside.sidebar
      SidebarMenu
    header.header
      HeaderBar
    main.content
      router-view
      //- esto es una prueba
</template>

<script lang="ts" setup></script>

<style lang="scss">
.default-layout {
  display: grid;
  grid-template-areas: 'sidebar header' 'sidebar content';
  grid-template-columns: 220px 1fr;
  grid-template-rows: 72px 1fr;
  gap: 20px;
  height: 100vh; /* asegurar que la app encaje en la pantalla */
  background: #f6f8fb;
  padding: 12px;
  box-sizing: border-box;
}
.sidebar {
  grid-area: sidebar;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(23, 43, 77, 0.06);
  padding: 8px;
  grid-row: 1 / -1; /* ocupar ambas filas (header + content) */
  height: calc(100vh - 44px); /* mantener dentro de la pantalla */
}
.header {
  grid-area: header;
  background: transparent;
  display: flex;
  align-items: center;
  padding: 0 12px;
}
.content {
  grid-area: content;
  // padding: 24px;
  overflow-y: auto;
  background: transparent;
}

/* Make inner app content stand out with rounded container */
// .content > .router-view, .content > * {
//   min-height: calc(100vh - 140px);
// }
</style>
