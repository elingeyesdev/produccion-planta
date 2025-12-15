<?php

/**
 * Script de prueba para verificar la integración Trazabilidad → plantaCruds
 * 
 * Este script verifica:
 * 1. Conectividad con la API de plantaCruds
 * 2. Disponibilidad de almacenes
 * 3. Funcionalidad del servicio de integración
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST DE INTEGRACIÓN TRAZABILIDAD → PLANTACRUDS ===\n\n";

// Test 1: Verificar configuración
echo "1. Verificando configuración...\n";
$apiUrl = env('PLANTACRUDS_API_URL');
echo "   API URL: {$apiUrl}\n";

if (!$apiUrl) {
    echo "   ❌ ERROR: PLANTACRUDS_API_URL no está configurado en .env\n";
    exit(1);
}

// Test 2: Verificar conectividad
echo "\n2. Verificando conectividad con plantaCruds...\n";
try {
    $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$apiUrl}/almacenes");
    
    if ($response->successful()) {
        $almacenes = $response->json('data', []);
        echo "   ✓ Conectado exitosamente\n";
        echo "   ✓ Almacenes disponibles: " . count($almacenes) . "\n";
        
        if (count($almacenes) > 0) {
            echo "   Ejemplo: {$almacenes[0]['nombre']} (ID: {$almacenes[0]['id']})\n";
        } else {
            echo "   ⚠️ ADVERTENCIA: No hay almacenes en plantaCruds\n";
        }
    } else {
        echo "   ❌ ERROR: Respuesta HTTP {$response->status()}\n";
        echo "   Body: {$response->body()}\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "   ❌ ERROR: No se puede conectar a plantaCruds\n";
    echo "   Mensaje: {$e->getMessage()}\n";
    exit(1);
}

// Test 3: Verificar base de datos
echo "\n3. Verificando base de datos...\n";
$pedidosPendientes = \App\Models\CustomerOrder::where('status', 'pendiente')->count();
echo "   Pedidos pendientes: {$pedidosPendientes}\n";

$trackingTotal = \App\Models\OrderEnvioTracking::count();
echo "   Registros de tracking: {$trackingTotal}\n";

if ($trackingTotal > 0) {
    $exitosos = \App\Models\OrderEnvioTracking::where('status', 'success')->count();
    $fallidos = \App\Models\OrderEnvioTracking::where('status', 'failed')->count();
    echo "   - Exitosos: {$exitosos}\n";
    echo "   - Fallidos: {$fallidos}\n";
}

// Test 4: Verificar servicio
echo "\n4. Verificando servicio de integración...\n";
try {
    $service = new \App\Services\PlantaCrudsIntegrationService();
    echo "   ✓ Servicio instanciado correctamente\n";
} catch (\Exception $e) {
    echo "   ❌ ERROR: {$e->getMessage()}\n";
    exit(1);
}

// Test 5: Buscar pedido de prueba (opcional)
echo "\n5. Buscando pedido de prueba...\n";
$pedidoPrueba = \App\Models\CustomerOrder::with([
    'customer',
    'orderProducts.product',
    'destinations'
])->where('status', 'pendiente')->first();

if ($pedidoPrueba) {
    echo "   ✓ Pedido encontrado: {$pedidoPrueba->order_number}\n";
    echo "   - Cliente: {$pedidoPrueba->customer->business_name}\n";
    echo "   - Productos: " . $pedidoPrueba->orderProducts->count() . "\n";
    echo "   - Destinos: " . $pedidoPrueba->destinations->count() . "\n";
    
    echo "\n   ¿Deseas aprobar este pedido y probar la integración? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    
    if (trim($line) === 'y') {
        echo "\n   Procesando integración...\n";
        
        try {
            // Simular aprobación de productos
            \App\Models\OrderProduct::where('order_id', $pedidoPrueba->order_id)
                ->where('status', 'pendiente')
                ->update([
                    'status' => 'aprobado',
                    'approved_by' => 1,
                    'approved_at' => now(),
                ]);
            
            // Aprobar pedido
            $pedidoPrueba->update([
                'status' => 'aprobado',
                'approved_by' => 1,
                'approved_at' => now(),
            ]);
            
            echo "   ✓ Pedido aprobado\n";
            
            // Ejecutar integración
            $results = $service->sendOrderToShipping($pedidoPrueba);
            
            echo "\n   Resultados de la integración:\n";
            foreach ($results as $result) {
                if ($result['success']) {
                    echo "   ✓ Destino #{$result['destination_id']}: Envío creado\n";
                    echo "      Código: {$result['envio_codigo']}\n";
                    echo "      ID: {$result['envio_id']}\n";
                } else {
                    echo "   ❌ Destino #{$result['destination_id']}: ERROR\n";
                    echo "      {$result['error']}\n";
                }
            }
            
            echo "\n   ✓ Integración completada\n";
            
        } catch (\Exception $e) {
            echo "   ❌ ERROR en integración: {$e->getMessage()}\n";
            echo "   Trace: {$e->getTraceAsString()}\n";
        }
    } else {
        echo "   Integración cancelada\n";
    }
} else {
    echo "   ℹ️ No hay pedidos pendientes para probar\n";
    echo "   Puedes crear un pedido usando la API de Trazabilidad\n";
}

echo "\n=== FIN DEL TEST ===\n";
echo "\nPara más información, consulta: INTEGRACION_PLANTACRUDS.md\n";
