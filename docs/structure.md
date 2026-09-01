src/
├── Entity/                 # Tus tablas de base de datos
│   ├── Auth/               # (User, Group, Permission)
│   ├── Sales/              # (Order, Invoice, Customer)
│   └── Inventory/          # (Product, Stock, Warehouse)
│
├── Repository/             # Consultas SQL complejas (Espejo de Entity)
│   ├── Auth/
│   ├── Sales/
│   └── ...
│
├── Dto/                    # DATA TRANSFER OBJECTS (¡Vital para un ERP!)
│   ├── Sales/              # InvoiceCreateDto, InvoiceOutputDto
│   └── ...
│   # Nota: En un ERP, NUNCA expongas tus Entidades directamente a la API
│   # para escrituras complejas. Usa DTOs.
│
├── State/                  # Lógica de API Platform (Reemplaza a los controladores)
│   ├── Sales/
│   │   ├── InvoiceProcessor.php  # Lógica al guardar una factura (ej: restar stock)
│   │   └── UserHashPasswordProcessor.php
│
├── Service/                # Lógica de Negocio Pura (El cerebro del ERP)
│   ├── Billing/
│   │   ├── TaxCalculator.php
│   │   └── InvoicePdfGenerator.php
│   └── Inventory/
│       └── StockManager.php
│
├── Controller/             # Solo para cosas "raras" que API Platform no cubra
│   └── Actions/            # Ej: Descargar un PDF, Exportar Excel
│
├── Security/               # Permisos complejos
│   └── Voter/              # InvoiceVoter.php (¿Quién puede ver esta factura?)
│
└── EventSubscriber/        # Reaccionar a eventos (ej: Enviar email tras registro)
