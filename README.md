
# Plantilla ERP — Symfony + Vue

**Índice**
- [Introducción](#introducción)
- [Requisitos](#requisitos)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Instalación — Backend](#instalación---backend)
- [Instalación — Frontend](#instalación---frontend)
- [Ejecución con Docker (opcional)](#ejecución-con-docker-opcional)
- [Autenticación](#autenticación)
- [Pruebas y linting](#pruebas-y-linting)
- [Contribución](#contribución)

**Introducción**
- **Propósito**: Plantilla para construir ERPs personalizados usando Vue en el frontend y Symfony en el backend. Proporciona una base modular con autenticación JWT, estructura para entidades, rutas API y un frontend SPA con Vite.

**Requisitos**
- PHP 8.1+; Composer; Node.js 18+; npm o yarn; Docker (opcional); una base de datos compatible (MySQL/PostgreSQL).

**Estructura del proyecto**
- `backend/`: aplicación Symfony (API, entidades, migraciones, configuración).
- `frontend/`: aplicación Vue + Vite (SPA, módulos, rutas).
- `docker/`, `compose.yaml`: contenedores y orquestación (opcional).

**Instalación — Backend**
- Clona el repositorio y entra en la carpeta `backend`.
- Instala dependencias:

```bash
cd backend
composer install
```

- Configura variables de entorno copiando `.env` a `.env.local` y ajustando `DATABASE_URL`, JWT y otros valores.
- Crea la base de datos y ejecuta migraciones:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

- (Opcional) Carga datos de ejemplo:

```bash
php bin/console doctrine:fixtures:load
```

**Instalación — Frontend**
- Entra en `frontend` e instala dependencias:

```bash
cd frontend
npm install
# o: yarn
```

<!-- - Variables de entorno: configura `VITE_API_BASE_URL` u otras variables usadas por Vite en `.env` o el sistema. -->
- Desarrollo:

```bash
npm run dev
```

- Build producción:

```bash
npm run build
```

**Ejecución con Docker (opcional)**
- Revisa `docker/`, `compose.yaml` y `compose.override.yaml` para levantar servicios (PHP-FPM, base de datos, Nginx, y opciones para construir el frontend si están configuradas). Usa `docker-compose up --build` o los comandos del `Makefile` según convenga.

**Autenticación**
- El backend incluye soporte para JWT mediante LexikJWTAuthenticationBundle. Para generar las claves (privada y pública) puedes usar la utilidad del bundle:

```bash
cd backend
php bin/console lexik:jwt:generate-keypair
```

<!-- - Alternativa (manual) con OpenSSL si prefieres control explícito:

```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -algorithm RSA -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
chmod 600 config/jwt/private.pem
``` -->

- Si usas passphrase para la clave privada, añade `JWT_PASSPHRASE` en `.env` y ajusta `config/packages/lexik_jwt_authentication.yaml` según la documentación del bundle.

**Pruebas y linting**
- Backend (si aplica):

```bash
cd backend
php bin/phpunit
```

- Frontend (según configuración):

```bash
cd frontend
npm run test
npm run lint
```

**Contribución**
- Si quieres contribuir, abre un issue o un merge request y sigue las guías de codificación del proyecto.


