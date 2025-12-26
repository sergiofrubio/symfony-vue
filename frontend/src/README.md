frontend/src/
├── api/                # Configuración base de Axios o Fetch (interceptores, JWT)
├── assets/             # Estilos globales, imágenes, logos
├── components/         # Componentes "tontos" REUTILIZABLES en toda la app
│   ├── ui/             # Botones, Inputs personalizados, Cards (wrappers de Element Plus)
│   └── common/         # Modales genéricos, Loaders
├── composables/        # Lógica compartida (Hooks de Vue)
│   ├── useAuth.ts      # Lógica de login/logout
│   └── useApi.ts       # Wrapper para peticiones
├── layouts/            # Estructuras de página
│   ├── DashboardLayout.vue
│   └── AuthLayout.vue
├── modules/            # <--- EL CORAZÓN DE TU ERP
│   ├── auth/           # Módulo de Autenticación
│   │   ├── components/ # Componentes exclusivos de Auth (LoginForm.vue)
│   │   ├── views/      # Vistas (LoginView.vue, ForgotPassword.vue)
│   │   ├── store/      # Store de Pinia para Auth
│   │   └── services/   # Llamadas a la API de Auth
│   ├── users/          # Módulo de Usuarios
│   │   ├── components/ # UserListTable.vue, UserForm.vue
│   │   ├── views/      # UserIndex.vue, UserDetail.vue
│   │   ├── types/      # Interfaces TypeScript (User.ts)
│   │   └── services/   # user.service.ts (CRUD específico)
│   └── invoices/       # Módulo de Facturación...
├── router/             # Configuración de rutas (importando rutas de los módulos)
├── stores/             # Stores GLOBALES (ej. Configuración de UI, Notificaciones)
├── types/              # Tipos globales de TypeScript (Respuesta API, Paginación)
├── utils/              # Funciones puras (formateo de fechas, moneda)
├── App.vue
└── main.ts