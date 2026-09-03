<template lang="pug">
.purchases-module
  .page-header
    h2.title Pedidos de Compra (Proveedores)
    el-button(type="primary" @click="$router.push('/purchases/new')")
      el-icon.mr-1
        Plus
      | Nuevo Pedido

  DataTable(
    :data="purchases"
    :loading="loading"
    :total-items="totalItems"
    search-placeholder="Buscar pedido..."
    @search="handleSearch"
    @update:page="handlePageChange"
  )
    el-table-column(prop="id" label="ID" width="80")
    el-table-column(prop="orderNumber" label="Nº Pedido" width="160")
      template(#default="{ row }")
        span.font-semibold {{ row.orderNumber || 'Borrador' }}
    el-table-column(prop="supplier.name" label="Proveedor" sortable)
    el-table-column(prop="date" label="Fecha" width="140")
      template(#default="{ row }")
        span {{ formatDate(row.date) }}
    el-table-column(prop="totalAmount" label="Total" width="130")
      template(#default="{ row }")
        span.font-semibold {{ Number(row.totalAmount || 0).toFixed(2) }} €
    el-table-column(prop="status" label="Estado" width="130")
      template(#default="{ row }")
        el-tag(:type="statusTagType(row.status)") {{ statusLabel(row.status) }}
    el-table-column(label="Acciones" width="160" align="right")
      template(#default="{ row }")
        el-button(size="small" @click="$router.push(`/purchases/${row.id}/edit`)") Editar
        el-popconfirm(title="¿Eliminar pedido?" @confirm="handleDelete(row.id)")
          template(#reference)
            el-button(size="small" type="danger") Eliminar
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { Plus } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';
import DataTable from '@/components/common/DataTable.vue';
import { apiClient } from '@/api/client';

const purchases = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const currentPage = ref(1);
const search = ref('');

function formatDate(dateStr: string) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString();
}

function statusTagType(status: string) {
  switch (status) {
    case 'ordered': return 'warning';
    case 'received': return 'success';
    case 'cancelled': return 'danger';
    default: return 'info';
  }
}

function statusLabel(status: string) {
  switch (status) {
    case 'draft': return 'Borrador';
    case 'ordered': return 'Enviado';
    case 'received': return 'Recibido';
    case 'cancelled': return 'Cancelado';
    default: return status;
  }
}

async function loadPurchases() {
  loading.value = true;
  try {
    let url = `/api/purchase_orders?page=${currentPage.value}`;
    const data = await apiClient(url);
    purchases.value = data['hydra:member'] || data['member'] || data;
    totalItems.value = data['hydra:totalItems'] || purchases.value.length;
  } catch (err: any) {
    ElMessage.error(err.message || 'Error cargando pedidos de compra');
  } finally {
    loading.value = false;
  }
}

function handleSearch(q: string) {
  search.value = q;
  currentPage.value = 1;
  loadPurchases();
}

function handlePageChange(p: number) {
  currentPage.value = p;
  loadPurchases();
}

async function handleDelete(id: number) {
  try {
    await apiClient(`/api/purchase_orders/${id}`, { method: 'DELETE' });
    ElMessage.success('Pedido eliminado correctamente');
    loadPurchases();
  } catch (err: any) {
    ElMessage.error(err.message || 'Error eliminando pedido');
  }
}

onMounted(() => {
  loadPurchases();
});
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.title {
  margin: 0;
  font-size: 24px;
}
.font-semibold {
  font-weight: 600;
}
.mr-1 {
  margin-right: 6px;
}
</style>
