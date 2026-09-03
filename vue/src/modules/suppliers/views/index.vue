<template lang="pug">
.suppliers-module
  .page-header
    h2.title Proveedores
    el-button(type="primary" @click="$router.push('/suppliers/new')")
      el-icon.mr-1
        Plus
      | Nuevo Proveedor

  DataTable(
    :data="suppliers"
    :loading="loading"
    :total-items="totalItems"
    search-placeholder="Buscar proveedor..."
    @search="handleSearch"
    @update:page="handlePageChange"
  )
    el-table-column(prop="id" label="ID" width="80")
    el-table-column(prop="name" label="Nombre / Razón Social" sortable)
    el-table-column(prop="taxId" label="CIF / NIF" width="140")
    el-table-column(prop="email" label="Email")
    el-table-column(prop="phone" label="Teléfono" width="150")
    el-table-column(label="Acciones" width="160" align="right")
      template(#default="{ row }")
        el-button(size="small" @click="$router.push(`/suppliers/${row.id}/edit`)") Editar
        el-popconfirm(title="¿Eliminar este proveedor?" @confirm="handleDelete(row.id)")
          template(#reference)
            el-button(size="small" type="danger") Eliminar
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { Plus } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';
import DataTable from '@/components/common/DataTable.vue';
import { apiClient } from '@/api/client';

const suppliers = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const currentPage = ref(1);
const search = ref('');

async function loadSuppliers() {
  loading.value = true;
  try {
    let url = `/api/suppliers?page=${currentPage.value}`;
    if (search.value) {
      url += `&name=${encodeURIComponent(search.value)}`;
    }
    const data = await apiClient(url);
    suppliers.value = data['hydra:member'] || data['member'] || data;
    totalItems.value = data['hydra:totalItems'] || suppliers.value.length;
  } catch (err: any) {
    ElMessage.error(err.message || 'Error cargando proveedores');
  } finally {
    loading.value = false;
  }
}

function handleSearch(q: string) {
  search.value = q;
  currentPage.value = 1;
  loadSuppliers();
}

function handlePageChange(p: number) {
  currentPage.value = p;
  loadSuppliers();
}

async function handleDelete(id: number) {
  try {
    await apiClient(`/api/suppliers/${id}`, { method: 'DELETE' });
    ElMessage.success('Proveedor eliminado correctamente');
    loadSuppliers();
  } catch (err: any) {
    ElMessage.error(err.message || 'Error eliminando proveedor');
  }
}

onMounted(() => {
  loadSuppliers();
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
.mr-1 {
  margin-right: 6px;
}
</style>
