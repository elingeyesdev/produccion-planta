<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductionBatchController;
use App\Http\Controllers\Api\ProcessTransformationController;
use App\Http\Controllers\Api\ProcessEvaluationController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\RawMaterialController;
use App\Http\Controllers\Api\RawMaterialBaseController;
use App\Http\Controllers\Api\MaterialMovementLogController;
use App\Http\Controllers\Api\UnitOfMeasureController;
use App\Http\Controllers\Api\AlmacenController;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
// Crear pedido sin autenticación (crea cliente automáticamente)
// El token es opcional: si hay token usa el cliente del usuario, si no hay token usa datos del body
Route::post('/customer-orders', [CustomerOrderController::class, 'store'])->withoutMiddleware(['auth:api']);
// Obtener pedidos por nombre_usuario sin autenticación
Route::get('/customer-orders/by-user', [CustomerOrderController::class, 'byUser'])->withoutMiddleware(['auth:api']);
// Actualizar pedido sin autenticación (valida nombre_usuario)
Route::put('/customer-orders/{id}/public', [CustomerOrderController::class, 'updatePublic'])->withoutMiddleware(['auth:api']);
// Obtener productos disponibles (token opcional)
Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index'])->withoutMiddleware(['auth:api']);
Route::get('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show'])->withoutMiddleware(['auth:api']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // CRUD Routes using apiResource (ibex pattern)
    Route::apiResource('unit-of-measures', UnitOfMeasureController::class);
    Route::apiResource('statuses', \App\Http\Controllers\Api\StatusController::class);
    Route::apiResource('movement-types', \App\Http\Controllers\Api\MovementTypeController::class);
    Route::apiResource('operator-roles', \App\Http\Controllers\Api\OperatorRoleController::class);
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class);
    Route::apiResource('raw-material-categories', \App\Http\Controllers\Api\RawMaterialCategoryController::class);
    Route::apiResource('suppliers', \App\Http\Controllers\Api\SupplierController::class);
    Route::apiResource('standard-variables', \App\Http\Controllers\Api\StandardVariableController::class);
    Route::apiResource('machines', \App\Http\Controllers\Api\MachineController::class);
    Route::apiResource('processes', \App\Http\Controllers\Api\ProcessController::class);
    Route::apiResource('operators', \App\Http\Controllers\Api\OperatorController::class);
    Route::apiResource('raw-material-bases', RawMaterialBaseController::class);
    Route::apiResource('raw-materials', RawMaterialController::class);
    // Customer orders - solo las rutas que requieren autenticación (store ya está fuera)
    Route::get('/customer-orders', [CustomerOrderController::class, 'index']);
    Route::get('/customer-orders/{id}', [CustomerOrderController::class, 'show']);
    Route::put('/customer-orders/{id}', [CustomerOrderController::class, 'update']);
    Route::delete('/customer-orders/{id}', [CustomerOrderController::class, 'destroy']);
    Route::post('/customer-orders/{id}/cancel', [CustomerOrderController::class, 'cancel']);
    // Products - solo las rutas que requieren autenticación (index y show ya están fuera)
    Route::post('/products', [\App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::put('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy']);
    // Order Approval Routes
    Route::prefix('order-approval')->group(function () {
        Route::get('/pending', [\App\Http\Controllers\Api\OrderApprovalController::class, 'pendingOrders']);
        Route::get('/{id}', [\App\Http\Controllers\Api\OrderApprovalController::class, 'show']);
        Route::post('/{orderId}/approve', [\App\Http\Controllers\Api\OrderApprovalController::class, 'approveOrder']);
        Route::post('/{orderId}/product/{productId}/approve', [\App\Http\Controllers\Api\OrderApprovalController::class, 'approveProduct']);
        Route::post('/{orderId}/product/{productId}/reject', [\App\Http\Controllers\Api\OrderApprovalController::class, 'rejectProduct']);
    });

    // Almacenes Routes (sincronizados desde plantaCruds)
    Route::prefix('almacenes')->group(function () {
        Route::get('/', [AlmacenController::class, 'index']);
        Route::get('/planta', [AlmacenController::class, 'planta']);
        Route::get('/destinos', [AlmacenController::class, 'destinos']);
        Route::get('/nearest', [AlmacenController::class, 'nearest']);
        Route::post('/clear-cache', [AlmacenController::class, 'clearCache']);
    });
          
    Route::apiResource('production-batches', ProductionBatchController::class);
    Route::apiResource('batch-raw-materials', \App\Http\Controllers\Api\BatchRawMaterialController::class);
    Route::apiResource('material-movement-logs', MaterialMovementLogController::class);
    Route::apiResource('process-machines', \App\Http\Controllers\Api\ProcessMachineController::class);
    Route::apiResource('process-machine-variables', \App\Http\Controllers\Api\ProcessMachineVariableController::class);
    Route::apiResource('process-machine-records', \App\Http\Controllers\Api\ProcessMachineRecordController::class);
    Route::apiResource('process-final-evaluations', \App\Http\Controllers\Api\ProcessFinalEvaluationController::class);
    Route::apiResource('storages', StorageController::class);
    Route::apiResource('material-requests', \App\Http\Controllers\Api\MaterialRequestController::class);
    Route::apiResource('material-request-details', \App\Http\Controllers\Api\MaterialRequestDetailController::class);
    Route::apiResource('supplier-responses', \App\Http\Controllers\Api\SupplierResponseController::class);

    // Custom routes for business logic
    // Process Transformation
    Route::prefix('process-transformation')->group(function () {
        Route::post('/batch/{batchId}/machine/{processMachineId}', [ProcessTransformationController::class, 'registerForm']);
        Route::get('/batch/{batchId}/machine/{processMachineId}', [ProcessTransformationController::class, 'getForm']);
        Route::get('/batch/{batchId}', [ProcessTransformationController::class, 'getBatchProcess']);
    });

    // Process Evaluation
    Route::prefix('process-evaluation')->group(function () {
        Route::post('/finalize/{batchId}', [ProcessEvaluationController::class, 'finalize']);
        Route::get('/log/{batchId}', [ProcessEvaluationController::class, 'getLog']);
    });

    // Storage custom routes
    Route::get('/storages/batch/{batchId}', [StorageController::class, 'getByBatch']);
    
    // Material Movement Log custom routes
    Route::get('/material-movement-logs/material/{materialId}', [MaterialMovementLogController::class, 'getByMaterial']);

    // Batch Certification routes
    Route::prefix('batches')->group(function () {
        Route::get('/pending-certification', [ProductionBatchController::class, 'getPendingCertification']);
        Route::post('/{batchId}/assign-process', [ProductionBatchController::class, 'assignProcess']);
        Route::get('/{batchId}/process-machines', [ProductionBatchController::class, 'getProcessMachines']);
        Route::post('/{batchId}/finalize-certification', [ProductionBatchController::class, 'finalizeCertification']);
        Route::get('/{batchId}/certification-log', [ProductionBatchController::class, 'getCertificationLog']);
    });

    // Image Upload
    Route::post('/upload', [\App\Http\Controllers\Web\ImageUploadController::class, 'upload']);
});

