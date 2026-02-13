<template lang="pug">
  div.dashboard-home
    .home-header
      h2.greeting Bienvenido al Panel
      p.subtle Aquí tienes un resumen claro y conciso de tu actividad

    .card-grid
      el-card.card-box
        .card-value 128
        .card-title Total de Facturas

      el-card.card-box
        .card-value 34
        .card-title Clientes Activos

      el-card.card-box
        .card-value €4.500
        .card-title Ingresos del Mes
</template>

<script lang="ts" setup>
// Datos estáticos por ahora
</script>

<style scoped>
.dashboard-home {
  max-width: 1100px;
  margin: 0 auto;
  padding: 8px 12px;
}

.home-header {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 18px;
}

.greeting {
  margin: 0;
  font-size: 22px;
  color: #0f172a;
}

.subtle {
  margin: 0;
  color: #6b7280;
  font-size: 13px;
}

.card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.card-box {
  padding: 20px;
  border-radius: 12px;
  background: #ffffff;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-start;
}

.card-value {
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
}

.card-title {
  font-size: 13px;
  color: #6b7280;
}
</style>
