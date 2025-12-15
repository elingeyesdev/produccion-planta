<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\Product;
use App\Models\RawMaterialBase;
use App\Models\RawMaterialCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlmacenPedidoTransformService
{
    /**
     * Transforma un pedido de sistema-almacen-PSIII a estructura de Trazabilidad
     * 
     * @param array $pedidoData Datos del pedido desde sistema-almacen-PSIII
     * @return array Datos transformados listos para crear CustomerOrder
     */
    public function transformToCustomerOrder(array $pedidoData): array
    {
        // Crear o buscar cliente basado en administrador/almacén
        $customer = $this->findOrCreateCustomer($pedidoData['administrador'] ?? []);

        // Generar número de pedido único
        $numeroPedido = $this->generateOrderNumber($pedidoData['codigo_comprobante'] ?? null);

        // Generar pedido_id manualmente (no es auto-increment)
        $lastOrder = CustomerOrder::orderBy('pedido_id', 'desc')->first();
        $nextPedidoId = $lastOrder ? ($lastOrder->pedido_id + 1) : 1;

        return [
            'pedido_id' => $nextPedidoId,
            'cliente_id' => $customer->cliente_id,
            'numero_pedido' => $numeroPedido,
            'nombre' => $pedidoData['codigo_comprobante'] ?? 'Pedido desde Almacén', // Usar código del pedido como nombre
            'estado' => 'pendiente',
            'fecha_creacion' => $pedidoData['fecha'] ?? now()->format('Y-m-d'),
            'fecha_entrega' => $pedidoData['fecha_max'] ?? now()->addDays(7)->format('Y-m-d'),
            'descripcion' => "Pedido desde Sistema Almacén - {$pedidoData['codigo_comprobante']}",
            'observaciones' => $this->buildObservations($pedidoData),
            'origen_sistema' => 'almacen',
            'pedido_almacen_id' => $pedidoData['pedido_id'] ?? null,
        ];
    }

    /**
     * Crea o busca un cliente basado en datos del administrador
     * 
     * @param array $administradorData
     * @return Customer
     */
    private function findOrCreateCustomer(array $administradorData): Customer
    {
        $email = $administradorData['email'] ?? null;
        $fullName = $administradorData['full_name'] ?? 'Cliente Almacén';

        if ($email) {
            // Buscar por email
            $customer = Customer::where('email', $email)->first();
            if ($customer) {
                return $customer;
            }
        }

        // Generar cliente_id manualmente (no es auto-increment)
        $lastCustomer = Customer::orderBy('cliente_id', 'desc')->first();
        $nextClienteId = $lastCustomer ? ($lastCustomer->cliente_id + 1) : 1;
        
        // Crear nuevo cliente
        $customer = Customer::create([
            'cliente_id' => $nextClienteId,
            'razon_social' => $fullName,
            'nombre_comercial' => $fullName,
            'email' => $email,
            'telefono' => $administradorData['phone_number'] ?? null,
            'direccion' => null,
            'activo' => true,
        ]);

        Log::info('Cliente creado desde pedido de almacén', [
            'cliente_id' => $customer->cliente_id,
            'razon_social' => $fullName,
            'email' => $email
        ]);

        return $customer;
    }

    /**
     * Genera número de pedido único
     * Usa el mismo código del pedido de almacenes para mantener consistencia
     * 
     * @param string|null $codigoComprobante
     * @return string
     */
    private function generateOrderNumber(?string $codigoComprobante): string
    {
        // Usar directamente el código del pedido de almacenes (sin prefijo ALM-)
        // Esto mantiene el mismo código en todos los sistemas
        if ($codigoComprobante) {
            return $codigoComprobante;
        }

        // Solo generar un código nuevo si no viene del almacén
        $lastOrder = CustomerOrder::orderBy('pedido_id', 'desc')->first();
        $nextId = $lastOrder ? $lastOrder->pedido_id + 1 : 1;
        
        return "TRZ-PED-" . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Construye observaciones completas del pedido
     * 
     * @param array $pedidoData
     * @return string
     */
    private function buildObservations(array $pedidoData): string
    {
        $obs = "Pedido desde Sistema Almacén\n";
        $obs .= "Código: {$pedidoData['codigo_comprobante']}\n";
        $obs .= "Fecha: {$pedidoData['fecha']}\n";
        $obs .= "Fecha mínima: {$pedidoData['fecha_min']}\n";
        $obs .= "Fecha máxima: {$pedidoData['fecha_max']}\n";

        if (isset($pedidoData['administrador']['full_name'])) {
            $obs .= "Solicitante: {$pedidoData['administrador']['full_name']}";
            if (isset($pedidoData['administrador']['email'])) {
                $obs .= " ({$pedidoData['administrador']['email']})";
            }
            $obs .= "\n";
        }

        if (isset($pedidoData['operador']['full_name'])) {
            $obs .= "Operador: {$pedidoData['operador']['full_name']}\n";
        }

        if (isset($pedidoData['transportista']['full_name'])) {
            $obs .= "Transportista: {$pedidoData['transportista']['full_name']}\n";
        }

        if (isset($pedidoData['proveedor_id'])) {
            $obs .= "Proveedor ID: {$pedidoData['proveedor_id']}\n";
        }

        if (isset($pedidoData['observaciones'])) {
            $obs .= "\nNotas adicionales:\n{$pedidoData['observaciones']}";
        }

        return trim($obs);
    }

    /**
     * Crea productos del pedido en Trazabilidad
     * 
     * @param CustomerOrder $order
     * @param array $productosData
     * @return array Array de OrderProduct creados
     */
    public function createOrderProducts(CustomerOrder $order, array $productosData): array
    {
        $orderProducts = [];
        
        // Obtener el último producto_pedido_id para generar el siguiente
        $lastOrderProduct = OrderProduct::orderBy('producto_pedido_id', 'desc')->first();
        $nextProductoPedidoId = $lastOrderProduct ? ($lastOrderProduct->producto_pedido_id + 1) : 1;

        foreach ($productosData as $productoData) {
            // Buscar o crear producto
            $product = $this->findOrCreateProduct($productoData);

            // Crear OrderProduct con producto_pedido_id generado manualmente
            $orderProduct = OrderProduct::create([
                'producto_pedido_id' => $nextProductoPedidoId++,
                'pedido_id' => $order->pedido_id,
                'producto_id' => $product->producto_id,
                'cantidad' => (float) ($productoData['cantidad'] ?? 0),
                'precio' => (float) ($productoData['precio'] ?? 0.00),
                'estado' => 'pendiente',
            ]);

            $orderProducts[] = $orderProduct;
        }

        return $orderProducts;
    }

    /**
     * Busca o crea un producto en Trazabilidad
     * 
     * @param array $productoData
     * @return Product
     */
    private function findOrCreateProduct(array $productoData): Product
    {
        $productoNombre = $productoData['producto_nombre'] ?? 'Producto sin nombre';
        $productoId = $productoData['producto_id'] ?? null;

        // Si hay producto_id, intentar buscar por ID primero (si hay sincronización)
        if ($productoId) {
            $product = Product::find($productoId);
            if ($product) {
                return $product;
            }
        }

        // Buscar por nombre
        $product = Product::where('nombre', $productoNombre)->first();
        if ($product) {
            return $product;
        }

        // Crear nuevo producto
        $product = Product::create([
            'codigo' => 'ALM-' . strtoupper(substr($productoNombre, 0, 3)) . '-' . time(),
            'nombre' => $productoNombre,
            'tipo' => 'general',
            'peso' => (float) ($productoData['peso_kg'] ?? $productoData['peso'] ?? 0),
            'precio_unitario' => (float) ($productoData['precio'] ?? 0),
            'descripcion' => "Producto importado desde Sistema Almacén",
            'activo' => true,
        ]);

        Log::info('Producto creado desde pedido de almacén', [
            'producto_id' => $product->producto_id,
            'nombre' => $productoNombre
        ]);

        return $product;
    }

    /**
     * Crea destino del pedido (almacén como destino)
     * 
     * @param CustomerOrder $order
     * @param array $almacenData
     * @param array|null $operadorData
     * @return OrderDestination
     */
    public function createOrderDestination(CustomerOrder $order, array $almacenData, ?array $operadorData = null): OrderDestination
    {
        // Generar destino_id manualmente (no es auto-increment)
        $lastDestination = OrderDestination::orderBy('destino_id', 'desc')->first();
        $nextDestinoId = $lastDestination ? ($lastDestination->destino_id + 1) : 1;
        
        return OrderDestination::create([
            'destino_id' => $nextDestinoId,
            'pedido_id' => $order->pedido_id,
            'direccion' => $almacenData['direccion'] ?? $almacenData['nombre'] ?? 'Dirección no especificada',
            'latitud' => $almacenData['latitud'] ?? null,
            'longitud' => $almacenData['longitud'] ?? null,
            'nombre_contacto' => $operadorData['full_name'] ?? null,
            'telefono_contacto' => null,
            'instrucciones_entrega' => "Entrega en almacén: {$almacenData['nombre']}",
            'almacen_almacen_id' => $almacenData['id'] ?? null, // ID del almacén en sistema-almacen-PSIII
        ]);
    }

    /**
     * Crea automáticamente materias primas base para los productos del pedido
     * Esto ayuda a recordar qué productos se necesitan para el pedido
     * 
     * @param array $orderProducts Array de OrderProduct creados
     * @param CustomerOrder|null $order Pedido al que pertenecen los productos (opcional)
     * @return void
     */
    public function createRawMaterialsFromProducts(array $orderProducts, ?CustomerOrder $order = null): void
    {
        if (empty($orderProducts)) {
            Log::warning('No se pueden crear materias primas: array de productos vacío', [
                'pedido' => $order ? $order->numero_pedido : null
            ]);
            return;
        }

        // Obtener categoría por defecto (primera categoría activa o crear una genérica)
        $categoria = RawMaterialCategory::where('activo', true)->first();
        if (!$categoria) {
            Log::warning('No se encontró categoría activa para crear materias primas base', [
                'pedido' => $order ? $order->numero_pedido : null
            ]);
            return;
        }

        // Obtener unidad por defecto (buscar "KG" o primera unidad activa)
        $unidad = UnitOfMeasure::where('activo', true)
            ->where(function($query) {
                $query->where('codigo', 'KG')
                      ->orWhere('codigo', 'kg')
                      ->orWhere('nombre', 'LIKE', '%kilogramo%');
            })
            ->first();

        // Si no hay unidad KG, usar la primera unidad activa
        if (!$unidad) {
            $unidad = UnitOfMeasure::where('activo', true)->first();
        }

        if (!$unidad) {
            Log::warning('No se encontró unidad de medida activa para crear materias primas base');
            return;
        }

        foreach ($orderProducts as $orderProduct) {
            $product = $orderProduct->product;
            if (!$product) {
                continue;
            }

            $productoNombre = $product->nombre ?? 'Producto sin nombre';
            
            // Verificar si ya existe una materia prima base con este nombre
            $existingMaterial = RawMaterialBase::where('nombre', $productoNombre)
                ->where('activo', true)
                ->first();

            if ($existingMaterial) {
                // Si ya existe, actualizar la cantidad disponible con la cantidad del pedido
                // (sumar a la cantidad existente)
                $existingMaterial->cantidad_disponible = 
                    ($existingMaterial->cantidad_disponible ?? 0) + $orderProduct->cantidad;
                
                // Actualizar descripción para incluir referencia al pedido
                $pedidoInfo = $order ? "Pedido: {$order->numero_pedido}" : '';
                $descripcionActual = $existingMaterial->descripcion ?? '';
                if ($pedidoInfo && strpos($descripcionActual, $pedidoInfo) === false) {
                    $existingMaterial->descripcion = trim($descripcionActual . "\n" . $pedidoInfo);
                }
                
                $existingMaterial->save();
                
                Log::info('Materia prima base actualizada desde pedido de almacén', [
                    'material_id' => $existingMaterial->material_id,
                    'nombre' => $productoNombre,
                    'cantidad_agregada' => $orderProduct->cantidad,
                    'cantidad_total' => $existingMaterial->cantidad_disponible,
                    'pedido' => $order ? $order->numero_pedido : null
                ]);
                continue;
            }

            // Crear nueva materia prima base
            try {
                // Sincronizar la secuencia y obtener el siguiente ID
                $maxId = DB::table('materia_prima_base')->max('material_id');
                
                if ($maxId !== null && $maxId > 0) {
                    DB::statement("SELECT setval('materia_prima_base_seq', {$maxId}, true)");
                }
                
                $nextId = DB::selectOne("SELECT nextval('materia_prima_base_seq') as id")->id;
                
                // Generar código automáticamente
                $code = 'MP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

                // Construir descripción con información del pedido
                $pedidoInfo = $order ? "Pedido: {$order->numero_pedido}" : '';
                $unidadCodigo = $unidad->codigo ?? 'KG';
                $descripcion = "Materia prima creada automáticamente desde pedido de almacén.\n";
                $descripcion .= "Producto: {$productoNombre}\n";
                $descripcion .= "Cantidad requerida: {$orderProduct->cantidad} {$unidadCodigo}\n";
                if ($pedidoInfo) {
                    $descripcion .= $pedidoInfo;
                }

                // Crear usando SQL directo para evitar conflictos
                $materialId = DB::selectOne("
                    INSERT INTO materia_prima_base (material_id, categoria_id, unidad_id, codigo, nombre, descripcion, cantidad_disponible, stock_minimo, stock_maximo, activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    RETURNING material_id
                ", [
                    $nextId,
                    $categoria->categoria_id,
                    $unidad->unidad_id,
                    $code,
                    $productoNombre,
                    $descripcion,
                    $orderProduct->cantidad, // Cantidad disponible inicial = cantidad del pedido
                    0, // Stock mínimo por defecto
                    null, // Stock máximo sin límite
                    true
                ])->material_id;

                Log::info('Materia prima base creada automáticamente desde pedido de almacén', [
                    'material_id' => $materialId,
                    'nombre' => $productoNombre,
                    'cantidad_disponible' => $orderProduct->cantidad,
                    'categoria_id' => $categoria->categoria_id,
                    'unidad_id' => $unidad->unidad_id,
                    'pedido' => $order ? $order->numero_pedido : null
                ]);
            } catch (\Exception $e) {
                Log::error('Error al crear materia prima base desde pedido de almacén', [
                    'producto_nombre' => $productoNombre,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }
}

