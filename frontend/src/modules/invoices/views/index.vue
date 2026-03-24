<template lang="pug">
.invoice-view
  .header
    .header-copy
      h2 Gestión de facturas
      p Administra facturas, estados y exportación a PDF.
    .actions
      el-button(type="primary" @click="onAdd") Nueva factura
      el-button(type="primary" @click="fetchInvoices" :loading="loading") Actualizar

  el-card.invoices-card(shadow="never")
    .toolbar
      el-input.toolbar-input(v-model="search" placeholder="Buscar por número o cliente..." clearable @clear="onClear" @input="onSearchInput")
      el-select(v-model="selectedStatus" placeholder="Estado" clearable)
        el-option(key="all" label="Todos" :value="''")
        el-option(label="Borrador" :value="'draft'")
        el-option(label="Enviado" :value="'sent'")
        el-option(label="Pagado" :value="'paid'")
        el-option(label="Cancelado" :value="'cancelled'")

    el-table(:data="invoices" v-loading="loading" style="width:100%")
      el-table-column(prop="id" label="ID" width="80")
      el-table-column(prop="number" label="Número" min-width="160")
      el-table-column(prop="date" label="Fecha" min-width="160")
        template(#default="{ row }")
          span {{ formatDate(row.date) }}
      el-table-column(prop="customer" label="Cliente" min-width="220")
        template(#default="{ row }")
          span {{ row.customer ? row.customer.name : '-' }}
      el-table-column(prop="totalAmount" label="Importe" width="140")
      el-table-column(prop="status" label="Estado" width="120")
      el-table-column(label="Acciones" width="220" fixed="right")
        template(#default="{ row }")
          .actions
            el-button(type="primary" link @click.prevent="onEdit(row)") Editar
            el-button(type="primary" link @click.prevent="onPdf(row)") PDF
            el-button(type="danger" link @click.prevent="onDelete(row)") Eliminar

    .footer
      span.results Mostrando {{ meta.from }} - {{ meta.to }} de {{ meta.total }} facturas
      el-pagination(background :page-size="pageSize" :current-page="page" :total="meta.total" @current-change="onPageChange" @size-change="onPageSizeChange" layout="sizes, prev, pager, next" :page-sizes="[10,20,50]")
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getAuthHeaders } from '@/api/auth'

type Invoice = {
  id: number
  number: string
  date: string | null
  totalAmount: string
  status: string
  customer: { id: number; name: string; email?: string } | null
}

const router = useRouter()
const invoices = ref<Invoice[]>([])
const loading = ref(false)
const search = ref('')
const selectedStatus = ref<string | ''>('')
const page = ref(1)
const pageSize = ref(10)
const meta = reactive({ total: 0, from: 0, to: 0 })

let searchTimer: number | undefined

function buildQueryParams() {
  const params: Record<string, string> = {}
  if (search.value) params.search = String(search.value)
  if (selectedStatus.value !== '') params.status = String(selectedStatus.value)
  params.page = String(page.value)
  params.limit = String(pageSize.value)
  return new URLSearchParams(params).toString()
}

async function fetchInvoices() {
  loading.value = true
  try {
    const qs = buildQueryParams()
    const res = await fetch(`/api/invoices?${qs}`, { headers: getAuthHeaders() })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('Error fetching invoices')
    const data = await res.json()
    invoices.value = Array.isArray(data.items) ? data.items : []
    meta.total = data.total ?? invoices.value.length
    meta.from = invoices.value.length ? (page.value - 1) * pageSize.value + 1 : 0
    meta.to = invoices.value.length ? meta.from + invoices.value.length - 1 : 0
  } catch {
    invoices.value = []
    meta.total = 0
    meta.from = 0
    meta.to = 0
  } finally { loading.value = false }
}

function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = window.setTimeout(() => { page.value = 1; fetchInvoices() }, 350)
}

function onClear() { page.value = 1; fetchInvoices() }

function onPageChange(p: number) { page.value = p; fetchInvoices() }
function onPageSizeChange(size: number) { pageSize.value = size; page.value = 1; fetchInvoices() }

function onAdd() { router.push('/invoices/new') }
function onEdit(row: Invoice) { router.push({ name: 'InvoiceEdit', params: { id: String(row.id) } }) }

async function onDelete(row: Invoice) {
  if (!confirm(`Eliminar factura ${row.number}?`)) return
  loading.value = true
  try {
    const res = await fetch(`/api/invoices/${row.id}`, { method: 'DELETE', headers: getAuthHeaders() })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('No se pudo eliminar')
    fetchInvoices()
  } catch { alert('Error al eliminar factura') } finally { loading.value = false }
}

function formatDate(v: string | null) { if (!v) return '-'; try { return new Date(v).toLocaleString() } catch { return v } }

async function onPdf(row: Invoice) {
  loading.value = true
  try {
    const res = await fetch(`/api/invoices/${row.id}/pdf`, { headers: getAuthHeaders() })
    if (res.status === 401) { router.push('/login'); return }
    if (!res.ok) throw new Error('Error obteniendo PDF')

    const contentType = res.headers.get('Content-Type') || ''
    const buffer = await res.arrayBuffer()
    const blob = new Blob([buffer], { type: contentType || 'application/pdf' })
    const url = URL.createObjectURL(blob)
    window.open(url, '_blank')
    // revoke after a while
    setTimeout(() => URL.revokeObjectURL(url), 60_000)
  } catch (e) {
    alert('No se pudo abrir el PDF')
  } finally {
    loading.value = false
  }
}

onMounted(() => { fetchInvoices() })
</script>

<style scoped>
.invoice-view { max-width: 1200px; margin: 0 auto; padding: 8px 12px }
.header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; gap: 12px }
.header-copy h2 { margin:0; font-size:22px; color:#0f172a }
.header-copy p { margin:4px 0 0; color:#6b7280; font-size: 13px;}
.invoices-card { border:1px solid rgba(15,23,42,0.08); border-radius:12px }
.toolbar { display:grid; grid-template-columns: minmax(250px,1.4fr) 1fr; gap:12px; margin-bottom:14px }
.footer { display:flex; justify-content:space-between; align-items:center; margin-top:14px }
.results { color:#6b7280; font-size:13px }
.actions { display:flex; gap:8px }
</style>