// Rutas públicas (sin autenticación) para integración con sistema-almacen-PSIII
Route::post('/pedidos-almacen', [\App\Http\Controllers\Api\AlmacenPedidoController::class, 'store'])
    ->name('api.pedidos-almacen');

// Ruta pública (sin autenticación) para recibir documentos de entrega desde plantaCruds
Route::post('/pedidos/{pedido}/documentos-entrega', [\App\Http\Controllers\Api\PedidoDocumentosController::class, 'recibirDocumentos'])
    ->name('api.pedidos.documentos-entrega');

// Ruta pública para buscar pedido por envio_id (para integración con plantaCruds)
Route::get('/pedidos/by-envio/{envioId}', [\App\Http\Controllers\Api\CustomerOrderController::class, 'getByEnvioId'])
    ->name('api.pedidos.by-envio');

// Ruta pública para obtener información completa de todos los pedidos (sin autenticación)
// Incluye toda la información: pedido, cliente, productos, destinos, montos, etc.
Route::get('/pedidos/completo', [\App\Http\Controllers\Api\CustomerOrderController::class, 'getAllCompleteOrders'])
    ->name('api.pedidos.completo.all');

// Ruta pública para obtener información completa de un pedido específico (sin autenticación)
Route::get('/pedidos/{id}/completo', [\App\Http\Controllers\Api\CustomerOrderController::class, 'getCompleteOrder'])
    ->name('api.pedidos.completo');

// Legacy routes (keeping for compatibility)
// Comentado para evitar conflicto con rutas web
// Route::apiResource('procesos', \App\Http\Controllers\ProcesoController::class);
Route::apiResource('operadores', \App\Http\Controllers\OperadorController::class);
Route::apiResource('proveedores', \App\Http\Controllers\ProveedorController::class);
// Comentado para evitar conflicto con rutas web
// Route::apiResource('maquinas', \App\Http\Controllers\MaquinaController::class);
