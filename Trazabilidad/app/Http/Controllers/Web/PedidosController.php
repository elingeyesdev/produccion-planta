<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\OrderDestinationProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\AlmacenSyncService;

class PedidosController extends Controller
{
    public function misPedidos()
    {
        $user = Auth::user();

        // Verificar si el usuario es admin (tiene permiso de gestionar pedidos o tiene rol admin/administrador)
        $esAdmin = false;
        if ($user) {
            $esAdmin = $user->hasPermissionTo('gestionar pedidos') || 
                       $user->hasRole('admin') || 
                       $user->hasRole('administrador');
        }

        // Si es admin, mostrar todos los pedidos
        if ($esAdmin) {
            $pedidos = CustomerOrder::with([
                    'customer', 
                    'orderProducts.product', 
                    'batches.latestFinalEvaluation',
                    'batches.processMachineRecords',
                    'batches.storage'
                ])
                ->orderBy('fecha_creacion', 'desc')
                ->paginate(15);
            
            // Calcular estado real para cada pedido basándose en sus lotes
            $pedidos->getCollection()->transform(function($pedido) {
                $estadoReal = $this->calcularEstadoRealPedido($pedido);
                $pedido->estado_real = $estadoReal;
                return $pedido;
            });
        } else {
            // Para usuarios no admin, mantener la lógica original
            // Buscar customer relacionado con el operador
            $customerId = $user->cliente_id ?? null;
            $customer = null;

            if (!$customerId) {
                // Buscar por email
                $customer = Customer::where('email', $user->email)->first();
                $customerId = $customer ? $customer->cliente_id : null;
            }

            // Si no se encontró un cliente, crear uno automáticamente para este usuario
            if (!$customerId) {
                try {
                    // Sincronizar secuencia de customer si es necesario
                    $maxCustomerId = Customer::max('cliente_id') ?? 0;
                    try {
                        $seqResult = DB::selectOne("SELECT last_value FROM cliente_seq");
                        $seqValue = $seqResult->last_value ?? 0;
                    } catch (\Exception $e) {
                        $seqValue = 0;
                    }

                    if ($seqValue < $maxCustomerId) {
                        DB::statement("SELECT setval('cliente_seq', $maxCustomerId, true)");
                    }

                    // Obtener el siguiente ID de la secuencia
                    $nextId = DB::selectOne("SELECT nextval('cliente_seq') as id")->id;

                    // Crear un Customer automáticamente para este operador
                    $customer = Customer::create([
                        'cliente_id' => $nextId,
                        'razon_social' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: 'Cliente ' . $user->usuario,
                        'nombre_comercial' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: 'Cliente ' . $user->usuario,
                        'email' => $user->email ?? null,
                        'contacto' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: $user->usuario,
                        'activo' => true,
                    ]);

                    $customerId = $customer->cliente_id;
                } catch (\Exception $e) {
                    // Si falla, usar el primer cliente activo como fallback
                    $customer = Customer::where('activo', true)->first();
                    $customerId = $customer ? $customer->cliente_id : null;
                }
            }

            // Si aún no hay customerId, mostrar pedidos vacíos
            if (!$customerId) {
                $pedidos = CustomerOrder::whereRaw('1 = 0')->paginate(15);
            } else {
                $pedidos = CustomerOrder::where('cliente_id', $customerId)
                    ->with([
                        'customer', 
                        'orderProducts.product', 
                        'batches.latestFinalEvaluation',
                        'batches.processMachineRecords',
                        'batches.storage'
                    ])
                    ->orderBy('fecha_creacion', 'desc')
                    ->paginate(15);
                
                // Calcular estado real para cada pedido basándose en sus lotes
                $pedidos->getCollection()->transform(function($pedido) {
                    $estadoReal = $this->calcularEstadoRealPedido($pedido);
                    $pedido->estado_real = $estadoReal;
                    return $pedido;
                });
            }
        }

        // Calcular estadísticas basadas en el estado real
        $pedidosCollection = $pedidos->getCollection();
        $stats = [
            'total' => $pedidos->total(),
            'pendientes' => $pedidosCollection->where('estado_real', 'pendiente')->count(),
            'en_proceso' => $pedidosCollection->where('estado_real', 'en_proceso')->count(),
            'completados' => $pedidosCollection->where('estado_real', 'completado')->count(),
        ];

        return view('mis-pedidos', compact('pedidos', 'stats'));
    }

