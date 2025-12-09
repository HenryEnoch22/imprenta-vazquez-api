# Imprenta Vázquez - API Backend

API RESTful desarrollada con Laravel para el sistema de gestión de solicitudes de impresión. Backend del sistema integral para administración de clientes, trabajos de impresión y seguimiento de pedidos.

## Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Requisitos Previos](#requisitos-previos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Base de Datos](#base-de-datos)
- [Autenticación](#autenticación)
- [API Endpoints](#api-endpoints)
- [Modelos y Relaciones](#modelos-y-relaciones)
- [Validaciones](#validaciones)
- [Desarrollo](#desarrollo)
- [Testing](#testing)
- [Despliegue](#despliegue)
- [Convenciones de Código](#convenciones-de-código)

## Características

### Sistema de Autenticación
- Autenticación basada en tokens con **Laravel Sanctum**
- Login/Logout con tokens de API
- Tokens con expiración configurable (14 días por defecto)
- Gestión de roles (Admin/Cliente)
- Protección de rutas mediante middleware

### Gestión de Clientes
- CRUD completo de clientes
- Información detallada: nombre comercial, representante, RFC, teléfono
- Sistema de direcciones completo con datos de México
- Validación de RFC mexicano
- Soft deletes para eliminación segura
- Relación uno a uno con usuarios

### Gestión de Direcciones
- Direcciones detalladas de clientes
- Soporte para código postal mexicano (5 dígitos)
- Campos: entidad federativa, municipio, localidad, colonia
- Números interior y exterior
- Referencias: entre calles

### Arquitectura de Solicitudes de Impresión
- Sistema modular de categorías y tipos de recibos
- Relación con clientes
- Preparado para expansión de funcionalidades
- *En desarrollo: campos específicos de solicitudes*

### Seguridad
- Validación de datos con Form Requests
- Hashing de contraseñas con Bcrypt
- Protección CSRF
- CORS configurado para frontend específico
- Soft deletes en registros sensibles

## Tecnologías

### Core
- **PHP 8.2+** - Lenguaje de programación
- **Laravel 12.x** - Framework PHP
- **MySQL** - Base de datos relacional

### Autenticación y Seguridad
- **Laravel Sanctum 4.x** - Autenticación API con tokens
- **Laravel Breeze 2.x** - Scaffolding de autenticación

### Herramientas de Desarrollo
- **Composer** - Gestor de dependencias PHP
- **Laravel Tinker 2.x** - REPL para Laravel
- **Laravel Pail 1.x** - Visualización de logs en tiempo real
- **Laravel Sail 1.x** - Entorno Docker para desarrollo

### Testing y Calidad
- **PHPUnit 11.x** - Framework de testing
- **Laravel Pint 1.x** - Linter de código PHP
- **Faker PHP 1.x** - Generación de datos de prueba
- **Mockery 1.x** - Mocking para tests

### Otras Herramientas
- **Guzzle HTTP** - Cliente HTTP para PHP
- **Collision 8.x** - Reportes de error mejorados

## Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL >= 5.7 o MariaDB >= 10.3
- Extensiones PHP requeridas:
    - BCMath
    - Ctype
    - JSON
    - Mbstring
    - OpenSSL
    - PDO
    - Tokenizer
    - XML

## Instalación

### Instalación Rápida

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/imprenta-vazquez-api.git
cd imprenta-vazquez-api
```

2. **Ejecutar script de instalación automática**
```bash
composer install && cp .env.example .env && php artisan key:generate && php artisan migrate && npm install && npm run build
```

Este comando ejecutará automáticamente:
- Instalación de dependencias
- Copia del archivo `.env`
- Generación de la clave de aplicación
- Ejecución de migraciones
- Instalación de dependencias NPM
- Build de assets

### Instalación Manual

1. **Instalar dependencias**
```bash
composer install
```

2. **Configurar variables de entorno**
```bash
cp .env.example .env
```

3. **Generar clave de aplicación**
```bash
php artisan key:generate
```

4. **Configurar base de datos** (editar `.env` con tus credenciales)

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Ejecutar seeders (opcional)**
```bash
php artisan db:seed
```

7. **Generar link simbólico para storage**
```bash
php artisan storage:link
```

## Configuración

### Variables de Entorno

Crea un archivo `.env` basado en este ejemplo `.env.example`:

```env
# Aplicación
APP_NAME="Imprenta Vázquez API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=imprenta_vazquez_api
DB_USERNAME=root
DB_PASSWORD=

# Frontend (para CORS)
FRONTEND_URL=http://localhost:3000

# Sesiones
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Cola de trabajos
QUEUE_CONNECTION=database

# Email
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@imprentavazquez.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuración de Sanctum

Los tokens de API están configurados en [config/sanctum.php](config/sanctum.php) para trabajar con SPA (Single Page Applications).

## Estructura del Proyecto

```
imprenta-vazquez-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                      # Controladores de autenticación
│   │   │   │   ├── AuthenticatedSessionController.php  # Login/Logout
│   │   │   │   ├── RegisteredUserController.php        # Registro
│   │   │   │   ├── PasswordResetLinkController.php     # Recuperación
│   │   │   │   └── ...
│   │   │   ├── CustomerController.php     # CRUD de clientes
│   │   │   └── Controller.php             # Controlador base
│   │   ├── Middleware/                    # Middlewares personalizados
│   │   └── Requests/                      # Form Requests
│   │       ├── Auth/
│   │       │   └── LoginRequest.php       # Validación de login
│   │       └── customers/
│   │           ├── StoreCustomerRequest.php    # Validación crear cliente
│   │           └── UpdateCustomerRequest.php   # Validación actualizar
│   │
│   ├── Models/                            # Modelos Eloquent
│   │   ├── User.php                       # Modelo de usuario
│   │   ├── Customer.php                   # Modelo de cliente
│   │   └── CustomerAddress.php            # Modelo de dirección
│   │
│   └── Providers/                         # Service Providers
│
├── config/                                # Archivos de configuración
│   ├── app.php                            # Configuración general
│   ├── auth.php                           # Configuración de autenticación
│   ├── cors.php                           # Configuración CORS
│   ├── database.php                       # Configuración de BD
│   └── sanctum.php                        # Configuración de tokens
│
├── database/
│   ├── factories/                         # Factories para testing
│   ├── migrations/                        # Migraciones de base de datos
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2025_11_09_001119_create_personal_access_tokens_table.php
│   │   ├── 2025_11_09_005807_create_customer_addresses_table.php
│   │   ├── 2025_11_09_005808_create_customers_table.php
│   │   ├── 2025_11_09_064146_create_receipt_categories_table.php
│   │   ├── 2025_11_09_064151_create_type_receipts_table.php
│   │   └── 2025_11_09_064436_create_print_job_requests_table.php
│   └── seeders/                           # Seeders de datos
│       └── DatabaseSeeder.php             # Seeder principal
│
├── routes/
│   ├── api.php                            # Rutas de API
│   ├── auth.php                           # Rutas de autenticación
│   ├── web.php                            # Rutas web
│   └── console.php                        # Comandos Artisan
│
├── tests/                                 # Tests automatizados
│   ├── Feature/                           # Tests de integración
│   └── Unit/                              # Tests unitarios
│
├── storage/                               # Archivos generados
│   ├── app/                               # Archivos de aplicación
│   ├── logs/                              # Logs
│   └── framework/                         # Cache, sesiones, vistas
│
├── public/                                # Punto de entrada web
│   └── index.php                          # Front controller
│
├── .env.example                           # Plantilla de variables de entorno
├── artisan                                # CLI de Laravel
├── composer.json                          # Dependencias PHP
├── phpunit.xml                            # Configuración de testing
└── README.md                              # Este archivo
```

## Autenticación

### Sistema de Tokens

El API utiliza **Laravel Sanctum** para autenticación basada en tokens.

#### Login

**Endpoint:** `POST /login`

**Request:**
```json
{
  "username": "henryyv",
  "password": "password"
}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "username": "henryyv",
    "email": "test@example.com"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz..."
}
```

**Response (401):**
```json
{
  "message": "Credenciales incorrectas"
}
```

**Características del token:**
- Expiración: 14 días
- Formato: `{id}|{token_hash}`
- Se eliminan tokens previos al generar uno nuevo

#### Logout

**Endpoint:** `POST /logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

#### Uso del Token

Todas las rutas protegidas requieren el header:
```
Authorization: Bearer {token}
```

## API Endpoints

### Autenticación

| Método | Ruta | Descripción | Middleware |
|--------|------|-------------|------------|
| POST | `/register` | Registrar nuevo usuario | guest |
| POST | `/login` | Iniciar sesión | guest |
| POST | `/logout` | Cerrar sesión | auth:sanctum |
| POST | `/forgot-password` | Solicitar reset de contraseña | guest |
| POST | `/reset-password` | Restablecer contraseña | guest |
| GET | `/verify-email/{id}/{hash}` | Verificar email | auth, signed |
| POST | `/email/verification-notification` | Reenviar verificación | auth |

### Usuario Autenticado

| Método | Ruta | Descripción | Middleware |
|--------|------|-------------|------------|
| GET | `/user` | Obtener usuario actual | auth:sanctum |

### Clientes

Todas las rutas de clientes requieren autenticación (`auth:sanctum`).

| Método | Ruta | Descripción | Controller |
|--------|------|-------------|------------|
| GET | `/customers` | Listar todos los clientes | index |
| POST | `/customers` | Crear nuevo cliente | store |
| GET | `/customers/{id}` | Obtener cliente específico | show |
| PUT/PATCH | `/customers/{id}` | Actualizar cliente | update |
| DELETE | `/customers/{id}` | Eliminar cliente (soft delete) | destroy |

## Validaciones

#### Validaciones Específicas

- **RFC:** Formato válido de RFC mexicano (3-4 letras + 6 dígitos + 3 caracteres)
- **Código Postal:** Exactamente 5 dígitos numéricos
- **Teléfono:** Números y símbolos válidos (+, -, espacios, paréntesis)
- **Password:** Mínimo 8 caracteres, con confirmación

## Desarrollo

### Scripts de Desarrollo

```bash
# Instalar y configurar todo
composer install

# Copiar archivo de entorno
Linux/Mac:
cp .env.example .env
Windows:
copy .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Generar link simbólico para storage
php artisan storage:link

# Rollback de migraciones
php artisan migrate:rollback

# Refrescar migraciones
php artisan migrate:fresh

# Ejecutar seeders
php artisan db:seed

# Servidor de desarrollo
php artisan serve

# Ejecutar migraciones
php artisan migrate

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generar nueva migración
php artisan make:migration create_nombre_tabla

# Generar nuevo modelo
php artisan make:model NombreModelo -m

# Generar nuevo controlador
php artisan make:controller NombreController

# Generar Form Request
php artisan make:request NombreRequest
```

## Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter NombreTest

# Con cobertura
php artisan test --coverage
```

## Despliegue

### Preparación para Producción

1. **Configurar variables de entorno**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.imprentavazquez.com

DB_CONNECTION=mysql
DB_HOST=tu_host_produccion
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña_segura

FRONTEND_URL=https://imprentavazquez.com
```
``

2. **Ejecutar migraciones**
```bash
php artisan migrate --force
```

3. **Configurar permisos**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```


## Seguridad

### Mejores Prácticas Implementadas

- Hashing de contraseñas con Bcrypt
- Validación de datos en Form Requests
- Protección CSRF en rutas web
- CORS configurado restrictivamente
- Soft deletes para datos sensibles
- Tokens con expiración
- Validación de RFC mexicano


## Roadmap

### En Desarrollo
- [ ] Completar campos de `print_job_requests`
- [ ] Implementar controlador de solicitudes de impresión
- [ ] Sistema de pagos
- [ ] Carga de archivos

## Contribución

1. Fork el proyecto
2. Crea tu rama de feature (`git checkout -b feature/nueva-caracteristica`)
3. Commit tus cambios (`git commit -m 'feat: agregar nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Abre un Pull Request


## Licencia

Este proyecto es privado y pertenece a **Imprenta Vázquez**.

## Contacto

Para preguntas o soporte, contacta al equipo de desarrollo.

---

**Desarrollado con Laravel para Imprenta Vázquez** 
