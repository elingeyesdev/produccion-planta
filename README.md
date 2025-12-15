# Sistema de Trazabilidad - Guía de Instalación

Este repositorio contiene un sistema completo de trazabilidad compuesto por:

## Estructura del Sistema

### 1. **Trazabilidad** (Backend Laravel)
   - **Aplicación Web**: Interfaz web completa con páginas Blade
     - Dashboard administrativo
     - Gestión de pedidos, lotes, máquinas, procesos
     - Gestión de operadores, proveedores, materia prima
     - Certificados y documentación
     - Rutas en tiempo real
   - **API REST**: Endpoints para la aplicación móvil
     - Autenticación JWT
     - Gestión de órdenes de clientes
     - Procesos de producción
     - Almacenamiento y trazabilidad
   - **Base de Datos**: **PostgreSQL**
     - **NOTA**: El archivo `planta.sql` es un dump de PostgreSQL

### 2. **Trazabilidad-mobile** (Aplicación Móvil)
   - Aplicación React Native con Expo
   - Consume la API REST del backend
   - Funcionalidades móviles para operadores en campo

---

## Clonar el Repositorio

Antes de comenzar con la instalación, necesitas clonar el repositorio en tu máquina local.

### Opción 1: Clonar con HTTPS

```bash
git clone https://github.com/elingeyesdev/produccion-planta 
cd produccion-planta
```

### Opción 2: Descargar como ZIP

Si no tienes Git instalado, puedes descargar el repositorio como archivo ZIP desde GitHub:
1. Ve a la página del repositorio en GitHub
2. Haz clic en el botón verde **"Code"**
3. Selecciona **"Download ZIP"**
4. Extrae el archivo ZIP en tu ubicación deseada
5. Abre una terminal en la carpeta extraída

### Verificar la estructura del proyecto

Después de clonar, verifica que la estructura del proyecto sea correcta. Deberías ver las siguientes carpetas:

```bash
# Desde la raíz del proyecto clonado
ls

# Deberías ver:
# - Trazabilidad/
# - Trazabilidad-mobile/
# - planta.sql
# - README.md
```

Si la estructura es correcta, puedes continuar con la instalación. Si falta alguna carpeta, verifica que el clonado se haya completado correctamente.

---

## Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

### Para el Backend (Laravel)
- **PHP 8.2** o superior
- **Composer** (gestor de dependencias de PHP)
- **Node.js 18+** y **npm** (para Vite y assets frontend)
- **PostgreSQL 12+** (recomendado) o **MySQL 8+** / **MariaDB 10+**
  - **Nota**: El proyecto está configurado principalmente para PostgreSQL. El archivo `planta.sql` es un dump de PostgreSQL.
- **Extensiones PHP requeridas**:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

### Para la Aplicación Móvil
- **Node.js 18+** y **npm**
- **Expo CLI** (se instalará globalmente)
- **Android Studio** (para desarrollo Android) o **Xcode** (para desarrollo iOS - solo macOS)

---

## Estructura del Proyecto

```
produccion-planta/
├── Trazabilidad/              # Backend Laravel (Web + API)
│   ├── app/                   # Lógica de la aplicación
│   ├── config/                # Configuraciones
│   ├── database/
│   │   ├── migrations/        # Migraciones de base de datos
│   │   └── seeders/           # Seeders (datos iniciales)
│   ├── routes/
│   │   ├── web.php            # Rutas de la aplicación web
│   │   ├── api.php            # Rutas de la API REST
│   │   └── resources/views/   # Vistas Blade (páginas web)
│   └── ...
├── Trazabilidad-mobile/       # Aplicación móvil React Native
│   ├── src/                   # Código fuente de la app móvil
│   ├── assets/                # Imágenes y recursos
│   └── ...
└── planta.sql                 # Dump de PostgreSQL (opcional, usar si prefieres importar en lugar de migraciones)
```

---

## Instalación Paso a Paso

**IMPORTANTE**: Asegúrate de haber clonado el repositorio y estar en la carpeta raíz del proyecto antes de continuar.

### 1. Instalación del Backend (Laravel)

#### Paso 1: Navegar al directorio del backend

Desde la raíz del proyecto clonado:

```bash
cd Trazabilidad
```

#### Paso 2: Instalar dependencias de PHP con Composer

```bash
composer install
```

Este comando instalará todas las dependencias definidas en `composer.json`, incluyendo:
- Laravel Framework 12
- JWT Auth (tymon/jwt-auth)
- Spatie Permissions
- AdminLTE
- Cloudinary
- Y otras dependencias necesarias

#### Paso 3: Instalar dependencias de Node.js

