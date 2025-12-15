# Guía de Prueba - Integración Trazabilidad → plantaCruds

## Descripción
Esta integración permite que cuando un pedido es aprobado en Trazabilidad, automáticamente se crean los envíos correspondientes en plantaCruds.

## Componentes Implementados

### 1. PlantaCrudsIntegrationService
**Ubicación**: `Trazabilidad/app/Services/PlantaCrudsIntegrationService.php`

Servicio que maneja:
- Transformación de datos de pedido a envío
- Mapeo de productos por nombre
- Búsqueda de almacenes por coordenadas o dirección
- Creación de observaciones detalladas
- Comunicación HTTP con la API de plantaCruds

### 2. OrderEnvioTracking Model
**Ubicación**: `Trazabilidad/app/Models/OrderEnvioTracking.php`

Tabla que registra:
- Relación order_id → destination_id → envio_id
- Estado de sincronización (pending, success, failed)
- Códigos de envío generados
- Datos de request/response
- Mensajes de error

### 3. OrderApprovalController - Modificado
**Ubicación**: `Trazabilidad/app/Http/Controllers/Api/OrderApprovalController.php`

Al aprobar un pedido:
1. Aprueba todos los productos pendientes
2. Marca el pedido como aprobado
3. Llama al servicio de integración
4. Crea un envío en plantaCruds por cada destino
5. Guarda el tracking de cada envío
6. Retorna respuesta con envíos creados y posibles errores

### 4. EnvioApiController - Ajustado
**Ubicación**: `plantaCruds/app/Http/Controllers/Api/EnvioApiController.php`

Cambios:
- `producto_id` ahora es nullable
- Se acepta `producto_nombre` como string
- Si viene `producto_id`, se busca el nombre automáticamente
- Si viene `producto_nombre`, se usa directamente

## Configuración

### Variables de Entorno

**Trazabilidad (.env)**:
```env
PLANTACRUDS_API_URL=http://localhost/plantaCruds/public/api
```

Ajustar según tu configuración local:
- Si usas XAMPP: `http://localhost/plantaCruds/public/api`
- Si usas Laravel Serve: `http://localhost:8000/api`
- Si está en red local: `http://192.168.x.x/plantaCruds/public/api`

## Flujo de Integración

```
1. Usuario aprueba pedido en Trazabilidad
   ↓
2. OrderApprovalController::approveOrder()
   - Marca pedido como "aprobado"
   - Actualiza todos los productos a "aprobado"
   ↓
3. PlantaCrudsIntegrationService::sendOrderToShipping()
   - Carga pedido con todas las relaciones
   - Por cada destino del pedido:
     ↓
4. buildEnvioData()
   - Busca almacén coincidente en plantaCruds
   - Mapea productos a estructura de envío
   - Construye observaciones con datos del pedido
     ↓
5. createEnvio() - HTTP POST
   POST /api/envios
   {
     "almacen_destino_id": 1,
     "categoria": "general",
     "fecha_estimada_entrega": "2025-12-15",
     "productos": [
       {
         "producto_nombre": "Cemento Portland",
         "cantidad": 50,
         "peso_kg": 25,
         "precio": 15.50
       }
     ]
   }
     ↓
6. plantaCruds recibe y crea envío
   - Genera código único (ENV-YYMMDD-XXXXXX)
   - Crea registro en tabla `envios`
   - Crea registros en `envio_productos`
   - Genera QR code
   - Retorna datos del envío
     ↓
7. Guarda tracking en Trazabilidad
   - order_envio_tracking
   - Almacena envio_id, envio_codigo
   - Status: success o failed
```

## Cómo Probar

### Opción 1: Usando la API directamente

#### Paso 1: Crear un pedido de prueba
```bash
POST http://localhost/trazabilidad/public/api/customer-orders
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
  "customer_id": 1,
  "name": "Pedido de Prueba Integración",
  "delivery_date": "2025-12-15",
  "priority": 5,
  "description": "Pedido para probar la integración con plantaCruds",
  "products": [
    {
      "product_id": 1,
      "quantity": 50,
      "observations": "Producto de prueba"
    }
  ],
  "destinations": [
    {
      "address": "Av. Principal 123, Lima",
      "latitude": -12.0464,
      "longitude": -77.0428,
      "reference": "Frente al parque",
      "contact_name": "Juan Pérez",
      "contact_phone": "999888777",
      "delivery_instructions": "Llamar antes de llegar",
      "products": [
        {
          "order_product_index": 0,
          "quantity": 50
        }
      ]
    }
  ]
}
```

#### Paso 2: Aprobar el pedido
```bash
POST http://localhost/trazabilidad/public/api/order-approval/{orderId}/approve
Authorization: Bearer YOUR_JWT_TOKEN
```

#### Paso 3: Verificar respuesta
La respuesta incluirá:
```json
{
  "message": "Pedido aprobado exitosamente",
  "order": { ... },
  "envios_created": [
    {
      "destination_id": 1,
      "envio_codigo": "ENV-251208-000001"
    }
  ],
  "integration_success": true
}
```

