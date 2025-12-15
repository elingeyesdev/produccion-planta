<?php

use App\Models\CustomerOrder;
use App\Models\OrderDestination;
use App\Models\OrderProduct;
use App\Models\DestinationProduct;
use App\Services\PlantaCrudsIntegrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Iniciando prueba de integración...\n";

    // Login as admin for approval
    Auth::loginUsingId(1);

    echo "Buscando pedido existente...\n";
    $order = CustomerOrder::where('order_number', 'PED-0003-20251208')->firstOrFail();

    // Resetear estado
    $order->status = 'pendiente';
    $order->save();

    // Actualizar destino para tener un almacen_destino_id válido
    $destination = $order->destinations->first();
    if ($destination) {
        $destination->almacen_destino_id = 2;
        $destination->save();
    }

    echo "Pedido reseteado: {$order->order_number} (ID: {$order->order_id})\n";

    echo "Intentando aprobar y enviar a plantaCruds...\n";

    $integration = new PlantaCrudsIntegrationService();
    $results = $integration->sendOrderToShipping($order);

    print_r($results);

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
