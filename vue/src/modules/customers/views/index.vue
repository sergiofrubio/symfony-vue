<template lang="pug">
.customers-module
  .page-header
    h2.title Clientes
    el-button(type="primary" @click="$router.push('/customers/new')")
      el-icon.mr-1
        Plus
      | Nuevo Cliente

  DataTable(
    :data="customers"
    :loading="loading"
    :total-items="totalItems"
    search-placeholder="Buscar cliente..."
    @search="handleSearch"
    @update:page="handlePageChange"
  )
    el-table-column(prop="id" label="ID" width="80")
    el-table-column(prop="name" label="Razón Social / Nombre" sortable)
    el-table-column(prop="taxId" label="NIF/CIF" width="140")
    el-table-column(prop="email" label="Email")
    el-table-column(prop="phone" label="Teléfono" width="150")
    el-table-column(label="Acciones" width="160" align="right")
      template(#default="{ row }")
        el-button(size="small" @click="$router.push(`/customers/${row.id}/edit`)") Editar
        el-popconfirm(title="¿Eliminar este cliente?" @confirm="handleDelete(row.id)")
          template(#reference)
            el-button(size="small" type="danger") Eliminar
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { Plus } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';
import DataTable from '@/components/common/DataTable.vue';
import { apiClient } from '@/api/client';

const customers = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const currentPage = ref(1);
const search = ref('');

async function loadCustomers() {
  loading.value = true;
  try {
    let url = `/api/customers?page=${currentPage.value}`;
    if (search.value) {
      url += `&name=${encodeURIComponent(search.value)}`;
    }
    const data = await apiClient(url);
    customers.value = data['hydra:member'] || data['member'] || data;
    totalItems.value = data['hydra:totalItems'] || customers.value.length;
  } catch (err: any) {
    ElMessage.error(err.message || 'Error cargando clientes');
  } finally {
    loading.value = false;
  }
}

function handleSearch(q: string) {
  search.value = q;
  currentPage.value = 1;
  loadCustomers();
}

function handlePageChange(p: number) {
  currentPage.value = p;
  loadCustomers();
}

async function handleDelete(id: number) {
  try {
    await apiClient(`/api/customers/${id}`, { method: 'DELETE' });
    ElMessage.success('Cliente eliminado correctamente');
    loadCustomers();
  } catch (err: any) {
    ElMessage.error(err.message || 'Error eliminando cliente');
  }
}

onMounted(() => {
  loadCustomers();
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
  color: #0f172a;
}
.mr-1 {
  margin-right: 6px;
}
</style>
