<template lang="pug">
.product-form-module
  .page-header
    h2.title {{ isEdit ? 'Editar Producto / Servicio' : 'Nuevo Producto / Servicio' }}
    el-button(@click="$router.push('/products')") Volver

  el-card(shadow="never")
    el-form(
      ref="formRef"
      :model="form"
      :rules="rules"
      label-position="top"
      style="max-width: 650px"
    )
      el-row(:gutter="16")
        el-col(:span="16")
          el-form-item(label="Nombre del Producto o Servicio" prop="name")
            el-input(v-model="form.name" placeholder="Ej: Consultoría Tecnológica")
        el-col(:span="8")
          el-form-item(label="SKU / Referencia" prop="sku")
            el-input(v-model="form.sku" placeholder="CONS-01")

      el-form-item(label="Descripción" prop="description")
        el-input(v-model="form.description" type="textarea" :rows="2" placeholder="Detalles o especificaciones")

      el-row(:gutter="16")
        el-col(:span="8")
          el-form-item(label="Precio Venta (€)" prop="price")
            el-input-number(v-model="form.price" :precision="2" :step="1" :min="0" style="width: 100%")
        el-col(:span="8")
          el-form-item(label="Precio Coste (€)" prop="costPrice")
            el-input-number(v-model="form.costPrice" :precision="2" :step="1" :min="0" style="width: 100%")
        el-col(:span="8")
          el-form-item(label="IVA (%)" prop="taxRate")
            el-select(v-model="form.taxRate" style="width: 100%")
              el-option(label="21% General" value="21.00")
              el-option(label="10% Reducido" value="10.00")
              el-option(label="4% Superreducido" value="4.00")
              el-option(label="0% Exento" value="0.00")

      el-form-item(label="Stock Actual" prop="stockQuantity")
        el-input-number(v-model="form.stockQuantity" :min="0" :step="1")

      el-form-item
        el-button(type="primary" :loading="saving" @click="save") Guardar
        el-button(@click="$router.push('/products')") Cancelar
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { apiClient } from '@/api/client';

const route = useRoute();
const router = useRouter();
const formRef = ref<any>(null);
const isEdit = computed(() => !!route.params.id);
const saving = ref(false);

const form = ref({
  name: '',
  sku: '',
  description: '',
  price: 0,
  costPrice: 0,
  taxRate: '21.00',
  stockQuantity: 0,
});

const rules = {
  name: [{ required: true, message: 'El nombre es obligatorio', trigger: 'blur' }],
  sku: [{ required: true, message: 'El SKU es obligatorio', trigger: 'blur' }],
};

async function loadProduct() {
  if (!isEdit.value) return;
  try {
    const data = await apiClient(`/api/products/${route.params.id}`);
    form.value = {
      name: data.name,
      sku: data.sku,
      description: data.description || '',
      price: Number(data.price || 0),
      costPrice: Number(data.costPrice || 0),
      taxRate: data.taxRate || '21.00',
      stockQuantity: Number(data.stockQuantity || 0),
    };
  } catch (err: any) {
    ElMessage.error(err.message || 'Error cargando datos del producto');
  }
}

async function save() {
  await formRef.value.validate(async (valid: boolean) => {
    if (!valid) return;
    saving.value = true;
    try {
      const payload = {
        ...form.value,
        price: String(form.value.price),
        costPrice: String(form.value.costPrice),
      };

      if (isEdit.value) {
        await apiClient(`/api/products/${route.params.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/merge-patch+json' },
          body: JSON.stringify(payload),
        });
        ElMessage.success('Producto actualizado');
      } else {
        await apiClient('/api/products', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
        ElMessage.success('Producto creado');
      }
      router.push('/products');
    } catch (err: any) {
      ElMessage.error(err.message || 'Error guardando producto');
    } finally {
      saving.value = false;
    }
  });
}

onMounted(() => {
  loadProduct();
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
</style>