    public function crearPedidoForm()
    {
        $products = Product::where('activo', true)
            ->with('unit')
            ->orderBy('nombre')
            ->get();

        // Obtener almacenes destino desde plantaCruds usando el servicio
        $almacenSyncService = new AlmacenSyncService();
        
        // Limpiar cache primero para asegurar datos frescos
        $almacenSyncService->clearCache();
        
        $almacenesDestino = $almacenSyncService->getDestinoAlmacenes();
        
        // Si no hay almacenes, loggear el error para debugging
        if (empty($almacenesDestino)) {
            $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
            \Log::warning('No se pudieron obtener almacenes destino desde plantaCruds', [
                'api_url' => $apiUrl,
                'count' => 0,
                'full_url' => rtrim($apiUrl, '/') . '/almacenes'
            ]);
            
            // Intentar una vez más sin cache
            try {
                $almacenesDestino = $almacenSyncService->getAlmacenes(true);
                $almacenesDestino = array_filter($almacenesDestino, function($alm) {
                    return !($alm['es_planta'] ?? false);
                });
                $almacenesDestino = array_values($almacenesDestino); // Reindexar
            } catch (\Exception $e) {
                \Log::error('Error al obtener almacenes en segundo intento', [
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            \Log::info('Almacenes destino obtenidos exitosamente', [
                'count' => count($almacenesDestino)
            ]);
        }
        
        return view('crear-pedido', compact('products', 'almacenesDestino'));
    }

    public function crearPedido(Request $request)
    {
        $user = Auth::user();

        // Obtener customer_id del usuario
        $customerId = $this->getOrCreateCustomerId($user);

        if (!$customerId) {
            return redirect()->back()
                ->with('error', 'No se pudo asociar un cliente a tu cuenta')
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'delivery_date' => 'nullable|date|after:today',
            'description' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:producto,producto_id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.observations' => 'nullable|string',
            'destinations' => 'required|array|min:1',
            'destinations.*.almacen_destino_id' => 'nullable|integer',
            'destinations.*.address' => 'required|string|max:500',
            'destinations.*.latitude' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitude' => 'nullable|numeric|between:-180,180',
            'destinations.*.reference' => 'nullable|string|max:200',
            'destinations.*.contact_name' => 'nullable|string|max:200',
            'destinations.*.contact_phone' => 'nullable|string|max:20',
            'destinations.*.delivery_instructions' => 'nullable|string',
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.quantity' => 'required|integer|min:1',
            'almacen_id' => 'nullable|integer',
        ], [
            'name.required' => 'El nombre del pedido es obligatorio.',
            'products.required' => 'Debe agregar al menos un producto al pedido.',
            'products.min' => 'Debe agregar al menos un producto al pedido.',
            'products.*.product_id.required' => 'Debe seleccionar un producto.',
            'products.*.product_id.exists' => 'El producto seleccionado no existe.',
            'products.*.quantity.required' => 'Debe especificar la cantidad del producto.',
            'products.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'destinations.required' => 'Debe agregar al menos un destino para el pedido.',
            'destinations.min' => 'Debe agregar al menos un destino para el pedido.',
            'destinations.*.address.required' => 'La dirección del destino es obligatoria. Por favor, ingrese la dirección o seleccione una ubicación en el mapa.',
            'destinations.*.address.max' => 'La dirección no puede exceder los 500 caracteres.',
            'destinations.*.products.required' => 'Debe asignar al menos un producto a este destino.',
            'destinations.*.products.min' => 'Debe asignar al menos un producto a este destino.',
            'destinations.*.products.*.order_product_index.required' => 'Debe seleccionar un producto válido.',
            'destinations.*.products.*.quantity.required' => 'Debe especificar la cantidad para este producto.',
            'destinations.*.products.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Obtener el siguiente ID
            $maxId = CustomerOrder::max('pedido_id') ?? 0;
            $nextId = $maxId + 1;

            // Generar número de pedido
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');

            // Calcular fecha límite de edición (por defecto 24 horas)
            $editableUntil = now()->addHours(24);

            $order = CustomerOrder::create([
                'pedido_id' => $nextId,
                'cliente_id' => $customerId,
                'numero_pedido' => $orderNumber,
                'nombre' => $request->name,
                'estado' => 'pendiente',
                'fecha_creacion' => now()->toDateString(),
                'fecha_entrega' => $request->delivery_date,
                'descripcion' => $request->description,
                'editable_hasta' => $editableUntil,
            ]);

            // Crear productos del pedido
            $orderProducts = [];
            $maxOrderProductId = OrderProduct::max('producto_pedido_id') ?? 0;

            foreach ($request->products as $index => $productData) {
                $orderProductId = $maxOrderProductId + $index + 1;

                // Obtener el producto para calcular el precio
                $product = Product::find($productData['product_id']);
                $precioUnitario = $product->precio_unitario ?? 0;
                $cantidad = $productData['quantity'];
                $precioTotal = $precioUnitario * $cantidad;

                $orderProduct = OrderProduct::create([
                    'producto_pedido_id' => $orderProductId,
                    'pedido_id' => $order->pedido_id,
                    'producto_id' => $productData['product_id'],
                    'cantidad' => $cantidad,
                    'precio' => $precioTotal,
                    'estado' => 'pendiente',
                    'observaciones' => $productData['observations'] ?? null,
                ]);

                $orderProducts[] = $orderProduct;
                $maxOrderProductId = $orderProductId; // Actualizar para el siguiente
            }

            // Crear destinos y asignar productos
            $maxDestinationId = OrderDestination::max('destino_id') ?? 0;

            // Si el usuario seleccionó un almacen para el pedido, intentar resolver su nombre
            $almacenName = null;
            $selectedAlmacenId = $request->input('almacen_id');
            if (!empty($selectedAlmacenId)) {
                try {
                    $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
                    $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                    if ($resp->successful()) {
                        $almacenes = $resp->json('data', []);
                        foreach ($almacenes as $alm) {
                            if (isset($alm['id']) && $alm['id'] == $selectedAlmacenId) {
                                $almacenName = $alm['nombre'] ?? ($alm['nombre_comercial'] ?? null);
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('No se pudo resolver nombre de almacen seleccionado: ' . $e->getMessage());
                }
            }
            foreach ($request->destinations as $destIndex => $destData) {
                $destinationId = $maxDestinationId + $destIndex + 1;

                // Resolver nombre del almacén destino si se proporcionó
                $almacenDestinoNombre = null;
                $almacenDestinoId = $destData['almacen_destino_id'] ?? null;
                if (!empty($almacenDestinoId)) {
                    try {
                        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
                        $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                        if ($resp->successful()) {
                            foreach ($resp->json('data', []) as $alm) {
                                if (isset($alm['id']) && $alm['id'] == $almacenDestinoId) {
                                    $almacenDestinoNombre = $alm['nombre'] ?? null;
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo resolver nombre de almacen destino: ' . $e->getMessage());
                    }
                }

                $destination = OrderDestination::create([
                    'destino_id' => $destinationId,
                    'pedido_id' => $order->pedido_id,
                    'direccion' => $destData['address'] ?? $almacenDestinoNombre ?? 'Sin dirección',
                    'latitud' => $destData['latitude'] ?? null,
                    'longitud' => $destData['longitude'] ?? null,
                    'referencia' => $destData['reference'] ?? null,
                    'nombre_contacto' => $destData['contact_name'] ?? null,
                    'telefono_contacto' => $destData['contact_phone'] ?? null,
                    'instrucciones_entrega' => $destData['delivery_instructions'] ?? null,
                    'almacen_destino_id' => $almacenDestinoId,
                    'almacen_destino_nombre' => $almacenDestinoNombre,
                ]);

                // Asignar productos a este destino
                $maxDestProdId = OrderDestinationProduct::max('producto_destino_id') ?? 0;
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $destProdId = $maxDestProdId + $destProdIndex + 1;
                        $maxDestProdId = $destProdId; // Actualizar para el siguiente

                        OrderDestinationProduct::create([
                            'producto_destino_id' => $destProdId,
                            'destino_id' => $destination->destino_id,
                            'producto_pedido_id' => $orderProducts[$orderProductIndex]->producto_pedido_id,
                            'cantidad' => $destProdData['quantity'],
                            'observaciones' => $destProdData['observations'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear pedido: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Error al crear pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Calcula el estado real de un pedido basándose en sus lotes asociados
     */
    private function calcularEstadoRealPedido($pedido)
    {
        $tieneLotes = $pedido->batches && $pedido->batches->isNotEmpty();
        
        if (!$tieneLotes) {
            // Si no tiene lotes, el estado depende del estado del pedido
            if ($pedido->estado === 'cancelado' || $pedido->estado === 'rechazado') {
                return $pedido->estado;
            }
            return 'pendiente';
        }
        
        // Verificar si algún lote está almacenado
        $loteAlmacenado = $pedido->batches->some(function($batch) {
            return $batch->storage && $batch->storage->isNotEmpty();
        });
        
        if ($loteAlmacenado) {
            return 'completado';
        }
        
        // Verificar si algún lote está certificado
        $loteCertificado = $pedido->batches->some(function($batch) {
            $eval = $batch->latestFinalEvaluation;
            return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
        });
        
        if ($loteCertificado) {
            return 'completado';
        }
        
        // Verificar si algún lote está en proceso
        $loteEnProceso = $pedido->batches->some(function($batch) {
            return $batch->processMachineRecords && $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
        });
        
        if ($loteEnProceso) {
            return 'en_proceso';
        }
        
        // Si tiene lotes pero no están en proceso, está aprobado
        if ($pedido->estado === 'aprobado') {
            return 'aprobado';
        }
        
        // Si tiene lotes creados pero no están en proceso aún
        return 'aprobado';
    }

    private function getOrCreateCustomerId($user)
    {
        $customerId = $user->cliente_id ?? null;
        $customer = null;

        if (!$customerId) {
            $customer = Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->cliente_id : null;
        }

        if (!$customerId) {
            try {
                $maxCustomerId = Customer::max('cliente_id') ?? 0;
                $nextId = $maxCustomerId + 1;

                $customer = Customer::create([
                    'cliente_id' => $nextId,
                    'razon_social' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: 'Cliente ' . $user->usuario,
                    'nombre_comercial' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: 'Cliente ' . $user->usuario,
                    'email' => $user->email ?? null,
                    'contacto' => trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) ?: $user->usuario,
                    'activo' => true,
                ]);

                $customerId = $customer->cliente_id;
            } catch (\Exception $e) {
                $customer = Customer::where('activo', true)->first();
                $customerId = $customer ? $customer->cliente_id : null;
            }
        }

        return $customerId;
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // Verificar si el usuario es admin
        $esAdmin = false;
        if ($user) {
            $esAdmin = $user->hasPermissionTo('gestionar pedidos') || 
                       $user->hasRole('admin') || 
                       $user->hasRole('administrador');
        }

        $pedido = CustomerOrder::with([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product',
            'approver',
            'batches'
        ])->findOrFail($id);

        // Si no es admin, verificar que el pedido pertenece al cliente del usuario
        if (!$esAdmin) {
            $customerId = $this->getOrCreateCustomerId($user);
            if ($pedido->cliente_id != $customerId) {
                abort(403, 'No tienes permiso para ver este pedido');
            }
        }

        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            // Obtener nombre del almacén si el pedido viene del sistema de almacenes
            $almacenNombre = null;
            if ($pedido->origen_sistema === 'almacen' && $pedido->pedido_almacen_id) {
                // Intentar obtener el nombre del almacén desde sistema-almacen-PSIII
                try {
                    $almacenApiUrl = env('ALMACEN_API_URL', 'http://localhost:8002/api');
                    $response = Http::timeout(5)->get("{$almacenApiUrl}/almacenes/{$pedido->pedido_almacen_id}");
                    if ($response->successful()) {
                        $almacenData = $response->json('data', []);
                        $almacenNombre = $almacenData['nombre'] ?? null;
                    }
                } catch (\Exception $e) {
                    \Log::warning('No se pudo obtener nombre del almacén: ' . $e->getMessage());
                }
                
                // Si no se pudo obtener, intentar desde el primer destino
                if (!$almacenNombre && $pedido->destinations->isNotEmpty()) {
                    $firstDest = $pedido->destinations->first();
                    // Extraer nombre del almacén desde las instrucciones de entrega
                    if ($firstDest->instrucciones_entrega) {
                        if (preg_match('/Entrega en almacén:\s*(.+)/i', $firstDest->instrucciones_entrega, $matches)) {
                            $almacenNombre = trim($matches[1]);
                        }
                    }
                    // Si no, usar la dirección como nombre
                    if (!$almacenNombre) {
                        $almacenNombre = $firstDest->direccion;
                    }
                }
            }
            
            return response()->json([
                'order_id' => $pedido->pedido_id,
                'order_number' => $pedido->numero_pedido ?? 'PED-' . $pedido->pedido_id,
                'name' => $pedido->nombre ?? 'Sin nombre',
                'description' => $pedido->descripcion ?? 'Sin descripción',
                'status' => $pedido->estado,
                'creation_date' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('d/m/Y') : 'N/A',
                'delivery_date' => $pedido->fecha_entrega ? $pedido->fecha_entrega->format('d/m/Y') : null,
                'observations' => $pedido->observaciones,
                'editable_until' => $pedido->editable_hasta ? $pedido->editable_hasta->format('Y-m-d H:i:s') : null,
                'approved_at' => $pedido->aprobado_en ? $pedido->aprobado_en->format('Y-m-d H:i:s') : null,
                'rejection_reason' => $pedido->razon_rechazo,
                'can_be_edited' => $pedido->canBeEdited(),
                'almacen_nombre' => $almacenNombre, // Nombre del almacén si viene del sistema de almacenes
                'products' => $pedido->orderProducts->map(function ($op) {
                    return [
                        'order_product_id' => $op->producto_pedido_id,
                        'product_id' => $op->producto_id,
                        'product_name' => $op->product->nombre ?? 'Producto sin nombre',
                        'quantity' => number_format($op->cantidad, 2),
                        'unit' => $op->product->unit->codigo ?? $op->product->unit->nombre ?? 'N/A',
                        'status' => $op->estado,
                        'observations' => $op->observaciones,
                        'rejection_reason' => $op->razon_rechazo,
                    ];
                })->toArray(),
                'destinations' => $pedido->destinations->map(function ($dest) {
                    return [
                        'address' => $dest->direccion,
                        'reference' => $dest->referencia,
                        'contact_name' => $dest->nombre_contacto,
                        'contact_phone' => $dest->telefono_contacto,
                        'delivery_instructions' => $dest->instrucciones_entrega,
                    ];
                })->toArray(),
            ]);
        }

        return view('mis-pedidos-detalle', compact('pedido'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $customerId = $this->getOrCreateCustomerId($user);

        $pedido = CustomerOrder::with([
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product'
        ])->findOrFail($id);

        // Verificar que el pedido pertenece al cliente del usuario
        if ($pedido->cliente_id != $customerId) {
            abort(403, 'No tienes permiso para editar este pedido');
        }

        // Verificar si el pedido puede ser editado
        if (!$pedido->canBeEdited()) {
            return redirect()->route('mis-pedidos')
                ->with('error', 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.');
        }

        $products = Product::where('activo', true)
            ->with('unit')
            ->orderBy('nombre')
            ->get();

        // Obtener almacenes destino
        $almacenesDestino = [];
        try {
            $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
            $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
            if ($resp->successful()) {
                foreach ($resp->json('data', []) as $alm) {
                    // Solo almacenes destino (no plantas)
                    if (empty($alm['es_planta']) || !$alm['es_planta']) {
                        $almacenesDestino[] = $alm;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('No se pudieron obtener almacenes de plantaCruds en edit: ' . $e->getMessage());
        }

        return view('editar-pedido', compact('pedido', 'products', 'almacenesDestino'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $customerId = $this->getOrCreateCustomerId($user);

        $pedido = CustomerOrder::findOrFail($id);

        // Verificar que el pedido pertenece al cliente del usuario
        if ($pedido->cliente_id != $customerId) {
            abort(403, 'No tienes permiso para editar este pedido');
        }

        // Verificar si el pedido puede ser editado
        if (!$pedido->canBeEdited()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.')
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:200',
            'fecha_entrega' => 'nullable|date|after:today',
            'descripcion' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:producto,producto_id',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.observations' => 'nullable|string',
            'destinations' => 'required|array|min:1',
            'destinations.*.almacen_destino_id' => 'nullable|integer',
            'destinations.*.address' => 'required|string|max:500',
            'destinations.*.latitude' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitude' => 'nullable|numeric|between:-180,180',
            'destinations.*.reference' => 'nullable|string|max:200',
            'destinations.*.contact_name' => 'nullable|string|max:200',
            'destinations.*.contact_phone' => 'nullable|string|max:20',
            'destinations.*.delivery_instructions' => 'nullable|string',
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.quantity' => 'required|numeric|min:0.0001',
        ], [
            'nombre.required' => 'El nombre del pedido es obligatorio.',
            'products.required' => 'Debe agregar al menos un producto al pedido.',
            'products.min' => 'Debe agregar al menos un producto al pedido.',
            'products.*.product_id.required' => 'Debe seleccionar un producto.',
            'products.*.product_id.exists' => 'El producto seleccionado no existe.',
            'products.*.quantity.required' => 'Debe especificar la cantidad del producto.',
            'products.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'destinations.required' => 'Debe agregar al menos un destino para el pedido.',
            'destinations.min' => 'Debe agregar al menos un destino para el pedido.',
            'destinations.*.address.required' => 'La dirección del destino es obligatoria. Por favor, ingrese la dirección o seleccione una ubicación en el mapa.',
            'destinations.*.address.max' => 'La dirección no puede exceder los 500 caracteres.',
            'destinations.*.products.required' => 'Debe asignar al menos un producto a este destino.',
            'destinations.*.products.min' => 'Debe asignar al menos un producto a este destino.',
            'destinations.*.products.*.order_product_index.required' => 'Debe seleccionar un producto válido.',
            'destinations.*.products.*.quantity.required' => 'Debe especificar la cantidad para este producto.',
            'destinations.*.products.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Actualizar información básica del pedido
            $pedido->update([
                'nombre' => $request->nombre,
                'fecha_entrega' => $request->fecha_entrega,
                'descripcion' => $request->descripcion,
            ]);

            // Eliminar productos y destinos existentes
            $pedido->orderProducts()->delete();
            $pedido->destinations()->delete();

            // Crear nuevos productos del pedido
            $orderProducts = [];
            $maxOrderProductId = OrderProduct::max('producto_pedido_id') ?? 0;

            foreach ($request->products as $index => $productData) {
                $orderProductId = $maxOrderProductId + $index + 1;

                // Obtener el producto para calcular el precio
                $product = Product::find($productData['product_id']);
                $precioUnitario = $product->precio_unitario ?? 0;
                $cantidad = $productData['quantity'];
                $precioTotal = $precioUnitario * $cantidad;

                $orderProduct = OrderProduct::create([
                    'producto_pedido_id' => $orderProductId,
                    'pedido_id' => $pedido->pedido_id,
                    'producto_id' => $productData['product_id'],
                    'cantidad' => $cantidad,
                    'precio' => $precioTotal,
                    'estado' => 'pendiente',
                    'observaciones' => $productData['observations'] ?? null,
                ]);

                $orderProducts[] = $orderProduct;
                $maxOrderProductId = $orderProductId;
            }

            // Crear destinos y asignar productos
            $maxDestinationId = OrderDestination::max('destino_id') ?? 0;

            // Resolver nombre de almacen si se proporcionó en la petición
            $almacenName = null;
            $selectedAlmacenId = $request->input('almacen_id');
            if (!empty($selectedAlmacenId)) {
                try {
                    $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
                    $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                    if ($resp->successful()) {
                        $almacenes = $resp->json('data', []);
                        foreach ($almacenes as $alm) {
                            if (isset($alm['id']) && $alm['id'] == $selectedAlmacenId) {
                                $almacenName = $alm['nombre'] ?? ($alm['nombre_comercial'] ?? null);
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('No se pudo resolver nombre de almacen seleccionado (update): ' . $e->getMessage());
                }
            }
            foreach ($request->destinations as $destIndex => $destData) {
                $destinationId = $maxDestinationId + $destIndex + 1;

                // Resolver nombre del almacén destino si se proporcionó
                $almacenDestinoNombre = null;
                $almacenDestinoId = $destData['almacen_destino_id'] ?? null;
                if (!empty($almacenDestinoId)) {
                    try {
                        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
                        $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                        if ($resp->successful()) {
                            foreach ($resp->json('data', []) as $alm) {
                                if (isset($alm['id']) && $alm['id'] == $almacenDestinoId) {
                                    $almacenDestinoNombre = $alm['nombre'] ?? null;
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo resolver nombre de almacen destino (update): ' . $e->getMessage());
                    }
                }

                $destination = OrderDestination::create([
                    'destino_id' => $destinationId,
                    'pedido_id' => $pedido->pedido_id,
                    'direccion' => $destData['address'] ?? $almacenDestinoNombre ?? 'Sin dirección',
                    'latitud' => $destData['latitude'] ?? null,
                    'longitud' => $destData['longitude'] ?? null,
                    'referencia' => $destData['reference'] ?? null,
                    'nombre_contacto' => $destData['contact_name'] ?? null,
                    'telefono_contacto' => $destData['contact_phone'] ?? null,
                    'instrucciones_entrega' => $destData['delivery_instructions'] ?? null,
                    'almacen_destino_id' => $almacenDestinoId,
                    'almacen_destino_nombre' => $almacenDestinoNombre,
                ]);

                // Asignar productos a este destino
                $maxDestProdId = OrderDestinationProduct::max('producto_destino_id') ?? 0;
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $destProdId = $maxDestProdId + $destProdIndex + 1;
                        $maxDestProdId = $destProdId;

                        OrderDestinationProduct::create([
                            'producto_destino_id' => $destProdId,
                            'destino_id' => $destination->destino_id,
                            'producto_pedido_id' => $orderProducts[$orderProductIndex]->producto_pedido_id,
                            'cantidad' => $destProdData['quantity'],
                            'observaciones' => $destProdData['observations'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido actualizado exitosamente'
                ]);
            }

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar pedido: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar pedido: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function cancel($id)
    {
        try {
            $user = Auth::user();
            $customerId = $this->getOrCreateCustomerId($user);

            $order = CustomerOrder::findOrFail($id);

            // Verificar que el pedido pertenece al cliente del usuario
            if ($order->cliente_id != $customerId) {
                abort(403, 'No tienes permiso para cancelar este pedido');
            }

            // Verificar si el pedido puede ser cancelado
            if (!$order->canBeEdited()) {
                return redirect()->back()
                    ->with('error', 'El pedido no puede ser cancelado. Ya fue aprobado o expiró el tiempo de edición.');
            }

            $order->update([
                'status' => 'cancelado'
            ]);

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido cancelado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cancelar pedido: ' . $e->getMessage());
        }
    }
}
