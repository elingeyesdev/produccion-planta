<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DashboardClienteController;
use App\Http\Controllers\Web\GestionLotesController;
use App\Http\Controllers\Web\MateriaPrimaBaseController;
use App\Http\Controllers\Web\SolicitarMateriaPrimaController;
use App\Http\Controllers\Web\RecepcionMateriaPrimaController;
use App\Http\Controllers\Web\ProveedorWebController;
use App\Http\Controllers\Web\MaquinaWebController;
use App\Http\Controllers\Web\ProcesoWebController;
use App\Http\Controllers\Web\OperadorWebController;
use App\Http\Controllers\Web\VariablesEstandarController;
use App\Http\Controllers\Web\CertificarLoteController;
use App\Http\Controllers\Web\CertificadosController;
use App\Http\Controllers\Web\AlmacenajeController;
use App\Http\Controllers\Web\LotesAlmacenadosController;
use App\Http\Controllers\Web\PedidosController;
use App\Http\Controllers\Web\GestionPedidosController;
use App\Http\Controllers\Web\UsuariosController;
use App\Http\Controllers\Web\ProcesoTransformacionController;
use App\Http\Controllers\Web\PlantaUbicacionController;
use App\Http\Controllers\Web\RutaTiempoRealController;
use App\Http\Controllers\Web\DocumentacionPedidosController;

Route::redirect('/', '/login');

// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Ruta pública para ver certificados (accesible desde QR)
Route::get('/certificado-publico/{id}', [CertificadosController::class, 'showPublic'])->name('certificado.publico');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    // Dashboards
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:ver panel control')->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->middleware('permission:ver panel control')->name('dashboard.data');
    Route::get('/dashboard-cliente', [DashboardClienteController::class, 'index'])->middleware('permission:ver panel cliente')->name('dashboard-cliente');
    Route::get('/dashboard-cliente/data', [DashboardClienteController::class, 'getData'])->middleware('permission:ver panel cliente')->name('dashboard-cliente.data');
    Route::get('/dashboard-cliente/pedido/{orderId}', [DashboardClienteController::class, 'obtenerDetallesPedido'])->middleware('permission:ver panel cliente')->name('dashboard-cliente.pedido.detalles');

    // Materia Prima
    Route::middleware('permission:ver materia prima')->group(function () {
        Route::get('/materia-prima-base', [MateriaPrimaBaseController::class, 'index'])->name('materia-prima-base');
        Route::post('/materia-prima-base', [MateriaPrimaBaseController::class, 'store']);
        Route::get('/materia-prima-base/{id}', [MateriaPrimaBaseController::class, 'show'])->name('materia-prima-base.show');
        Route::put('/materia-prima-base/{id}', [MateriaPrimaBaseController::class, 'update'])->name('materia-prima-base.update');
    });
    
    Route::get('/solicitar-materia-prima', [SolicitarMateriaPrimaController::class, 'index'])->middleware('permission:solicitar materia prima')->name('solicitar-materia-prima');
    Route::post('/solicitar-materia-prima', [SolicitarMateriaPrimaController::class, 'store'])->middleware('permission:solicitar materia prima');
    Route::get('/solicitar-materia-prima/pedido/{pedidoId}/materias-primas', [SolicitarMateriaPrimaController::class, 'getMateriasPrimasPorPedido'])->middleware('permission:solicitar materia prima')->name('solicitar-materia-prima.get-materias-primas');
    
    Route::get('/recepcion-materia-prima', [RecepcionMateriaPrimaController::class, 'index'])->middleware('permission:recepcionar materia prima')->name('recepcion-materia-prima');
    Route::post('/recepcion-materia-prima', [RecepcionMateriaPrimaController::class, 'store'])->middleware('permission:recepcionar materia prima');
    Route::post('/recepcion-materia-prima/sync-envios', [RecepcionMateriaPrimaController::class, 'syncEnvios'])->middleware('permission:recepcionar materia prima')->name('recepcion-materia-prima.sync-envios');

    // Proveedores
    Route::resource('proveedores', ProveedorWebController::class, ['names' => [
        'index' => 'proveedores.web.index',
        'create' => 'proveedores.web.create',
        'store' => 'proveedores.web.store',
        'show' => 'proveedores.web.show',
        'edit' => 'proveedores.web.edit',
        'update' => 'proveedores.web.update',
        'destroy' => 'proveedores.web.destroy',
    ]])->middleware('permission:gestionar proveedores');

    // Gestión de Lotes
    Route::middleware('permission:gestionar lotes')->group(function () {
        Route::get('/gestion-lotes', [GestionLotesController::class, 'index'])->name('gestion-lotes');
        Route::post('/gestion-lotes', [GestionLotesController::class, 'store']);
        Route::get('/gestion-lotes/{id}', [GestionLotesController::class, 'show'])->name('gestion-lotes.show');
        Route::get('/gestion-lotes/{id}/edit', [GestionLotesController::class, 'edit'])->name('gestion-lotes.edit');
        Route::put('/gestion-lotes/{id}', [GestionLotesController::class, 'update'])->name('gestion-lotes.update');
    });

    // Máquinas
    Route::resource('maquinas', MaquinaWebController::class, ['names' => [
        'index' => 'maquinas.index',
        'create' => 'maquinas.create',
        'store' => 'maquinas.store',
        'show' => 'maquinas.show',
        'edit' => 'maquinas.edit',
        'update' => 'maquinas.update',
        'destroy' => 'maquinas.destroy',
    ]])->middleware('permission:gestionar maquinas');

    // Procesos
    Route::resource('procesos', ProcesoWebController::class, ['names' => [
        'index' => 'procesos.index',
        'create' => 'procesos.create',
        'store' => 'procesos.store',
        'show' => 'procesos.show',
        'edit' => 'procesos.edit',
        'update' => 'procesos.update',
        'destroy' => 'procesos.destroy',
    ]])->middleware('permission:gestionar procesos');

    // Variables Estándar
    Route::middleware('permission:gestionar variables estandar')->group(function () {
        Route::get('/variables-estandar', [VariablesEstandarController::class, 'index'])->name('variables-estandar');
        Route::post('/variables-estandar', [VariablesEstandarController::class, 'store']);
        Route::get('/variables-estandar/{id}', [VariablesEstandarController::class, 'show'])->name('variables-estandar.show');
        Route::put('/variables-estandar/{id}', [VariablesEstandarController::class, 'update'])->name('variables-estandar.update');
        Route::delete('/variables-estandar/{id}', [VariablesEstandarController::class, 'destroy'])->name('variables-estandar.destroy');
    });

    // Proceso de Transformación
    Route::get('/proceso/{batchId}', [ProcesoTransformacionController::class, 'index'])->middleware('permission:gestionar procesos')->name('proceso-transformacion');
    Route::post('/proceso/{batchId}/asignar', [ProcesoTransformacionController::class, 'asignarProceso'])->middleware('permission:gestionar procesos')->name('proceso-transformacion.asignar');
    Route::get('/proceso/{batchId}/maquina/{processMachineId}', [ProcesoTransformacionController::class, 'mostrarFormulario'])->middleware('permission:gestionar procesos')->name('proceso-transformacion.mostrar-formulario');
    Route::post('/proceso/{batchId}/maquina/{processMachineId}', [ProcesoTransformacionController::class, 'registrarFormulario'])->middleware('permission:gestionar procesos')->name('proceso-transformacion.registrar');
    Route::get('/proceso/{batchId}/maquina/{processMachineId}/formulario', [ProcesoTransformacionController::class, 'obtenerFormulario'])->middleware('permission:gestionar procesos')->name('proceso-transformacion.formulario');
    Route::get('/proceso/{processId}/maquinas', [ProcesoTransformacionController::class, 'obtenerMaquinasProceso'])->middleware('permission:gestionar procesos')->name('proceso-transformacion.maquinas');

    // Certificación
    Route::get('/certificar-lote', [CertificarLoteController::class, 'index'])->middleware('permission:certificar lotes')->name('certificar-lote');
    Route::post('/certificar-lote/{batchId}', [CertificarLoteController::class, 'finalizar'])->middleware('permission:certificar lotes')->name('certificar-lote.finalizar');
    Route::get('/certificar-lote/{batchId}/log', [CertificarLoteController::class, 'obtenerLog'])->middleware('permission:certificar lotes')->name('certificar-lote.log');
    
    Route::get('/certificados', [CertificadosController::class, 'index'])->middleware('permission:ver certificados')->name('certificados');
    Route::get('/certificado/{id}', [CertificadosController::class, 'show'])->middleware('permission:ver certificados')->name('certificado.show');
    Route::get('/certificado/{id}/qr', [CertificadosController::class, 'qr'])->middleware('permission:ver certificados')->name('certificado.qr');

    // Almacenaje
    Route::get('/almacenaje', [AlmacenajeController::class, 'index'])->middleware('permission:almacenar lotes')->name('almacenaje');
    Route::post('/almacenaje', [AlmacenajeController::class, 'almacenar'])->middleware('permission:almacenar lotes')->name('almacenaje.store');
    Route::get('/almacenaje/lote/{batchId}', [AlmacenajeController::class, 'obtenerAlmacenajesPorLote'])->middleware('permission:almacenar lotes')->name('almacenaje.por-lote');
    
    Route::get('/lotes-almacenados', [LotesAlmacenadosController::class, 'index'])->middleware('permission:almacenar lotes')->name('lotes-almacenados');
    Route::get('/lotes-almacenados/lote/{batchId}', [LotesAlmacenadosController::class, 'obtenerAlmacenajesPorLote'])->middleware('permission:almacenar lotes')->name('lotes-almacenados.por-lote');

    // Pedidos
    Route::middleware('permission:crear pedidos')->group(function () {
        Route::get('/crear-pedido', [PedidosController::class, 'crearPedidoForm'])->name('crear-pedido');
        Route::post('/mis-pedidos', [PedidosController::class, 'crearPedido'])->name('mis-pedidos.store');
    });
    
    Route::middleware('permission:ver mis pedidos')->group(function () {
        Route::get('/mis-pedidos', [PedidosController::class, 'misPedidos'])->name('mis-pedidos');
        Route::get('/mis-pedidos/{id}', [PedidosController::class, 'show'])->name('mis-pedidos.show');
        Route::get('/mis-pedidos/{id}/edit', [PedidosController::class, 'edit'])->name('mis-pedidos.edit');
        Route::put('/mis-pedidos/{id}', [PedidosController::class, 'update'])->name('mis-pedidos.update');
        Route::post('/mis-pedidos/{id}/cancel', [PedidosController::class, 'cancel'])->middleware('permission:cancelar mis pedidos')->name('mis-pedidos.cancel');
    });
    
    Route::middleware('permission:gestionar pedidos')->group(function () {
        Route::get('/gestion-pedidos', [GestionPedidosController::class, 'index'])->name('gestion-pedidos');
        Route::get('/gestion-pedidos/{id}', [GestionPedidosController::class, 'show'])->name('gestion-pedidos.show');
        Route::put('/gestion-pedidos/{id}', [GestionPedidosController::class, 'update'])->name('gestion-pedidos.update');
        Route::post('/gestion-pedidos/{orderId}/approve', [GestionPedidosController::class, 'approveOrder'])->middleware('permission:aprobar pedidos')->name('gestion-pedidos.approve-order');
        Route::post('/gestion-pedidos/{orderId}/reject', [GestionPedidosController::class, 'rejectOrder'])->middleware('permission:rechazar pedidos')->name('gestion-pedidos.reject-order');
        
        // Documentación de Pedidos
        Route::get('/documentacion-pedidos', [DocumentacionPedidosController::class, 'index'])->name('documentacion-pedidos');
        Route::get('/documentacion-pedidos/{pedido}', [DocumentacionPedidosController::class, 'show'])->name('documentacion-pedidos.show');
        Route::get('/documentacion-pedidos/{pedido}/descargar/{tipo}', [DocumentacionPedidosController::class, 'descargarDocumento'])->name('documentacion-pedidos.descargar');
    });

    // Configuración de Ubicación de la Planta
    Route::get('/planta-ubicacion', [PlantaUbicacionController::class, 'index'])->middleware('permission:gestionar usuarios')->name('planta-ubicacion');
    Route::put('/planta-ubicacion', [PlantaUbicacionController::class, 'update'])->middleware('permission:gestionar usuarios')->name('planta-ubicacion.update');

    // Rutas en Tiempo Real
    Route::get('/rutas-tiempo-real', [RutaTiempoRealController::class, 'index'])->name('rutas-tiempo-real');
    
    // Usuarios/Operadores
    Route::get('/usuarios', [UsuariosController::class, 'index'])->middleware('permission:gestionar usuarios')->name('usuarios');
    Route::post('/usuarios', [UsuariosController::class, 'store'])->middleware('permission:gestionar usuarios');
    Route::get('/usuarios/{id}/edit', [UsuariosController::class, 'edit'])->middleware('permission:gestionar usuarios')->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->middleware('permission:gestionar usuarios')->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->middleware('permission:gestionar usuarios')->name('usuarios.destroy');

    // Operadores (CRUD completo)
    Route::resource('operadores', OperadorWebController::class, ['names' => [
        'index' => 'operadores.web.index',
        'create' => 'operadores.web.create',
        'store' => 'operadores.web.store',
        'show' => 'operadores.web.show',
        'edit' => 'operadores.web.edit',
        'update' => 'operadores.web.update',
        'destroy' => 'operadores.web.destroy',
    ]])->middleware('permission:gestionar usuarios');

    // Carga de imágenes
    Route::post('/upload-image', [\App\Http\Controllers\Web\ImageUploadController::class, 'upload'])->name('upload-image');
    Route::delete('/delete-image', [\App\Http\Controllers\Web\ImageUploadController::class, 'delete'])->name('delete-image');
    
    // Helpdesk - Centro de Soporte
    Route::get('/helpdesk', function () {
        return view('helpdesk');
    })->name('helpdesk');
});
