<template lang="pug">
.purchase-form-module
  .page-header
    h2.title {{ isEdit ? 'Editar Pedido de Compra' : 'Nuevo Pedido de Compra' }}
    el-button(@click="$router.push('/purchases')") Volver

  el-card(shadow="never")
    el-form(
      ref="formRef"
      :model="form"
      :rules="rules"
      label-position="top"
    )
      el-row(:gutter="16")
        el-col(:span="12")
          el-form-item(label="Proveedor" prop="supplier")
            el-select(
              v-model="form.supplier"
              placeholder="Seleccione proveedor"
              filterable
              style="width: 100%"
            )
              el-option(
                v-for="s in suppliers"
                :key="s['@id'] || s.id"
                :label="`${s.name} (${s.taxId || 'Sin NIF'})`"
                :value="s['@id'] || `/api/suppliers/${s.id}`"
              )
        el-col(:span="6")
          el-form-item(label="Fecha de Pedido" prop="date")
            el-date-picker(v-model="form.date" type="date" style="width: 100%")
        el-col(:span="6")
          el-form-item(label="Estado" prop="status")
            el-select(v-model="form.status" style="width: 100%")
              el-option(label="Borrador" value="draft")
              el-option(label="Enviado a proveedor" value="ordered")
              el-option(label="Mercancía Recibida" value="received")
              el-option(label="Cancelado" value="cancelled")

      el-divider(content-position="left") Líneas del Pedido

      el-table(:data="lines" stripe style="width: 100%; margin-bottom: 16px")
        el-table-column(label="Producto")
          template(#default="{ row, $index }")
            el-select(
              v-model="row.product"
              placeholder="Seleccionar producto"
              filterable
              @change="(val) => onProductSelect(val, $index)"
              style="width: 100%"
            )
              el-option(
                v-for="p in products"
                :key="p['@id'] || p.id"
                :label="`${p.name} (Coste: ${p.costPrice || p.price} €)`"
                :value="p['@id'] || `/api/products/${p.id}`"
              )
        el-table-column(label="Cantidad" width="140")
          template(#default="{ row }")
            el-input-number(v-model="row.quantity" :min="1" :step="1" @change="recalculate")
        el-table-column(label="Precio Unit. (€)" width="160")
          template(#default="{ row }")
            el-input-number(v-model="row.unitPrice" :precision="2" :step="1" :min="0" @change="recalculate")
        el-table-column(label="Subtotal (€)" width="140")
          template(#default="{ row }")
            span.font-semibold {{ (row.quantity * row.unitPrice).toFixed(2) }} €
        el-table-column(label="" width="80" align="center")
          template(#default="{ $index }")
            el-button(type="danger" size="small" circle @click="removeLine($index)")
              el-icon
                Delete

      el-button(type="dashed" @click="addLine" style="margin-bottom: 20px") + Añadir Línea

      .totals-summary
        el-descriptions(:column="1" border size="small" style="max-width: 320px; margin-left: auto")
          el-descriptions-item(label="Base Imponible") {{ totals.subtotal.toFixed(2) }} €
          el-descriptions-item(label="IVA Estimado (21%)") {{ totals.tax.toFixed(2) }} €
          el-descriptions-item(label="Total Pedido")
            span.font-bold.text-lg {{ totals.total.toFixed(2) }} €

      el-form-item(style="margin-top: 24px")
        el-button(type="primary" :loading="saving" @click="save") Guardar Pedido
        el-button(@click="$router.push('/purchases')") Cancelar
</template>

<script lang="ts" setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Delete } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';
import { apiClient } from '@/api/client';

const route = useRoute();
const router = useRouter();
const formRef = ref<any>(null);
const isEdit = computed(() => !!route.params.id);
const saving = ref(false);

const suppliers = ref<any[]>([]);
const products = ref<any[]>([]);

const form = ref({
  supplier: '',
  date: new Date(),
  status: 'draft',
  notes: '',
});

const lines = ref<any[]>([
  { product: null, quantity: 1, unitPrice: 0, taxRate: '21.00' },
]);

const totals = reactive({
  subtotal: 0,
  tax: 0,
  total: 0,
});

const rules = {
  supplier: [{ required: true, message: 'Debe seleccionar un proveedor', trigger: 'change' }],
};

function addLine() {
  lines.value.push({ product: null, quantity: 1, unitPrice: 0, taxRate: '21.00' });
}

function removeLine(index: number) {
  lines.value.splice(index, 1);
  recalculate();
}

function onProductSelect(productIdUri: string, index: number) {
  const prod = products.value.find((p) => (p['@id'] || `/api/products/${p.id}`) === productIdUri);
  if (prod) {
    lines.value[index].unitPrice = Number(prod.costPrice || prod.price || 0);
    lines.value[index].taxRate = prod.taxRate || '21.00';
    recalculate();
  }
}

function recalculate() {
  let sub = 0;
  let tax = 0;
  for (const line of lines.value) {
    const lineSub = Number(line.quantity || 0) * Number(line.unitPrice || 0);
    const lineTax = lineSub * (Number(line.taxRate || 21) / 100);
    sub += lineSub;
    tax += lineTax;
  }
  totals.subtotal = sub;
  totals.tax = tax;
  totals.total = sub + tax;
}

async function loadData() {
  try {
    const [suppData, prodData] = await Promise.all([
      apiClient('/api/suppliers?pagination=false'),
      apiClient('/api/products?pagination=false'),
    ]);
    suppliers.value = suppData['hydra:member'] || suppData['member'] || suppData;
    products.value = prodData['hydra:member'] || prodData['member'] || prodData;
  } catch (err) {
    console.error(err);
  }
}

async function save() {
  await formRef.value.validate(async (valid: boolean) => {
    if (!valid) return;
    if (lines.value.length === 0) {
      ElMessage.warning('Debe añadir al menos una línea de pedido');
      return;
    }

    saving.value = true;
    try {
      const payload = {
        ...form.value,
        totalAmount: String(totals.total.toFixed(2)),
        subtotal: String(totals.subtotal.toFixed(2)),
        taxAmount: String(totals.tax.toFixed(2)),
        purchaseOrderLines: lines.value.map((l) => ({
          product: l.product,
          quantity: String(l.quantity),
          unitPrice: String(l.unitPrice),
          subtotal: String((l.quantity * l.unitPrice).toFixed(2)),
          taxRate: String(l.taxRate || '21.00'),
        })),
      };

      if (isEdit.value) {
        await apiClient(`/api/purchase_orders/${route.params.id}`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/merge-patch+json' },
          body: JSON.stringify(payload),
        });
        ElMessage.success('Pedido actualizado');
      } else {
        await apiClient('/api/purchase_orders', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
        ElMessage.success('Pedido registrado con éxito');
      }
      router.push('/purchases');
    } catch (err: any) {
      ElMessage.error(err.message || 'Error guardando pedido');
    } finally {
      saving.value = false;
    }
  });
}

onMounted(async () => {
  await loadData();
  recalculate();
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
.font-bold {
  font-weight: 700;
}
.text-lg {
  font-size: 18px;
}
</style>
