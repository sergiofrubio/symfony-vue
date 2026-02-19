<template lang="pug">
  div.dashboard-home
    .home-header
      h2.greeting Bienvenido al Panel
      p.subtle Aquí tienes un resumen claro y conciso de tu actividad
    .card-grid
      el-card.card-box
        .card-info
          .card-value 128
          .card-title Total de Facturas
        .card-icon
          Document
      el-card.card-box
        .card-info
          .card-value 34
          .card-title Clientes Activos
        .card-icon
          User
      el-card.card-box
        .card-info
          .card-value €4.500
          .card-title Ingresos del Mes
        .card-icon
          Money
</template>

<script lang="ts" setup>
import { Document, User, Money } from '@element-plus/icons-vue'
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
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  position: relative;
  padding-right: 64px; /* espacio para el icono posicionado a la derecha */
}

.card-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-start;
}

/* el-card renderiza el contenido dentro de .el-card__body, así que
   forzamos la disposición flex en ese contenedor para asegurar
   que .card-info y .card-icon queden en la misma línea */
.card-box .el-card__body {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 0; /* dejamos el padding en .card-box */
  width: 100%;
}

.card-info {
  flex: 1 1 auto;
}

.card-icon {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
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

.card-icon svg {
  display: block;
  width: 36px;
  height: 36px;
  color: #0f172a;
}

.card-icon path {
  stroke: currentColor;
}
</style>
