<template lang="pug">
.products-module
  .page-header
    h2.title Catálogo de Productos y Servicios
    el-button(type="primary" @click="$router.push('/products/new')")
      el-icon.mr-1
        Plus
      | Nuevo Producto

  DataTable(
    :data="products"
    :loading="loading"
    :total-items="totalItems"
    search-placeholder="Buscar producto o SKU..."
    @search="handleSearch"
    @update:page="handlePageChange"
  )
    el-table-column(prop="id" label="ID" width="80")
    el-table-column(prop="sku" label="SKU / Código" width="140")
    el-table-column(prop="name" label="Nombre" sortable)
    el-table-column(prop="price" label="Precio Venta" width="130")
      template(#default="{ row }")
        span.font-semibold {{ Number(row.price || 0).toFixed(2) }} €
    el-table-column(prop="costPrice" label="Coste" width="110")
      template(#default="{ row }")
        span {{ Number(row.costPrice || 0).toFixed(2) }} €
    el-table-column(prop="taxRate" label="IVA (%)" width="100")
      template(#default="{ row }")
        el-tag(size="small") {{ row.taxRate }} %
    el-table-column(prop="stockQuantity" label="Stock" width="100")
      template(#default="{ row }")
        el-tag(:type="row.stockQuantity > 10 ? 'success' : (row.stockQuantity > 0 ? 'warning' : 'danger')" size="small")
          | {{ row.stockQuantity }}
    el-table-column(label="Acciones" width="160" align="right")
      template(#default="{ row }")
        el-button(size="small" @click="$router.push(`/products/${row.id}/edit`)") Editar
        el-popconfirm(title="¿Eliminar este producto?" @confirm="handleDelete(row.id)")
          template(#reference)
            el-button(size="small" type="danger") Eliminar
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { Plus } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';
import DataTable from '@/components/common/DataTable.vue';
import { apiClient } from '@/api/client';

const products = ref<any[]>([]);
const loading = ref(false);
const totalItems = ref(0);
const currentPage = ref(1);
const search = ref('');

async function loadProducts() {
  loading.value = true;
  try {
    let url = `/api/products?page=${currentPage.value}`;
    if (search.value) {
      url += `&name=${encodeURIComponent(search.value)}`;
    }
    const data = await apiClient(url);
    products.value = data['hydra:member'] || data['member'] || data;
    totalItems.value = data['hydra:totalItems'] || products.value.length;
  } catch (err: any) {
    ElMessage.error(err.message || 'Error cargando productos');
  } finally {
    loading.value = false;
  }
}

function handleSearch(q: string) {
  search.value = q;
  currentPage.value = 1;
  loadProducts();
}

function handlePageChange(p: number) {
  currentPage.value = p;
  loadProducts();
}

async function handleDelete(id: number) {
  try {
    await apiClient(`/api/products/${id}`, { method: 'DELETE' });
    ElMessage.success('Producto eliminado correctamente');
    loadProducts();
  } catch (err: any) {
    ElMessage.error(err.message || 'Error eliminando producto');
  }
}

onMounted(() => {
  loadProducts();
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
