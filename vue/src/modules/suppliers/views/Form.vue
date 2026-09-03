<template lang="pug">
.supplier-form-module
  .page-header
    h2.title {{ isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
    el-button(@click="$router.push('/suppliers')") Volver

  el-card(shadow="never")
    el-form(
      ref="formRef"
      :model="form"
      :rules="rules"
      label-position="top"
      style="max-width: 650px"
    )
      el-form-item(label="Nombre / Razón Social" prop="name")
        el-input(v-model="form.name" placeholder="Ej: Servicios Cloud Iberia")

      el-row(:gutter="16")
        el-col(:span="12")
          el-form-item(label="NIF / CIF" prop="taxId")
            el-input(v-model="form.taxId" placeholder="B12345678")
        el-col(:span="12")
          el-form-item(label="Teléfono" prop="phone")
            el-input(v-model="form.phone" placeholder="+34 900 100 200")

      el-form-item(label="Correo Electrónico" prop="email")
        el-input(v-model="form.email" type="email" placeholder="facturacion@proveedor.com")

      el-form-item(label="Dirección" prop="address")
        el-input(v-model="form.address" type="textarea" :rows="2" placeholder="Dirección del proveedor")

      el-form-item
        el-button(type="primary" :loading="saving" @click="save") Guardar
        el-button(@click="$router.push('/suppliers')") Cancelar
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
  taxId: '',
  email: '',
  phone: '',
  address: '',
});

const rules = {
  name: [{ required: true, message: 'El nombre es obligatorio', trigger: 'blur' }],
};

async function loadSupplier() {
  if (!isEdit.value) return;
  try {
    const data = await apiClient(`/api/suppliers/${route.params.id}`);
    form.value = {
      name: data.name,
      taxId: data.taxId || '',
      email: data.email || '',
      phone: data.phone || '',
      address: data.address || '',
    };
  } catch (err: any) {
    ElMessage.error(err.message || 'Error cargando datos del proveedor');
  }
}

async function save() {
  await formRef.value.validate(async (valid: boolean) => {
    if (!valid) return;
    saving.value = true;
    try {
      if (isEdit.value) {
        await apiClient(`/api/suppliers/${route.params.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/merge-patch+json' },
          body: JSON.stringify(form.value),
        });
        ElMessage.success('Proveedor actualizado');
      } else {
        await apiClient('/api/suppliers', {
          method: 'POST',
          body: JSON.stringify(form.value),
        });
        ElMessage.success('Proveedor creado');
      }
      router.push('/suppliers');
    } catch (err: any) {
      ElMessage.error(err.message || 'Error guardando proveedor');
    } finally {
      saving.value = false;
    }
  });
}

onMounted(() => {
  loadSupplier();
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