```bash
npm install
```

Esto instalará las dependencias para Vite, TailwindCSS y otros assets frontend.

#### Paso 4: Configurar el archivo de entorno

Crea un archivo `.env` basado en `.env.example`:

```bash
# Si existe .env.example
cp .env.example .env

# Si no existe, crea el archivo .env manualmente
```

#### Paso 5: Configurar variables de entorno en `.env`

Edita el archivo `.env` y configura las siguientes variables:

```env
# ============================================
# CONFIGURACIÓN BÁSICA DE LA APLICACIÓN
# ============================================
APP_NAME="Sistema de Trazabilidad"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=es

# ============================================
# CONFIGURACIÓN DE BASE DE DATOS
# ============================================
# IMPORTANTE: El proyecto está configurado principalmente para PostgreSQL
# El archivo planta.sql es un dump de PostgreSQL
# Para PostgreSQL (RECOMENDADO)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=trazabilidad_db
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña

# O para MySQL/MariaDB (si prefieres usar MySQL)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=planta
# DB_USERNAME=root
# DB_PASSWORD=tu_contraseña

# ============================================
# CONFIGURACIÓN JWT (Autenticación)
# ============================================
JWT_SECRET=
# Se generará automáticamente con: php artisan jwt:secret

# ============================================
# CONFIGURACIÓN CLOUDINARY (Almacenamiento de imágenes)
# ============================================
CLOUDINARY_CLOUD_NAME=tu_cloud_name
CLOUDINARY_API_KEY=tu_api_key
CLOUDINARY_API_SECRET=tu_api_secret
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name

# ============================================
# CONFIGURACIÓN DE INTEGRACIÓN PLANTACRUDS
# ============================================
PLANTACRUDS_API_URL=http://localhost:8001/api

# ============================================
# CONFIGURACIÓN DE PLANTA
# ============================================
PLANTA_NOMBRE=Planta Principal
PLANTA_DIRECCION=Av. Ejemplo 123, Santa Cruz de la Sierra, Bolivia
PLANTA_LATITUD=-17.8146
PLANTA_LONGITUD=-63.1561

# ============================================
# CONFIGURACIÓN DE CACHE Y SESIONES
# ============================================
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# ============================================
# CONFIGURACIÓN DE CORREO (Opcional)
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@trazabilidad.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**IMPORTANTE**: 
- Reemplaza `tu_contraseña`, `tu_cloud_name`, etc. con tus valores reales
- Si no tienes Cloudinary configurado, puedes dejar esas variables vacías (algunas funcionalidades de imágenes no estarán disponibles)

#### Paso 6: Generar la clave de aplicación

```bash
php artisan key:generate
```

Esto generará automáticamente `APP_KEY` en tu archivo `.env`.

#### Paso 7: Generar el secreto JWT

```bash
php artisan jwt:secret
```

Esto generará automáticamente `JWT_SECRET` en tu archivo `.env`.

#### Paso 8: Crear la base de datos

**IMPORTANTE**: El proyecto está configurado principalmente para **PostgreSQL**. El archivo `planta.sql` es un dump de PostgreSQL.

Crea la base de datos en PostgreSQL:

**Para PostgreSQL (RECOMENDADO):**
```sql
CREATE DATABASE planta;
```

**O usando la línea de comandos:**
```bash
createdb -U postgres planta
```

**Si prefieres usar MySQL/MariaDB:**
```sql
CREATE DATABASE planta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**O usando la línea de comandos:**
```bash
mysql -u root -p -e "CREATE DATABASE planta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### Paso 9: Ejecutar las migraciones

```bash
php artisan migrate
```

Este comando creará todas las tablas necesarias en la base de datos según las migraciones definidas en `database/migrations/`.

**Orden de ejecución de migraciones:**
1. Tablas de Spatie Permissions
2. Tablas principales del sistema
3. Tablas de relaciones y campos adicionales

#### Paso 10: Cargar los seeders (datos iniciales)

```bash
php artisan db:seed
```

Este comando ejecutará el `DatabaseSeeder` que carga los siguientes seeders en orden:

1. **RolesAndPermissionsSeeder**: Crea roles y permisos del sistema
2. **UnidadMedidaSeeder**: Unidades de medida
3. **TipoMovimientoSeeder**: Tipos de movimientos
4. **CategoriaMateriaPrimaSeeder**: Categorías de materia prima
5. **VariableEstandarSeeder**: Variables estándar
6. **MaquinaSeeder**: Máquinas del sistema
7. **ProcesoSeeder**: Procesos de producción
8. **OperadorSeeder**: Usuarios/operadores iniciales
9. **ProductoSeeder**: Productos iniciales

**NOTA**: Si prefieres cargar los seeders individualmente:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=UnidadMedidaSeeder
# ... etc
```

