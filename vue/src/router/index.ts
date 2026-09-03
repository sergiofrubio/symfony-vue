import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth/store/useAuthStore'
import Login from '@/modules/auth/views/LoginView.vue'
import DashboardLayout from '@/components/layout/DashboardLayout.vue'
import Home from '@/modules/home/views/HomeView.vue'
import Users from '@/modules/users/views/index.vue'
import UserForm from '@/modules/users/views/Form.vue'
import Profile from '@/modules/auth/views/ProfileView.vue'
import Invoices from '@/modules/invoices/views/index.vue'
import InvoiceForm from '@/modules/invoices/views/Form.vue'
import Customers from '@/modules/customers/views/index.vue'
import CustomerForm from '@/modules/customers/views/Form.vue'
import Suppliers from '@/modules/suppliers/views/index.vue'
import SupplierForm from '@/modules/suppliers/views/Form.vue'
import Products from '@/modules/products/views/index.vue'
import ProductForm from '@/modules/products/views/Form.vue'
import Purchases from '@/modules/purchases/views/index.vue'
import PurchaseForm from '@/modules/purchases/views/Form.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'Login', component: Login },
    {
      path: '/home',
      component: DashboardLayout,
      children: [{ path: '', name: 'Home', component: Home, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'House', title: 'Dashboard', show: true } }],
    },
    {
      path: '/customers',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Customers', component: Customers, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'Avatar', title: 'Clientes', show: true } },
        { path: 'new', name: 'CustomerNew', component: CustomerForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Avatar', title: 'Nuevo cliente', show: false } },
        { path: ':id/edit', name: 'CustomerEdit', component: CustomerForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Avatar', title: 'Editar cliente', show: false } },
      ],
    },
    {
      path: '/suppliers',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Suppliers', component: Suppliers, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'Van', title: 'Proveedores', show: true } },
        { path: 'new', name: 'SupplierNew', component: SupplierForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Van', title: 'Nuevo proveedor', show: false } },
        { path: ':id/edit', name: 'SupplierEdit', component: SupplierForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Van', title: 'Editar proveedor', show: false } },
      ],
    },
    {
      path: '/products',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Products', component: Products, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'Goods', title: 'Productos y Servicios', show: true } },
        { path: 'new', name: 'ProductNew', component: ProductForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Goods', title: 'Nuevo producto', show: false } },
        { path: ':id/edit', name: 'ProductEdit', component: ProductForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Goods', title: 'Editar producto', show: false } },
      ],
    },
    {
      path: '/invoices',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Invoices', component: Invoices, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'Document', title: 'Facturas de Venta', show: true } },
        { path: 'new', name: 'InvoiceNew', component: InvoiceForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: '', title: 'Nueva factura', show: false } },
        { path: ':id/edit', name: 'InvoiceEdit', component: InvoiceForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: '', title: 'Editar factura', show: false } },
      ],
    },
    {
      path: '/purchases',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Purchases', component: Purchases, meta: { requiresAuth: true, roles: ['admin','user'], icon: 'ShoppingCart', title: 'Pedidos de Compra', show: true } },
        { path: 'new', name: 'PurchaseNew', component: PurchaseForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: '', title: 'Nuevo pedido de compra', show: false } },
        { path: ':id/edit', name: 'PurchaseEdit', component: PurchaseForm, meta: { requiresAuth: true, roles: ['admin','user'], icon: '', title: 'Editar pedido de compra', show: false } },
      ],
    },
    {
      path: '/users',
      component: DashboardLayout,
      children: [
        { path: '', name: 'Users', component: Users, meta: { requiresAuth: true, roles: ['admin'], icon: 'User', title: 'Usuarios', show: true } },
        { path: 'new', name: 'UserNew', component: UserForm, meta: { requiresAuth: true, roles: ['admin'], icon: 'User', title: 'Nuevo usuario', show: false } },
        { path: ':id/edit', name: 'UserEdit', component: UserForm, meta: { requiresAuth: true, roles: ['admin'], icon: 'User', title: 'Editar usuario', show: false } },
      ],
    },
    {
      path: '/profile',
      component: DashboardLayout,
      children: [{ path: '', name: 'Profile', component: Profile, meta: { requiresAuth: true, roles: ['admin','user','manager'], icon: 'User', title: 'Perfil', show: false } }],
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.isAuthenticated && !auth.user) {
    try {
      await auth.fetchMe()
    } catch (e) {
      // ignore
    }
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  const routeRoles = (to.meta && (to.meta as any).roles) as string[] | undefined
  if (routeRoles && auth.user) {
    const userRoles = (auth.user.roles || []).map((r: string) => r.toLowerCase().replace(/^role_/, ''))
    const allowed = routeRoles.map(r => r.toLowerCase())
    const isAdmin = userRoles.includes('admin') || userRoles.includes('role_admin')
    const hasRole = isAdmin || userRoles.some((r: string) => allowed.includes(r))
    if (!hasRole) {
      return { path: '/home' }
    }
  }

  return true
})

export default router