#### Paso 4: Verificar en plantaCruds
```bash
GET http://localhost/plantaCruds/public/api/envios

# Buscar el envío por código
GET http://localhost/plantaCruds/public/api/envios/qr/ENV-251208-000001
```

### Opción 2: Usando scripts de prueba

Crear archivo: `Trazabilidad/test_integration.php`
```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Buscar un pedido pendiente
$order = \App\Models\CustomerOrder::where('status', 'pendiente')->first();

if (!$order) {
    echo "No hay pedidos pendientes\n";
    exit;
}

echo "Aprobando pedido #{$order->order_id} - {$order->order_number}\n";

// Simular aprobación
$service = new \App\Services\PlantaCrudsIntegrationService();
$results = $service->sendOrderToShipping($order);

echo "Resultados:\n";
print_r($results);
```

Ejecutar:
```bash
cd "c:\Users\Personal\Downloads\planta jhair\Trazabilidad"
php test_integration.php
```

## Verificación del Tracking

### Ver todos los trackings
```sql
SELECT 
    oet.*,
    co.order_number,
    od.address
FROM order_envio_tracking oet
JOIN customer_order co ON oet.order_id = co.order_id
JOIN order_destination od ON oet.destination_id = od.destination_id
ORDER BY oet.created_at DESC;
```

### Ver solo errores
```sql
SELECT * FROM order_envio_tracking 
WHERE status = 'failed'
ORDER BY created_at DESC;
```

### Ver envíos exitosos
```sql
SELECT 
    oet.envio_codigo,
    co.order_number,
    od.address,
    oet.created_at
FROM order_envio_tracking oet
JOIN customer_order co ON oet.order_id = co.order_id
JOIN order_destination od ON oet.destination_id = od.destination_id
WHERE oet.status = 'success'
ORDER BY oet.created_at DESC;
```

## Manejo de Errores

### Errores Comunes

#### 1. "No hay almacenes disponibles en plantaCruds"
**Causa**: No se encontró almacén coincidente por coordenadas o dirección.

**Solución**: 
- Crear almacenes en plantaCruds que coincidan con destinos comunes
- O modificar `findOrCreateAlmacen()` para usar un almacén por defecto

#### 2. "Error al crear envío en plantaCruds (HTTP 500)"
**Causa**: Validación fallida o error en plantaCruds.

**Solución**:
- Revisar logs en `plantaCruds/storage/logs/laravel.log`
- Verificar que existan productos en la tabla `productos`
- Verificar estructura de datos enviados

#### 3. "Connection refused"
**Causa**: URL incorrecta o servidor no accesible.

**Solución**:
- Verificar `PLANTACRUDS_API_URL` en `.env`
- Verificar que plantaCruds esté corriendo
- Probar acceso directo: `curl http://localhost/plantaCruds/public/api/ping`

## Logs

### Trazabilidad
```bash
tail -f "c:\Users\Personal\Downloads\planta jhair\Trazabilidad\storage\logs\laravel.log"
```

Buscar:
- `Envio created successfully in plantaCruds`
- `Failed to create Envio in plantaCruds`
- `PlantaCruds integration completed`

### plantaCruds
```bash
tail -f "c:\Users\Personal\Downloads\proyectoplantajunto\Planta\plantaCruds\storage\logs\laravel.log"
```

Buscar:
- `Envío creado exitosamente`
- `Error al crear envío`

## Características Avanzadas

### 1. Múltiples Destinos
Un pedido con N destinos generará N envíos en plantaCruds.

Ejemplo:
- Pedido #PED-0001 con 3 destinos
- Genera: ENV-001, ENV-002, ENV-003
- Cada uno rastreado independientemente

### 2. Mapeo de Almacenes
El servicio busca almacenes en este orden:
1. Por coordenadas (tolerancia: 0.001 grados ≈ 100m)
2. Por coincidencia de dirección (string matching)
3. Primer almacén activo (fallback)

### 3. Observaciones Enriquecidas
Las observaciones en plantaCruds incluyen:
- Número de pedido original
- Nombre del cliente
- Notas del pedido
- Instrucciones de entrega
- Datos de contacto

Ejemplo:
```
Pedido: PED-0001-20251208
Cliente: Constructora ABC S.A.C.
Notas: Entrega urgente
Instrucciones: Descargar por rampa lateral
Contacto: Juan Pérez - Tel: 999888777
Dirección: Av. Principal 123 (Frente al parque)
```

## Próximos Pasos (Opcional)

### 1. Webhooks de Estado
Implementar callback de plantaCruds a Trazabilidad para actualizar estados:
- Cuando el envío es asignado a transportista
- Cuando está en tránsito
- Cuando es entregado

### 2. Sincronización de Productos
Pre-sincronizar catálogo de productos entre sistemas.

### 3. Autenticación API
Agregar API token a plantaCruds para mayor seguridad.

### 4. Reintentos Automáticos
Implementar queue jobs para reintentar integraciones fallidas.

### 5. Panel de Administración
UI para ver y gestionar el tracking de envíos.

## Soporte

Para problemas con la integración:
1. Revisar logs de ambos sistemas
2. Verificar tabla `order_envio_tracking`
3. Probar endpoints manualmente con Postman
4. Verificar conectividad de red entre sistemas