#### Paso 11: Configurar permisos de almacenamiento

```bash
# En Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# En Windows, generalmente no es necesario, pero si hay problemas:
# Asegúrate de que la carpeta storage tenga permisos de escritura
```

#### Paso 12: Limpiar y optimizar la caché (opcional pero recomendado)

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

---

### 2. Ejecutar el Backend

#### Opción A: Servidor de desarrollo de Laravel

```bash
php artisan serve
```
o para conexion con movil

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

El servidor estará disponible en: `http://localhost:8000`

#### Opción B: Con Vite para assets frontend (recomendado)

Abre **dos terminales**:

**Terminal 1 - Servidor Laravel:**
```bash
php artisan serve
```
o para conexion con movil

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2 - Vite (compilador de assets):**
```bash
npm run dev
```

#### Opción C: Usando el script de Composer (todo en uno)

```bash
composer run dev
```

Este comando ejecuta simultáneamente:
- Servidor Laravel (puerto 8000)
- Queue worker
- Vite dev server
- Logs en tiempo real

---

### 3. Instalación de la Aplicación Móvil

#### Paso 1: Navegar al directorio de la aplicación móvil

Desde la raíz del proyecto clonado:

```bash
cd Trazabilidad-mobile
```

**TIP**: Si ya estás en la carpeta `Trazabilidad`, primero regresa a la raíz:
```bash
cd ..
cd Trazabilidad-mobile
```

#### Paso 2: Instalar dependencias de Node.js

```bash
npm install
```

Esto instalará todas las dependencias de React Native, Expo, y otras librerías necesarias.

#### Paso 3: Instalar Expo CLI globalmente (si no lo tienes)

```bash
npm install -g expo-cli
```

O usar `npx` sin instalación global:
```bash
npx expo start
```

#### Paso 4: Configurar la URL de la API

Edita el archivo `src/api/client.ts` y actualiza la URL de la API:

```typescript
// Cambiar esta línea:
const API_BASE_URL = 'http://10.26.3.97:8001/api';

// Por la URL de tu servidor backend:
const API_BASE_URL = 'http://TU_IP_LOCAL:8000/api';
// Ejemplo: 'http://192.168.1.100:8000/api'
// O si usas un emulador Android: 'http://10.0.2.2:8000/api'
// O si usas un emulador iOS: 'http://localhost:8000/api'
```

**IMPORTANTE**:
- **Para dispositivo físico**: Usa la IP local de tu computadora (ej: `192.168.1.100`)
- **Para emulador Android**: Usa `10.0.2.2` (IP especial del emulador)
- **Para emulador iOS**: Usa `localhost` o `127.0.0.1`
- Asegúrate de que el puerto coincida con el que usa tu servidor Laravel (por defecto `8000`)

#### Paso 5: Ejecutar la aplicación móvil

```bash
npm start
```

O con Expo CLI:

```bash
expo start
```

Esto abrirá el **Expo Dev Tools** en tu navegador. Desde ahí puedes:

- Presionar `a` para abrir en emulador Android
- Presionar `i` para abrir en simulador iOS (solo macOS)
- Escanear el código QR con la app **Expo Go** en tu dispositivo físico

**Para ejecutar directamente en Android:**
```bash
npm run android
```

**Para ejecutar directamente en iOS (solo macOS):**
```bash
npm run ios
```

---

## Configuración Adicional

### Configurar CORS en Laravel

Si la aplicación móvil no puede conectarse al backend, verifica la configuración de CORS en `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['*'], // En producción, especifica los orígenes permitidos
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

### Variables de Entorno Importantes

Asegúrate de que estas variables estén correctamente configuradas en el `.env` del backend:

| Variable | Descripción | Requerido |
|----------|-------------|-----------|
| `APP_KEY` | Clave de cifrado de Laravel | Sí |
| `JWT_SECRET` | Secreto para JWT Auth | Sí |
| `DB_*` | Configuración de base de datos | Sí |
| `CLOUDINARY_*` | Configuración de Cloudinary | Opcional |
| `PLANTACRUDS_API_URL` | URL de API externa | Opcional |

---

## Base de Datos

### Usar el dump SQL (Alternativa a migraciones)

Si prefieres usar el archivo `planta.sql` en lugar de las migraciones:

**IMPORTANTE**: El archivo `planta.sql` es un **dump de PostgreSQL**. Solo funciona con PostgreSQL.

**Para PostgreSQL:**
```bash
# Desde la raíz del proyecto
psql -U postgres -d trazabilidad_db -f planta.sql

