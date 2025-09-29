<template lang="pug">
div.dashboard-home
  el-card
    h2 Bienvenido al Panel de Control
    p Aquí puedes ver un resumen general de tu actividad.

  div.card-grid
    el-card.card-box
      h3 Total de Facturas
      p 128

    el-card.card-box
      h3 Clientes Activos
      p 34

    el-card.card-box
      h3 Ingresos del Mes
      p €4.500
</template>

<script setup>
// Datos estáticos por ahora
</script>

<style scoped>
.dashboard-home {
  max-width: 1000px;
  margin: 0 auto;
}

.card-grid {
  display: flex;
  gap: 20px;
  margin-top: 20px;
}

.card-box {
  flex: 1;
  min-width: 200px;
}
</style>
