<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\OrderDestinationProduct;
use App\Models\Customer;
use App\Models\Operator;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CustomerOrderResource;

class CustomerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $orders = CustomerOrder::with(['customer', 'orderProducts'])
                ->orderBy('fecha_creacion', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json(CustomerOrderResource::collection($orders)->response()->getData());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $order = CustomerOrder::with([
                'customer',
                'batches',
                'orderProducts.product.unit',
                'destinations.destinationProducts.orderProduct.product',
                'approver'
            ])->findOrFail($id);

            // Transformar para asegurar estructura consistente con frontend
            $data = $order->toArray();
            $data['orderProducts'] = $order->orderProducts->map(function($op) {
                return [
                    'producto_pedido_id' => $op->producto_pedido_id,
                    'producto_id' => $op->producto_id,
                    'cantidad' => $op->cantidad,
                    'estado' => $op->estado,
                    'observaciones' => $op->observaciones,
                    'razon_rechazo' => $op->razon_rechazo,
                    'product' => [
                        'producto_id' => $op->product->producto_id,
                        'nombre' => $op->product->nombre ?? 'N/A',
                        'codigo' => $op->product->codigo ?? 'N/A',
                        'unit' => [
                            'nombre' => $op->product->unit->nombre ?? 'N/A',
                            'abbreviation' => $op->product->unit->codigo ?? 'N/A',
                        ]
                    ]
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pedido no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtener información completa de un pedido (endpoint público)
     * Incluye toda la información necesaria para crear y procesar un pedido
     * 
     * @param int $id ID del pedido
     * @return JsonResponse
     */
    public function getCompleteOrder($id): JsonResponse
    {
        try {
            $order = CustomerOrder::with([
                'customer',
                'orderProducts.product.unit',
                'orderProducts.approver',
                'destinations.destinationProducts.orderProduct.product.unit',
                'approver'
            ])->findOrFail($id);

            $orderData = $this->formatCompleteOrderData($order);

            return response()->json($orderData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del pedido',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtener información completa de todos los pedidos (endpoint público)
     * Incluye toda la información necesaria para crear y procesar pedidos
     * 
     * @return JsonResponse
     */
    public function getAllCompleteOrders(): JsonResponse
    {
        try {
            $orders = CustomerOrder::with([
                'customer',
                'orderProducts.product.unit',
                'orderProducts.approver',
                'destinations.destinationProducts.orderProduct.product.unit',
                'approver'
            ])->orderBy('fecha_creacion', 'desc')->get();

            $pedidos = $orders->map(function($order) {
                return $this->formatCompleteOrderData($order);
            });

            return response()->json([
                'success' => true,
                'total' => $pedidos->count(),
                'pedidos' => $pedidos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de los pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea los datos completos de un pedido
     * 
     * @param CustomerOrder $order
     * @return array
     */
    private function formatCompleteOrderData($order): array
    {
        // Calcular montos totales
        $subtotal = 0;
        $totalProductos = 0;

        // Procesar productos del pedido
        $products = $order->orderProducts->map(function($op) use (&$subtotal, &$totalProductos) {
            $precio = (float)($op->precio ?? $op->product->precio_unitario ?? 0);
            $cantidad = (float)$op->cantidad;
            $total = $precio * $cantidad;
            
            $subtotal += $total;
            $totalProductos += $cantidad;

            return [
                'producto_pedido_id' => $op->producto_pedido_id,
                'producto_id' => $op->producto_id,
                'cantidad' => (float)$op->cantidad,
                'precio_unitario' => $precio,
                'precio_total' => $total,
                'estado' => $op->estado,
                'razon_rechazo' => $op->razon_rechazo,
                'observaciones' => $op->observaciones,
                'aprobado_por' => $op->aprobado_por,
                'aprobado_en' => $op->aprobado_en ? $op->aprobado_en->toDateTimeString() : null,
                'aprobador' => $op->approver ? [
                    'operador_id' => $op->approver->operador_id,
                    'nombre' => $op->approver->first_name . ' ' . $op->approver->last_name,
                    'username' => $op->approver->username,
                ] : null,
                'producto' => [
                    'producto_id' => $op->product->producto_id,
                    'codigo' => $op->product->codigo,
                    'nombre' => $op->product->nombre,
                    'tipo' => $op->product->tipo,
                    'peso' => (float)($op->product->peso ?? 0),
                    'precio_unitario' => (float)($op->product->precio_unitario ?? 0),
                    'descripcion' => $op->product->descripcion,
                    'activo' => $op->product->activo,
                    'unidad' => $op->product->unit ? [
                        'unidad_id' => $op->product->unit->unidad_id,
                        'codigo' => $op->product->unit->codigo,
                        'nombre' => $op->product->unit->nombre,
                    ] : null,
                ],
            ];
        });

        // Procesar destinos con sus productos
        $destinations = $order->destinations->map(function($destino) {
            $productosDestino = $destino->destinationProducts->map(function($dp) {
                $op = $dp->orderProduct;
                $product = $op->product;
                
                return [
                    'producto_destino_id' => $dp->producto_destino_id,
                    'producto_pedido_id' => $dp->producto_pedido_id,
                    'cantidad' => (float)$dp->cantidad,
                    'observaciones' => $dp->observaciones,
                    'producto_pedido' => [
                        'producto_pedido_id' => $op->producto_pedido_id,
                        'producto_id' => $op->producto_id,
                        'cantidad' => (float)$op->cantidad,
                        'precio' => (float)($op->precio ?? 0),
                        'estado' => $op->estado,
                        'producto' => [
                            'producto_id' => $product->producto_id,
                            'codigo' => $product->codigo,
                            'nombre' => $product->nombre,
                            'tipo' => $product->tipo,
                            'peso' => (float)($product->peso ?? 0),
                            'precio_unitario' => (float)($product->precio_unitario ?? 0),
                            'unidad' => $product->unit ? [
                                'unidad_id' => $product->unit->unidad_id,
                                'codigo' => $product->unit->codigo,
                                'nombre' => $product->unit->nombre,
                            ] : null,
                        ],
                    ],
                ];
            });

            return [
                'destino_id' => $destino->destino_id,
                'direccion' => $destino->direccion,
                'referencia' => $destino->referencia,
                'latitud' => $destino->latitud ? (float)$destino->latitud : null,
                'longitud' => $destino->longitud ? (float)$destino->longitud : null,
                'nombre_contacto' => $destino->nombre_contacto,
                'telefono_contacto' => $destino->telefono_contacto,
                'instrucciones_entrega' => $destino->instrucciones_entrega,
                'almacen_origen_id' => $destino->almacen_origen_id,
                'almacen_origen_nombre' => $destino->almacen_origen_nombre,
                'almacen_destino_id' => $destino->almacen_destino_id,
                'almacen_destino_nombre' => $destino->almacen_destino_nombre,
                'almacen_almacen_id' => $destino->almacen_almacen_id,
                'productos' => $productosDestino,
                'total_productos' => $productosDestino->sum('cantidad'),
            ];
        });

        // Construir respuesta completa
        return [
            'pedido' => [
                'pedido_id' => $order->pedido_id,
                'numero_pedido' => $order->numero_pedido,
                'nombre' => $order->nombre,
                'estado' => $order->estado,
                'fecha_creacion' => $order->fecha_creacion ? $order->fecha_creacion->toDateString() : null,
                'fecha_entrega' => $order->fecha_entrega ? $order->fecha_entrega->toDateString() : null,
                'descripcion' => $order->descripcion,
                'observaciones' => $order->observaciones,
                'editable_hasta' => $order->editable_hasta ? $order->editable_hasta->toDateTimeString() : null,
                'aprobado_en' => $order->aprobado_en ? $order->aprobado_en->toDateTimeString() : null,
                'aprobado_por' => $order->aprobado_por,
                'razon_rechazo' => $order->razon_rechazo,
                'origen_sistema' => $order->origen_sistema,
                'pedido_almacen_id' => $order->pedido_almacen_id,
                'aprobador' => $order->approver ? [
                    'operador_id' => $order->approver->operador_id,
                    'nombre' => $order->approver->first_name . ' ' . $order->approver->last_name,
                    'username' => $order->approver->username,
                ] : null,
            ],
            'cliente' => $order->customer ? [
                'cliente_id' => $order->customer->cliente_id,
                'razon_social' => $order->customer->razon_social,
                'nombre_comercial' => $order->customer->nombre_comercial,
                'nit' => $order->customer->nit,
                'direccion' => $order->customer->direccion,
                'telefono' => $order->customer->telefono,
                'email' => $order->customer->email,
                'contacto' => $order->customer->contacto,
                'activo' => $order->customer->activo,
            ] : null,
            'productos' => $products,
            'destinos' => $destinations,
            'resumen' => [
                'total_productos' => $totalProductos,
                'subtotal' => round($subtotal, 2),
                'total_destinos' => $destinations->count(),
                'total_items' => $products->count(),
            ],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        // Debug: Ver qué está recibiendo el request
        \Log::info('Request data:', [
            'all' => $request->all(),
            'json' => $request->json()->all(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
        ]);

        // Verificar si hay usuario autenticado de forma segura
        $user = null;
        $isAuthenticated = false;
        
        try {
            // Intentar obtener el usuario autenticado sin lanzar excepción
            $user = auth('api')->user();
            $isAuthenticated = $user !== null;
        } catch (\Exception $e) {
            // Si hay error de autenticación, simplemente no hay usuario autenticado
            $isAuthenticated = false;
            $user = null;
        }

        // Validación condicional: si no hay token, requerir datos del usuario
        $rules = [
            // Datos del pedido
            'nombre' => 'required|string|max:200',
            'fecha_entrega' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'editable_hasta' => 'nullable|date|after_or_equal:now',
            // Productos
            'products' => 'required|array|min:1',
            'products.*.producto_id' => 'required|integer|exists:producto,producto_id',
            'products.*.cantidad' => 'required|numeric|min:0.0001',
            'products.*.observaciones' => 'nullable|string',
            // Destinos
            'destinations' => 'required|array|min:1',
            'destinations.*.direccion' => 'required|string|max:500',
            'destinations.*.latitud' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitud' => 'nullable|numeric|between:-180,180',
            'destinations.*.referencia' => 'nullable|string|max:200',
            'destinations.*.nombre_contacto' => 'nullable|string|max:200',
            'destinations.*.telefono_contacto' => 'nullable|string|max:20',
            'destinations.*.instrucciones_entrega' => 'nullable|string',
            'destinations.*.almacen_destino_id' => 'nullable|integer', // ID del almacén destino desde plantaCruds
            'destinations.*.almacen_destino_nombre' => 'nullable|string|max:200', // Nombre del almacén destino
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.cantidad' => 'required|numeric|min:0.0001',
        ];

        // Si no está autenticado, requerir datos del usuario/cliente
        if (!$isAuthenticated) {
            $rules['email'] = 'required|email|max:255';
            $rules['nombre_usuario'] = 'nullable|string|max:200';
            $rules['apellido_usuario'] = 'nullable|string|max:200';
            $rules['telefono_usuario'] = 'nullable|string|max:20';
            $rules['nit'] = 'nullable|string|max:50';
            $rules['direccion_cliente'] = 'nullable|string|max:500';
        }

        // Obtener datos del request
        $requestData = $request->all();
        
        // Si está vacío, intentar obtener del JSON directamente
        if (empty($requestData)) {
            try {
                $jsonData = $request->json()->all();
                if (!empty($jsonData)) {
                    $requestData = $jsonData;
                }
            } catch (\Exception $e) {
                // Si falla, intentar obtener del input
                $requestData = $request->input();
            }
        }

        // Debug: Log para ver qué está recibiendo
        \Log::info('CustomerOrder Store - Request Data:', [
            'request_all' => $request->all(),
            'request_json' => $request->json()->all(),
            'request_input' => $request->input(),
            'requestData' => $requestData,
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
            'method' => $request->method(),
            'raw_content' => $request->getContent(),
        ]);

        $validator = Validator::make($requestData, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'debug' => [
                    'received_data' => $requestData,
                    'request_all' => $request->all(),
                    'request_json' => $request->json()->all(),
                    'content_type' => $request->header('Content-Type'),
                    'is_json' => $request->isJson(),
                    'method' => $request->method(),
                    'has_content' => !empty($request->getContent()),
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Obtener o crear el cliente
            if ($isAuthenticated) {
                // Si hay usuario autenticado, usar su información
                $customerId = $this->getOrCreateCustomerIdFromUser($user);
            } else {
                // Si no hay usuario autenticado, usar datos del body
                // Crear un request temporal con los datos procesados
                $tempRequest = new Request($requestData);
                $customerId = $this->getOrCreateCustomerIdFromRequest($tempRequest);
            }

            if (!$customerId) {
                return response()->json([
                    'message' => 'No se pudo crear o encontrar el cliente',
                    'error' => 'Error al procesar datos del cliente'
                ], 400);
            }

            // Obtener el siguiente ID de la secuencia
            $maxId = DB::table('pedido_cliente')->max('pedido_id') ?? 0;
            if ($maxId > 0) {
                DB::statement("SELECT setval('pedido_cliente_seq', {$maxId}, true)");
            }
            $nextId = DB::selectOne("SELECT nextval('pedido_cliente_seq') as id")->id;
            
            // Generar número de pedido automáticamente
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            // Calcular fecha límite de edición (por defecto 24 horas)
            $editableUntil = isset($requestData['editable_hasta']) && $requestData['editable_hasta']
                ? now()->parse($requestData['editable_hasta'])
                : now()->addHours(24);
            
            $order = CustomerOrder::create([
                'pedido_id' => $nextId,
                'cliente_id' => $customerId,
                'numero_pedido' => $orderNumber,
                'nombre' => $requestData['nombre'],
                'estado' => 'pendiente',
                'fecha_creacion' => now()->toDateString(),
                'fecha_entrega' => $requestData['fecha_entrega'] ?? null,
                'descripcion' => $requestData['descripcion'] ?? null,
                'observaciones' => $requestData['observaciones'] ?? null,
                'editable_hasta' => $editableUntil,
            ]);

            // Crear productos del pedido
            $orderProducts = [];
            foreach ($requestData['products'] as $index => $productData) {
                $maxProductId = DB::table('producto_pedido')->max('producto_pedido_id') ?? 0;
                if ($maxProductId > 0) {
                    DB::statement("SELECT setval('producto_pedido_seq', {$maxProductId}, true)");
                }
                $orderProductId = DB::selectOne("SELECT nextval('producto_pedido_seq') as id")->id;
                
                // Obtener el producto para calcular el precio
                $product = Product::find($productData['producto_id']);
                $precioUnitario = $product->precio_unitario ?? 0;
                $cantidad = $productData['cantidad'];
                $precioTotal = $precioUnitario * $cantidad;
                
                $orderProduct = OrderProduct::create([
                    'producto_pedido_id' => $orderProductId,
                    'pedido_id' => $order->pedido_id,
                    'producto_id' => $productData['producto_id'],
                    'cantidad' => $cantidad,
                    'precio' => $precioTotal,
                    'estado' => 'pendiente',
                    'observaciones' => $productData['observaciones'] ?? null,
                ]);
                
                $orderProducts[] = $orderProduct;
            }

            // Crear destinos y asignar productos
            foreach ($requestData['destinations'] as $destIndex => $destData) {
                $maxDestId = DB::table('destino_pedido')->max('destino_id') ?? 0;
                if ($maxDestId > 0) {
                    DB::statement("SELECT setval('destino_pedido_seq', {$maxDestId}, true)");
                }
                $destinationId = DB::selectOne("SELECT nextval('destino_pedido_seq') as id")->id;
                
                $destination = OrderDestination::create([
                    'destino_id' => $destinationId,
                    'pedido_id' => $order->pedido_id,
                    'direccion' => $destData['direccion'],
                    'latitud' => $destData['latitud'] ?? null,
                    'longitud' => $destData['longitud'] ?? null,
                    'referencia' => $destData['referencia'] ?? null,
                    'nombre_contacto' => $destData['nombre_contacto'] ?? null,
                    'telefono_contacto' => $destData['telefono_contacto'] ?? null,
                    'instrucciones_entrega' => $destData['instrucciones_entrega'] ?? null,
                    'almacen_destino_id' => $destData['almacen_destino_id'] ?? null,
                    'almacen_destino_nombre' => $destData['almacen_destino_nombre'] ?? null,
                ]);

                // Asignar productos a este destino
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $maxDestProdId = DB::table('producto_destino_pedido')->max('producto_destino_id') ?? 0;
                        if ($maxDestProdId > 0) {
                            DB::statement("SELECT setval('producto_destino_pedido_seq', {$maxDestProdId}, true)");
                        }
                        $destProdId = DB::selectOne("SELECT nextval('producto_destino_pedido_seq') as id")->id;
                        
                        OrderDestinationProduct::create([
                            'producto_destino_id' => $destProdId,
                            'destino_id' => $destination->destino_id,
                            'producto_pedido_id' => $orderProducts[$orderProductIndex]->producto_pedido_id,
                            'cantidad' => $destProdData['cantidad'],
                            'observaciones' => $destProdData['observaciones'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'order' => $order->load('orderProducts.product', 'destinations')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:200',
            'delivery_date' => 'nullable|date',
            'description' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $order = CustomerOrder::findOrFail($id);

            // Verificar si el pedido puede ser editado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            $order->update($request->only([
                'name', 'delivery_date', 'description', 'observations'
            ]));

            return response()->json([
                'message' => 'Pedido actualizado exitosamente',
                'order' => $order->load('orderProducts.product', 'destinations')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar pedido sin autenticación (público)
     * Valida que el nombre_usuario coincida con el cliente del pedido
     */
    public function updatePublic(Request $request, $id): JsonResponse
    {
        // Obtener datos del request
        $requestData = $request->all();
        
        // Si está vacío, intentar obtener del JSON directamente
        if (empty($requestData)) {
            try {
                $jsonData = $request->json()->all();
                if (!empty($jsonData)) {
                    $requestData = $jsonData;
                }
            } catch (\Exception $e) {
                $requestData = $request->input();
            }
        }

        // Validación
        $rules = [
            'nombre_usuario' => 'required|string|max:200',
            'nombre' => 'required|string|max:200',
            'fecha_entrega' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'editable_hasta' => 'nullable|date|after_or_equal:now',
            // Productos
            'products' => 'required|array|min:1',
            'products.*.producto_id' => 'required|integer|exists:producto,producto_id',
            'products.*.cantidad' => 'required|numeric|min:0.0001',
            'products.*.observaciones' => 'nullable|string',
            // Destinos
            'destinations' => 'required|array|min:1',
            'destinations.*.direccion' => 'required|string|max:500',
            'destinations.*.latitud' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitud' => 'nullable|numeric|between:-180,180',
            'destinations.*.referencia' => 'nullable|string|max:200',
            'destinations.*.nombre_contacto' => 'nullable|string|max:200',
            'destinations.*.telefono_contacto' => 'nullable|string|max:20',
            'destinations.*.instrucciones_entrega' => 'nullable|string',
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.cantidad' => 'required|numeric|min:0.0001',
        ];

        $validator = Validator::make($requestData, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Obtener el pedido
            $order = CustomerOrder::with('customer')->findOrFail($id);

            // Verificar si el pedido puede ser editado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            // Validar que el nombre_usuario coincida con el cliente del pedido
            $customer = $order->customer;
            if (!$customer) {
                return response()->json([
                    'message' => 'Cliente no encontrado para este pedido'
                ], 404);
            }

            // Comparar nombre_usuario con el contacto del cliente
            // El contacto se crea como "nombre_usuario apellido_usuario" o solo "nombre_usuario"
            $nombreUsuarioRequest = trim($requestData['nombre_usuario']);
            $contactoCliente = trim($customer->contacto ?? '');
            $razonSocialCliente = trim($customer->razon_social ?? '');
            
            // Verificar si el nombre_usuario está contenido en el contacto o razón social
            $nombreCoincide = false;
            if (!empty($contactoCliente)) {
                // Verificar si el nombre_usuario está al inicio del contacto
                $nombreCoincide = stripos($contactoCliente, $nombreUsuarioRequest) === 0;
            }
            
            if (!$nombreCoincide && !empty($razonSocialCliente)) {
                // Verificar si el nombre_usuario está al inicio de la razón social
                $nombreCoincide = stripos($razonSocialCliente, $nombreUsuarioRequest) === 0;
            }

            if (!$nombreCoincide) {
                return response()->json([
                    'message' => 'No tienes permiso para editar este pedido. El nombre de usuario no coincide con el cliente del pedido.'
                ], 403);
            }

            // Calcular fecha límite de edición (por defecto 24 horas)
            $editableUntil = isset($requestData['editable_hasta']) && $requestData['editable_hasta']
                ? now()->parse($requestData['editable_hasta'])
                : $order->editable_hasta ?? now()->addHours(24);

            // Actualizar información básica del pedido
            $order->update([
                'nombre' => $requestData['nombre'],
                'fecha_entrega' => $requestData['fecha_entrega'] ?? null,
                'descripcion' => $requestData['descripcion'] ?? null,
                'observaciones' => $requestData['observaciones'] ?? null,
                'editable_hasta' => $editableUntil,
            ]);

            // Eliminar productos y destinos existentes
            $order->orderProducts()->delete();
            $order->destinations()->delete();

            // Crear nuevos productos del pedido
            $orderProducts = [];
            foreach ($requestData['products'] as $index => $productData) {
                $maxProductId = DB::table('producto_pedido')->max('producto_pedido_id') ?? 0;
                if ($maxProductId > 0) {
                    DB::statement("SELECT setval('producto_pedido_seq', {$maxProductId}, true)");
                }
                $orderProductId = DB::selectOne("SELECT nextval('producto_pedido_seq') as id")->id;
                
                // Obtener el producto para calcular el precio
                $product = Product::find($productData['producto_id']);
                $precioUnitario = $product->precio_unitario ?? 0;
                $cantidad = $productData['cantidad'];
                $precioTotal = $precioUnitario * $cantidad;
                
                $orderProduct = OrderProduct::create([
                    'producto_pedido_id' => $orderProductId,
                    'pedido_id' => $order->pedido_id,
                    'producto_id' => $productData['producto_id'],
                    'cantidad' => $cantidad,
                    'precio' => $precioTotal,
                    'estado' => 'pendiente',
                    'observaciones' => $productData['observaciones'] ?? null,
                ]);
                
                $orderProducts[] = $orderProduct;
            }

            // Crear destinos y asignar productos
            foreach ($requestData['destinations'] as $destIndex => $destData) {
                $maxDestId = DB::table('destino_pedido')->max('destino_id') ?? 0;
                if ($maxDestId > 0) {
                    DB::statement("SELECT setval('destino_pedido_seq', {$maxDestId}, true)");
                }
                $destinationId = DB::selectOne("SELECT nextval('destino_pedido_seq') as id")->id;
                
                $destination = OrderDestination::create([
                    'destino_id' => $destinationId,
                    'pedido_id' => $order->pedido_id,
                    'direccion' => $destData['direccion'],
                    'latitud' => $destData['latitud'] ?? null,
                    'longitud' => $destData['longitud'] ?? null,
                    'referencia' => $destData['referencia'] ?? null,
                    'nombre_contacto' => $destData['nombre_contacto'] ?? null,
                    'telefono_contacto' => $destData['telefono_contacto'] ?? null,
                    'instrucciones_entrega' => $destData['instrucciones_entrega'] ?? null,
                ]);

                // Asignar productos a este destino
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $maxDestProdId = DB::table('producto_destino_pedido')->max('producto_destino_id') ?? 0;
                        if ($maxDestProdId > 0) {
                            DB::statement("SELECT setval('producto_destino_pedido_seq', {$maxDestProdId}, true)");
                        }
                        $destProdId = DB::selectOne("SELECT nextval('producto_destino_pedido_seq') as id")->id;
                        
                        OrderDestinationProduct::create([
                            'producto_destino_id' => $destProdId,
                            'destino_id' => $destination->destino_id,
                            'producto_pedido_id' => $orderProducts[$orderProductIndex]->producto_pedido_id,
                            'cantidad' => $destProdData['cantidad'],
                            'observaciones' => $destProdData['observaciones'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Pedido actualizado exitosamente',
                'order' => $order->load('orderProducts.product', 'destinations')
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id): JsonResponse
    {
        try {
            $order = CustomerOrder::findOrFail($id);

            // Verificar si el pedido puede ser cancelado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser cancelado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            $order->update([
                'status' => 'cancelado'
            ]);

            return response()->json([
                'message' => 'Pedido cancelado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cancelar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $order = CustomerOrder::findOrFail($id);

            // Solo se puede eliminar si está pendiente y puede ser editado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser eliminado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            $order->delete();

            return response()->json([
                'message' => 'Pedido eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener pedidos por nombre de usuario (sin autenticación)
     * Busca el cliente por nombre_usuario y devuelve sus pedidos
     */
    public function byUser(Request $request): JsonResponse
    {
        try {
            // Validar que se proporcione nombre_usuario
            $nombreUsuario = $request->get('nombre_usuario');
            
            if (!$nombreUsuario) {
                return response()->json([
                    'message' => 'El parámetro nombre_usuario es requerido',
                    'error' => 'MissingParameter'
                ], 400);
            }

            // Buscar cliente por nombre_usuario
            // El nombre_usuario puede estar en contacto o al inicio de razon_social
            $nombreUsuarioTrimmed = trim($nombreUsuario);
            
            $customer = Customer::where(function($query) use ($nombreUsuarioTrimmed) {
                // Buscar en contacto que empiece con el nombre_usuario (case-insensitive)
                $query->whereRaw('LOWER(contacto) LIKE LOWER(?)', [$nombreUsuarioTrimmed . '%'])
                      // O buscar en razon_social que empiece con el nombre_usuario (case-insensitive)
                      ->orWhereRaw('LOWER(razon_social) LIKE LOWER(?)', [$nombreUsuarioTrimmed . '%']);
            })
            ->where('activo', true)
            ->first();

            if (!$customer) {
                return response()->json([
                    'message' => 'No se encontró un cliente con ese nombre de usuario',
                    'error' => 'CustomerNotFound'
                ], 404);
            }

            // Obtener pedidos del cliente
            $orders = CustomerOrder::where('cliente_id', $customer->cliente_id)
                ->with([
                    'customer',
                    'orderProducts.product.unit',
                    'destinations.destinationProducts.orderProduct.product'
                ])
                ->orderBy('fecha_creacion', 'desc')
                ->orderBy('pedido_id', 'desc')
                ->get();

            // Transformar para asegurar estructura consistente
            $ordersData = $orders->map(function($order) {
                $data = $order->toArray();
                $data['orderProducts'] = $order->orderProducts->map(function($op) {
                    return [
                        'producto_pedido_id' => $op->producto_pedido_id,
                        'producto_id' => $op->producto_id,
                        'cantidad' => $op->cantidad,
                        'estado' => $op->estado,
                        'observaciones' => $op->observaciones,
                        'razon_rechazo' => $op->razon_rechazo,
                        'product' => [
                            'producto_id' => $op->product->producto_id,
                            'nombre' => $op->product->nombre ?? 'N/A',
                            'codigo' => $op->product->codigo ?? 'N/A',
                            'unit' => [
                                'nombre' => $op->product->unit->nombre ?? 'N/A',
                                'abbreviation' => $op->product->unit->codigo ?? 'N/A',
                            ]
                        ]
                    ];
                });
                return $data;
            });

            return response()->json([
                'message' => 'Pedidos obtenidos exitosamente',
                'customer' => [
                    'cliente_id' => $customer->cliente_id,
                    'razon_social' => $customer->razon_social,
                    'contacto' => $customer->contacto,
                    'email' => $customer->email,
                ],
                'orders' => $ordersData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene o crea un cliente basado en el usuario autenticado
     */
    private function getOrCreateCustomerIdFromUser($user): ?int
    {
        $customerId = $user->cliente_id ?? null;
        $customer = null;

        // Si el usuario ya tiene cliente_id, usarlo
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                return $customer->cliente_id;
            }
        }

        // Buscar por email
        if ($user->email) {
            $customer = Customer::where('email', $user->email)->first();
            if ($customer) {
                return $customer->cliente_id;
            }
        }

        // Si no existe, crear uno nuevo
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

            // Preparar nombre completo
            $nombreCompleto = trim(
                ($user->nombre ?? '') . ' ' . 
                ($user->apellido ?? '')
            );
            
            if (empty($nombreCompleto)) {
                $nombreCompleto = 'Cliente ' . ($user->usuario ?? $user->email ?? 'Usuario');
            }

            // Crear el cliente
            $customer = Customer::create([
                'cliente_id' => $nextId,
                'razon_social' => $nombreCompleto,
                'nombre_comercial' => $nombreCompleto,
                'email' => $user->email ?? null,
                'contacto' => $nombreCompleto,
                'activo' => true,
            ]);

            return $customer->cliente_id;
        } catch (\Exception $e) {
            // Si falla, intentar obtener el primer cliente activo como fallback
            $customer = Customer::where('activo', true)->first();
            return $customer ? $customer->cliente_id : null;
        }
    }

    /**
     * Obtiene o crea un cliente basado en los datos del request (sin autenticación)
     */
    private function getOrCreateCustomerIdFromRequest(Request $request): ?int
    {
        $email = $request->email;
        $customer = null;

        // Buscar cliente por email
        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }

        // Si no existe, crear uno nuevo
        if (!$customer) {
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

                // Preparar nombre completo
                $nombreCompleto = trim(
                    ($request->nombre_usuario ?? '') . ' ' . 
                    ($request->apellido_usuario ?? '')
                );
                
                if (empty($nombreCompleto)) {
                    $nombreCompleto = 'Cliente ' . ($request->email ?? 'Usuario');
                }

                // Crear el cliente
                $customer = Customer::create([
                    'cliente_id' => $nextId,
                    'razon_social' => $nombreCompleto,
                    'nombre_comercial' => $nombreCompleto,
                    'nit' => $request->nit ?? null,
                    'direccion' => $request->direccion_cliente ?? null,
                    'telefono' => $request->telefono_usuario ?? null,
                    'email' => $email,
                    'contacto' => $nombreCompleto,
                    'activo' => true,
                ]);
            } catch (\Exception $e) {
                // Si falla, intentar obtener el primer cliente activo como fallback
                $customer = Customer::where('activo', true)->first();
                if (!$customer) {
                    return null;
                }
            }
        }

        return $customer ? $customer->cliente_id : null;
    }

    /**
     * Obtener pedido por envio_id (para integración con plantaCruds)
     * GET /api/pedidos/by-envio/{envioId}
     * 
     * @param int $envioId
     * @return JsonResponse
     */
    public function getByEnvioId($envioId): JsonResponse
    {
        try {
            // Buscar en order_envio_tracking
            $tracking = DB::table('seguimiento_envio_pedido')
                ->where('envio_id', $envioId)
                ->where('estado', 'success')
                ->first();

            if (!$tracking) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró pedido asociado a este envío'
                ], 404);
            }

            // Obtener el pedido
            $pedido = CustomerOrder::where('pedido_id', $tracking->pedido_id)->first();

            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'pedido_id' => $pedido->pedido_id,
                'numero_pedido' => $pedido->numero_pedido,
                'envio_id' => $envioId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

