<?php
/**
 * Script de prueba simplificado para crear y aprobar pedidos
 * que se sincronicen con plantaCruds
 */

// Cargar Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\OrderDestinationProduct;
use App\Models\OrderEnvioTracking;
use App\Services\PlantaCrudsIntegrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$apiUrl = env('PLANTACRUDS_API_URL');

// Función para obtener almacenes de plantaCruds
function getAlmacenesPlantaCruds() {
    global $apiUrl;
    try {
        $response = Http::timeout(5)->get("{$apiUrl}/almacenes");
        if ($response->successful()) {
            return $response->json('data', []);
        }
    } catch (\Exception $e) {
        return [];
    }
    return [];
}

// Procesar formulario
$mensaje = '';
$error = '';
$envioCodigo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_y_aprobar'])) {
    try {
        DB::beginTransaction();
        
        $almacenId = $_POST['almacen_id'] ?? null;
        $almacenNombre = $_POST['almacen_nombre'] ?? 'Almacén desconocido';
        $almacenLat = $_POST['almacen_lat'] ?? null;
        $almacenLng = $_POST['almacen_lng'] ?? null;
        $almacenDireccion = $_POST['almacen_direccion'] ?? '';
        
        if (!$almacenId) {
            throw new \Exception('Debe seleccionar un almacén');
        }
        
        // Obtener o crear cliente de prueba
        $customer = Customer::firstOrCreate(
            ['tax_id' => '12345678'],
            [
                'business_name' => 'Cliente de Prueba Integración',
                'trading_name' => 'Cliente Prueba',
                'address' => 'Dirección de prueba',
                'phone' => '999888777',
                'email' => 'prueba@test.com',
                'contact_person' => 'Juan Pérez',
                'active' => true
            ]
        );
        
        // Obtener o crear producto de prueba
        $product = Product::firstOrCreate(
            ['code' => 'PROD-TEST-001'],
            [
                'name' => 'Producto de Prueba para Integración',
                'type' => 'material',
                'weight' => 25.5,
                'description' => 'Producto creado para probar la integración',
                'active' => true
            ]
        );
        
        // Crear pedido
        $orderNumber = 'PED-TEST-' . date('Ymd-His');
        $order = CustomerOrder::create([
            'customer_id' => $customer->customer_id,
            'order_number' => $orderNumber,
            'name' => 'Pedido de Prueba - Integración plantaCruds',
            'status' => 'pendiente',
            'creation_date' => now(),
            'delivery_date' => now()->addDays(3),
            'priority' => 5,
            'description' => 'Pedido creado automáticamente para probar la integración con plantaCruds',
            'observations' => "Este pedido será enviado al almacén: {$almacenNombre}"
        ]);
        
        // Crear producto del pedido
        $orderProduct = OrderProduct::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => 100,
            'status' => 'pendiente',
            'observations' => 'Producto de prueba'
        ]);
        
        // Crear destino
        $destination = OrderDestination::create([
            'order_id' => $order->order_id,
            'address' => $almacenDireccion ?: $almacenNombre,
            'reference' => 'Almacén de plantaCruds',
            'latitude' => $almacenLat,
            'longitude' => $almacenLng,
            'contact_name' => 'Encargado de Almacén',
            'contact_phone' => '999777888',
            'delivery_instructions' => 'Entrega directa en almacén'
        ]);
        
        // Relacionar producto con destino
        OrderDestinationProduct::create([
            'destination_id' => $destination->destination_id,
            'order_product_id' => $orderProduct->order_product_id,
            'quantity' => 100,
            'observations' => 'Entrega completa'
        ]);
        
        DB::commit();
        
        // Ahora aprobar el pedido
        DB::beginTransaction();
        
        // Aprobar producto
        $orderProduct->update([
            'status' => 'aprobado',
            'approved_by' => 1,
            'approved_at' => now()
        ]);
        
        // Aprobar pedido
        $order->update([
            'status' => 'aprobado',
            'approved_by' => 1,
            'approved_at' => now()
        ]);
        
        DB::commit();
        
        // Integración con plantaCruds
        $integrationService = new PlantaCrudsIntegrationService();
        $results = $integrationService->sendOrderToShipping($order);
        
        // Guardar tracking
        foreach ($results as $result) {
            $trackingData = [
                'order_id' => $order->order_id,
                'destination_id' => $result['destination_id'],
                'status' => $result['success'] ? 'success' : 'failed',
            ];
            
            if ($result['success']) {
                $trackingData['envio_id'] = $result['envio_id'];
                $trackingData['envio_codigo'] = $result['envio_codigo'];
                $trackingData['response_data'] = $result['response'] ?? null;
                $envioCodigo = $result['envio_codigo'];
            } else {
                $trackingData['error_message'] = $result['error'];
            }
            
            OrderEnvioTracking::create($trackingData);
        }
        
        if (!empty($envioCodigo)) {
            $mensaje = "✅ ¡Éxito! Pedido creado y aprobado: {$orderNumber}<br>";
            $mensaje .= "✅ Envío creado en plantaCruds: <strong>{$envioCodigo}</strong><br>";
            $mensaje .= "✅ Almacén destino: {$almacenNombre}";
        } else {
            $error = "⚠️ Pedido creado pero hubo un error en la integración: " . ($results[0]['error'] ?? 'Error desconocido');
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Obtener almacenes
$almacenes = getAlmacenesPlantaCruds();

// Obtener últimos trackings
$trackings = OrderEnvioTracking::with(['order', 'destination'])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Integración - Trazabilidad → plantaCruds</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        select:hover {
            border-color: #667eea;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
            border-left: 4px solid #667eea;
        }
        
        .info-box strong {
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e1e1e1;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            color: #666;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
        
        .almacen-info {
            font-size: 12px;
            color: #999;
            margin-left: 10px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 2px solid #e1e1e1;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🚀 Test de Integración</h1>
            <p class="subtitle">Trazabilidad → plantaCruds</p>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (empty($almacenes)): ?>
                <div class="alert alert-danger">
                    ❌ No se pudo conectar con plantaCruds API.<br>
                    Verifica que el servidor esté corriendo en: <strong><?= $apiUrl ?></strong>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    ✅ Conectado con plantaCruds - <strong><?= count($almacenes) ?> almacenes</strong> disponibles
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="almacen_id">📦 Seleccionar Almacén de Destino</label>
                        <select name="almacen_id" id="almacen_id" required onchange="updateAlmacenInfo(this)">
                            <option value="">-- Seleccione un almacén --</option>
                            <?php foreach ($almacenes as $almacen): ?>
                                <option 
                                    value="<?= $almacen['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($almacen['nombre']) ?>"
                                    data-direccion="<?= htmlspecialchars($almacen['direccion'] ?? '') ?>"
                                    data-lat="<?= $almacen['latitud'] ?? '' ?>"
                                    data-lng="<?= $almacen['longitud'] ?? '' ?>"
                                >
                                    <?= htmlspecialchars($almacen['nombre']) ?>
                                    <?php if (!empty($almacen['direccion'])): ?>
                                        - <?= htmlspecialchars($almacen['direccion']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Campos ocultos para datos del almacén -->
                        <input type="hidden" name="almacen_nombre" id="almacen_nombre">
                        <input type="hidden" name="almacen_direccion" id="almacen_direccion">
                        <input type="hidden" name="almacen_lat" id="almacen_lat">
                        <input type="hidden" name="almacen_lng" id="almacen_lng">
                    </div>
                    
                    <button type="submit" name="crear_y_aprobar" class="btn">
                        ✨ Crear Pedido y Aprobar Automáticamente
                    </button>
                    
                    <div class="info-box">
                        <strong>¿Qué hace este botón?</strong><br>
                        1. Crea un pedido de prueba en Trazabilidad<br>
                        2. Lo aprueba automáticamente<br>
                        3. Envía la información a plantaCruds para crear el envío<br>
                        4. Guarda el tracking de la sincronización
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($trackings)): ?>
        <div class="card">
            <h2>📊 Últimas Sincronizaciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Destino</th>
                        <th>Código Envío</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trackings as $tracking): ?>
                    <tr>
                        <td><?= $tracking->order->order_number ?? 'N/A' ?></td>
                        <td><?= Illuminate\Support\Str::limit($tracking->destination->address ?? 'N/A', 40) ?></td>
                        <td><strong><?= $tracking->envio_codigo ?? '-' ?></strong></td>
                        <td>
                            <span class="status status-<?= $tracking->status ?>">
                                <?= strtoupper($tracking->status) ?>
                            </span>
                        </td>
                        <td><?= $tracking->created_at->format('d/m/Y H:i') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateAlmacenInfo(select) {
            const option = select.options[select.selectedIndex];
            document.getElementById('almacen_nombre').value = option.dataset.nombre || '';
            document.getElementById('almacen_direccion').value = option.dataset.direccion || '';
            document.getElementById('almacen_lat').value = option.dataset.lat || '';
            document.getElementById('almacen_lng').value = option.dataset.lng || '';
        }
    </script>
</body>
</html>
