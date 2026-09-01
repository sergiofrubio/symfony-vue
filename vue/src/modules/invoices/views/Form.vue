<template lang="pug">
.invoice-form-view
  .header
    h2 {{ isEdit ? 'Editar factura' : 'Nueva factura' }}
    p.small Gestione líneas, cliente y estado.

  el-card.invoices-card(shadow="never")
    el-form(:model="form" ref="formRef" label-width="120px")
      el-form-item(label="Número" prop="number")
        el-input(v-model="form.number" placeholder="FACT-0001")

      el-form-item(label="Cliente" prop="customer_id")
        el-select(v-model="form.customer_id" placeholder="Selecciona cliente")
          el-option(v-for="c in customers" :key="c.id" :label="c.name" :value="c.id")

      el-form-item(label="Fecha" prop="date")
        el-input(v-model="form.date" placeholder="yyyy-mm-dd")

      el-form-item(label="Estado" prop="status")
        el-select(v-model="form.status")
          el-option(label="Borrador" :value="'draft'")
          el-option(label="Enviado" :value="'sent'")
          el-option(label="Pagado" :value="'paid'")
          el-option(label="Cancelado" :value="'cancelled'")

      el-form-item(label="Líneas" prop="lines")
        .lines
          div(v-for="(l, idx) in form.lines" :key="idx" class="line-row")
            el-input(v-model="l.description" placeholder="Descripción" style="width:40%")
            el-input(v-model="l.quantity" placeholder="Cantidad" style="width:12%" )
            el-input(v-model="l.unitPrice" placeholder="Precio" style="width:16%" )
            el-input(v-model="l.subtotal" placeholder="Subtotal" style="width:16%" )
            el-button(type="text" @click.prevent="removeLine(idx)") Quitar
          el-button(type="primary" @click.prevent="addLine") Añadir línea

      .form-actions
        el-button(type="primary" :loading="loading" @click="onSubmit") Guardar
        el-button(@click="onCancel") Cancelar
        el-button(type="danger" v-if="isEdit" @click="onDelete" :loading="loading") Eliminar

</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getAuthHeaders } from '@/api/auth'

const router = useRouter()
const route = useRoute()
const id = route.params.id ? String(route.params.id) : null
const isEdit = computed(() => !!id)

const formRef = ref<any>(null)
const loading = ref(false)

const customers = ref<{id:number;name:string;email?:string}[]>([])

const form = reactive({ number: '', customer_id: null as number | null, date: '', status: 'draft', lines: [] as any[] })

function addLine() { form.lines.push({ description: '', quantity: '1', unitPrice: '0.00', subtotal: '0.00' }) }
function removeLine(i:number) { form.lines.splice(i,1) }

async function fetchCustomers() {
  try {
    const res = await fetch('/api/customers', { headers: getAuthHeaders() })
    if (!res.ok) return
    customers.value = await res.json()
  } catch {}
}

async function fetchInvoice() {
  if (!id) return
  loading.value = true
  try {
    const res = await fetch(`/api/invoices/${id}`, { headers: getAuthHeaders() })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('Error fetching invoice')
    const data = await res.json()
    form.number = data.number || ''
    form.customer_id = data.customer?.id ?? null
    form.date = data.date ?? ''
    form.status = data.status ?? 'draft'
    form.lines = Array.isArray(data.lines) ? data.lines.map((l:any) => ({ description: l.product?.name || '', quantity: l.quantity, unitPrice: l.unitPrice, subtotal: l.subtotal })) : []
  } catch { alert('No se pudo cargar la factura') } finally { loading.value = false }
}

async function onSubmit() {
  loading.value = true
  try {
    const payload:any = { number: form.number, customer_id: form.customer_id, date: form.date, status: form.status, lines: form.lines }
    const method = isEdit.value ? 'PUT' : 'POST'
    const url = isEdit.value ? `/api/invoices/${id}` : '/api/invoices'
    const res = await fetch(url, { method, headers: { ...getAuthHeaders(), 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) { const err = await res.json().catch(()=>null); throw new Error(err?.message || 'Error al guardar') }
    router.push('/invoices')
  } catch (e:any) { alert(e.message || 'Error al guardar') } finally { loading.value = false }
}

function onCancel() { router.push('/invoices') }

async function onDelete() {
  if (!id) return
  if (!confirm('Eliminar factura?')) return
  loading.value = true
  try {
    const res = await fetch(`/api/invoices/${id}`, { method: 'DELETE', headers: getAuthHeaders() })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('No se pudo eliminar')
    router.push('/invoices')
  } catch { alert('Error al eliminar factura') } finally { loading.value = false }
}

onMounted(() => { fetchCustomers(); if (isEdit.value) fetchInvoice() })
</script>

<style scoped>
 .invoice-form-view {
    max-width: 840px;
    margin: 0 auto;
    padding: 12px
  }

  .header {
    display: block;
    /* justify-content: space-between;
  align-items: flex-start; */
    margin-bottom: 14px;
    gap: 12px;
  }

  .header h2 {
    margin: 0 0 6px;
  }

  .header p {
    margin: 4px 0 0;
    color: #6b7280;
    font-size: 13px;
  }

  .invoices-card {
    border-radius: 12px;
    border: 1px solid rgba(15, 23, 42, 0.06);
    padding: 16px
  }

  .form-actions {
    display: flex;
    gap: 8px;
    margin-top: 18px
  }

  .lines {
    display: flex;
    flex-direction: column;
    gap: 8px
  }

  .line-row {
    display: flex;
    gap: 8px;
    align-items: center
  }
</style>