# O si estás dentro de la carpeta Trazabilidad
psql -U postgres -d trazabilidad_db -f ../planta.sql
```

**NOTA**: 
- Si usas el dump SQL, **NO** ejecutes las migraciones (`php artisan migrate`), ya que las tablas ya estarán creadas.
- El dump SQL es específico de PostgreSQL y no funcionará con MySQL/MariaDB.

---

## Verificar la Instalación

### Backend

1. **Aplicación Web**: Visita `http://localhost:8000` - Deberías ver la página de login
   - Después de iniciar sesión, accederás al dashboard web con todas las funcionalidades
2. **API REST**: Prueba la API: `http://localhost:8000/api` - Deberías recibir una respuesta JSON
   - La API está disponible en `/api/*` para la aplicación móvil

### Aplicación Móvil

1. La aplicación debería iniciar sin errores
2. Intenta hacer login con un usuario creado por los seeders
3. Verifica que las peticiones a la API funcionen correctamente

---

## Solución de Problemas Comunes

### Error: "Class 'PDO' not found"
**Solución**: Instala la extensión PDO de PHP:
```bash
# Ubuntu/Debian
sudo apt-get install php-pdo php-pgsql  # o php-mysql

# macOS con Homebrew
brew install php@8.2
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"
**Solución**: 
- Verifica que PostgreSQL/MySQL esté corriendo
- Verifica las credenciales en `.env`
- Verifica que la base de datos exista

### Error: "JWT_SECRET is not set"
**Solución**: Ejecuta:
```bash
php artisan jwt:secret
```

### Error en la app móvil: "Network Error" o "Connection refused"
**Solución**:
- Verifica que el backend esté corriendo
- Verifica la URL en `src/api/client.ts`
- Verifica que el firewall permita conexiones en el puerto 8000
- Si usas dispositivo físico, asegúrate de que esté en la misma red WiFi

### Error: "Permission denied" en storage
**Solución**:
```bash
chmod -R 775 storage bootstrap/cache
```

### Error al ejecutar migraciones: "Table already exists"
**Solución**: 
- Si usaste el dump SQL, no ejecutes migraciones
- O resetea la base de datos: `php artisan migrate:fresh`

---

## Comandos Útiles

### Backend (Laravel)

```bash
# Limpiar todas las cachés
php artisan optimize:clear

# Recrear base de datos y ejecutar seeders
php artisan migrate:fresh --seed

# Ver rutas disponibles
php artisan route:list

# Crear un nuevo usuario
php artisan tinker
# Luego en tinker:
# $user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);

# Ejecutar queue worker
php artisan queue:work
```

### Aplicación Móvil

```bash
# Limpiar caché de npm
npm start -- --reset-cache

# Limpiar node_modules y reinstalar
rm -rf node_modules
npm install

# Ver logs de Expo
expo start --clear
```

---

## Credenciales por Defecto

Después de ejecutar los seeders, deberías tener usuarios creados. Revisa el archivo `Trazabilidad/database/seeders/OperadorSeeder.php` para ver las credenciales por defecto.

**IMPORTANTE**: Cambia las contraseñas por defecto en producción.

---

## Notas Adicionales

### Estructura del Proyecto
- **Trazabilidad** contiene:
  - **Aplicación Web completa** con interfaz Blade (dashboard, gestión de pedidos, lotes, máquinas, procesos, operadores, etc.)
  - **API REST** para la aplicación móvil
  - **Base de datos PostgreSQL** (recomendado, aunque también soporta MySQL/MariaDB)

- **Trazabilidad-mobile** es la aplicación móvil que consume la API

### Tecnologías
- El proyecto usa **Laravel 12** con **PHP 8.2+**
- La aplicación móvil usa **React Native** con **Expo SDK 54**
- El sistema de autenticación usa **JWT (JSON Web Tokens)**
- Las imágenes se almacenan en **Cloudinary** (opcional)
- El sistema incluye integración con **Plantacruds API** (opcional)
- **Base de datos**: PostgreSQL (el archivo `planta.sql` es un dump de PostgreSQL)

---

## Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## Licencia

Este proyecto es privado y de uso interno.

---

## Soporte

Si encuentras algún problema durante la instalación, verifica:
1. Que todos los requisitos previos estén instalados
2. Que las variables de entorno estén correctamente configuradas
3. Que la base de datos esté creada y accesible
4. Los logs de Laravel en `storage/logs/laravel.log`
5. Los logs de Expo en la terminal donde ejecutaste `expo start`

---

**¡Listo! Tu sistema de trazabilidad debería estar funcionando correctamente.**

