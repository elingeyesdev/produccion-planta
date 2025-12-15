<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migración consolidada: Base de datos en español sin bucles ni redundancias
     * 
     * OBJETIVOS:
     * 1. Todos los nombres en español
     * 2. Eliminar bucles/ciclos innecesarios
     * 3. Eliminar redundancias (status, operator_role si Spatie lo cubre)
     * 4. Mantener solo lo esencial
     * 5. Incluir Spatie Laravel Permission
     */
    public function up(): void
    {
        // =============================================
        // SPATIE LARAVEL PERMISSION
        // Se crea en migración separada (2025_01_19_000000_create_spatie_permission_tables.php)
        // que se ejecuta antes de esta migración
        // =============================================

        // =============================================
        // TABLAS PARAMÉTRICAS BÁSICAS
        // =============================================

        // Unidades de medida
        if (!Schema::hasTable('unidad_medida')) {
            Schema::create('unidad_medida', function (Blueprint $table) {
                $table->integer('unidad_id')->primary();
                $table->string('codigo', 10)->unique();
                $table->string('nombre', 50);
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // Tipos de movimiento
        if (!Schema::hasTable('tipo_movimiento')) {
            Schema::create('tipo_movimiento', function (Blueprint $table) {
                $table->integer('tipo_movimiento_id')->primary();
                $table->string('codigo', 20)->unique();
                $table->string('nombre', 100);
                $table->boolean('afecta_stock')->default(true);
                $table->boolean('es_entrada')->default(false);
                $table->boolean('activo')->default(true);
            });
        }

        // Clientes
        if (!Schema::hasTable('cliente')) {
            Schema::create('cliente', function (Blueprint $table) {
                $table->integer('cliente_id')->primary();
                $table->string('razon_social', 200);
                $table->string('nombre_comercial', 200)->nullable();
                $table->string('nit', 20)->nullable()->unique();
                $table->string('direccion', 255)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('contacto', 100)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // Categorías de materia prima
        if (!Schema::hasTable('categoria_materia_prima')) {
            Schema::create('categoria_materia_prima', function (Blueprint $table) {
                $table->integer('categoria_id')->primary();
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // Proveedores
        if (!Schema::hasTable('proveedor')) {
            Schema::create('proveedor', function (Blueprint $table) {
                $table->integer('proveedor_id')->primary();
                $table->string('razon_social', 200);
                $table->string('nombre_comercial', 200)->nullable();
                $table->string('nit', 20)->nullable()->unique();
                $table->string('contacto', 100)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // =============================================
        // TABLAS DE CONFIGURACIÓN
        // =============================================

        // Variables estándar
        if (!Schema::hasTable('variable_estandar')) {
            Schema::create('variable_estandar', function (Blueprint $table) {
                $table->integer('variable_id')->primary();
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100);
                $table->string('unidad', 50)->nullable();
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // Máquinas
        if (!Schema::hasTable('maquina')) {
            Schema::create('maquina', function (Blueprint $table) {
                $table->integer('maquina_id')->primary();
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->string('imagen_url', 500)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // Procesos
        if (!Schema::hasTable('proceso')) {
            Schema::create('proceso', function (Blueprint $table) {
                $table->integer('proceso_id')->primary();
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->boolean('activo')->default(true);
            });
        }

        // Operadores (usuarios del sistema)
        // NOTA: operator_role se elimina porque Spatie maneja los roles
        if (!Schema::hasTable('operador')) {
            Schema::create('operador', function (Blueprint $table) {
                $table->integer('operador_id')->primary();
                $table->string('nombre', 100);
                $table->string('apellido', 100);
                $table->string('usuario', 60)->unique();
                $table->string('password_hash', 255);
                $table->string('email', 100)->nullable();
                $table->boolean('activo')->default(true);
                // Eliminamos role_id porque Spatie maneja los roles
            });
        }

        // =============================================
        // TABLAS DE INVENTARIO
        // =============================================

        // Materia prima base (catálogo)
        if (!Schema::hasTable('materia_prima_base')) {
            Schema::create('materia_prima_base', function (Blueprint $table) {
                $table->integer('material_id')->primary();
                $table->integer('categoria_id');
                $table->integer('unidad_id');
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->decimal('cantidad_disponible', 15, 4)->default(0);
                $table->decimal('stock_minimo', 15, 4)->default(0);
                $table->decimal('stock_maximo', 15, 4)->nullable();
                $table->boolean('activo')->default(true);
                $table->foreign('categoria_id')->references('categoria_id')->on('categoria_materia_prima');
                $table->foreign('unidad_id')->references('unidad_id')->on('unidad_medida');
            });
        }

        // Materia prima (lotes recibidos)
        if (!Schema::hasTable('materia_prima')) {
            Schema::create('materia_prima', function (Blueprint $table) {
                $table->integer('materia_prima_id')->primary();
                $table->integer('material_id');
                $table->integer('proveedor_id');
                $table->string('lote_proveedor', 100)->nullable();
                $table->string('numero_factura', 100)->nullable();
                $table->date('fecha_recepcion');
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('cantidad', 15, 4);
                $table->decimal('cantidad_disponible', 15, 4);
                $table->boolean('conformidad_recepcion')->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->foreign('material_id')->references('material_id')->on('materia_prima_base');
                $table->foreign('proveedor_id')->references('proveedor_id')->on('proveedor');
            });
        }

        // =============================================
        // TABLAS DE PRODUCCIÓN (JERARQUÍA PRINCIPAL)
        // =============================================

        // Productos
        if (!Schema::hasTable('producto')) {
            Schema::create('producto', function (Blueprint $table) {
                $table->integer('producto_id')->primary();
                $table->string('codigo', 50)->unique();
                $table->string('nombre', 200);
                $table->enum('tipo', ['organico', 'marca_univalle', 'comestibles'])->default('comestibles');
                $table->decimal('peso', 10, 2)->nullable()->comment('Peso en kg');
                $table->integer('unidad_id')->nullable();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
                $table->foreign('unidad_id')->references('unidad_id')->on('unidad_medida');
            });
        }

        // Pedidos de clientes
        if (!Schema::hasTable('pedido_cliente')) {
            Schema::create('pedido_cliente', function (Blueprint $table) {
                $table->integer('pedido_id')->primary();
                $table->integer('cliente_id');
                $table->string('numero_pedido', 50)->unique();
                $table->string('nombre', 200)->nullable()->comment('Nombre del pedido');
                $table->decimal('cantidad', 15, 4)->nullable();
                $table->string('estado', 50)->default('pendiente')->comment('pendiente, aprobado, rechazado, en_produccion, completado, cancelado');
                $table->date('fecha_creacion')->default(DB::raw('CURRENT_DATE'));
                $table->date('fecha_entrega')->nullable();
                $table->text('descripcion')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamp('editable_hasta')->nullable();
                $table->timestamp('aprobado_en')->nullable();
                $table->integer('aprobado_por')->nullable()->comment('ID del operador que aprobó');
                $table->text('razon_rechazo')->nullable();
                $table->foreign('cliente_id')->references('cliente_id')->on('cliente');
                // Eliminamos foreign key a operador para evitar bucles innecesarios
                // Se puede obtener el operador mediante consulta directa si es necesario
            });
        }

        // Productos del pedido
        if (!Schema::hasTable('producto_pedido')) {
            Schema::create('producto_pedido', function (Blueprint $table) {
                $table->integer('producto_pedido_id')->primary();
                $table->integer('pedido_id');
                $table->integer('producto_id');
                $table->decimal('cantidad', 15, 4);
                $table->string('estado', 50)->default('pendiente')->comment('pendiente, aprobado, rechazado');
                $table->text('razon_rechazo')->nullable();
                $table->integer('aprobado_por')->nullable();
                $table->timestamp('aprobado_en')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('pedido_id')->references('pedido_id')->on('pedido_cliente')->onDelete('cascade');
                $table->foreign('producto_id')->references('producto_id')->on('producto');
                // Eliminamos foreign key a operador para evitar bucles
                $table->index(['pedido_id', 'producto_id']);
            });
        }

        // Destinos del pedido
        if (!Schema::hasTable('destino_pedido')) {
            Schema::create('destino_pedido', function (Blueprint $table) {
                $table->integer('destino_id')->primary();
                $table->integer('pedido_id');
                $table->string('direccion', 500);
                $table->string('referencia', 200)->nullable();
                $table->decimal('latitud', 10, 8)->nullable();
                $table->decimal('longitud', 11, 8)->nullable();
                $table->string('nombre_contacto', 200)->nullable();
                $table->string('telefono_contacto', 20)->nullable();
                $table->text('instrucciones_entrega')->nullable();
                $table->integer('almacen_origen_id')->nullable();
                $table->string('almacen_origen_nombre')->nullable();
                $table->integer('almacen_destino_id')->nullable();
                $table->string('almacen_destino_nombre')->nullable();
                $table->timestamps();
                $table->foreign('pedido_id')->references('pedido_id')->on('pedido_cliente')->onDelete('cascade');
                $table->index('pedido_id');
            });
        }

        // Productos por destino
        if (!Schema::hasTable('producto_destino_pedido')) {
            Schema::create('producto_destino_pedido', function (Blueprint $table) {
                $table->integer('producto_destino_id')->primary();
                $table->integer('destino_id');
                $table->integer('producto_pedido_id');
                $table->decimal('cantidad', 15, 4);
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('destino_id')->references('destino_id')->on('destino_pedido')->onDelete('cascade');
                $table->foreign('producto_pedido_id')->references('producto_pedido_id')->on('producto_pedido')->onDelete('cascade');
                $table->index(['destino_id', 'producto_pedido_id']);
            });
        }

        // Lotes de producción
        if (!Schema::hasTable('lote_produccion')) {
            Schema::create('lote_produccion', function (Blueprint $table) {
                $table->integer('lote_id')->primary();
                $table->integer('pedido_id');
                $table->string('codigo_lote', 50)->unique();
                $table->string('nombre', 100)->default('Lote sin nombre');
                $table->date('fecha_creacion')->default(DB::raw('CURRENT_DATE'));
                $table->timestamp('hora_inicio')->nullable();
                $table->timestamp('hora_fin')->nullable();
                $table->decimal('cantidad_objetivo', 15, 4)->nullable();
                $table->decimal('cantidad_producida', 15, 4)->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->foreign('pedido_id')->references('pedido_id')->on('pedido_cliente');
            });
        }

        // Materia prima usada en lotes
        if (!Schema::hasTable('lote_materia_prima')) {
            Schema::create('lote_materia_prima', function (Blueprint $table) {
                $table->integer('lote_material_id')->primary();
                $table->integer('lote_id');
                $table->integer('materia_prima_id');
                $table->decimal('cantidad_planificada', 15, 4);
                $table->decimal('cantidad_usada', 15, 4)->nullable();
                $table->foreign('lote_id')->references('lote_id')->on('lote_produccion');
                $table->foreign('materia_prima_id')->references('materia_prima_id')->on('materia_prima');
            });
        }

        // Registro de movimientos de material
        if (!Schema::hasTable('registro_movimiento_material')) {
            Schema::create('registro_movimiento_material', function (Blueprint $table) {
                $table->integer('registro_id')->primary();
                $table->integer('material_id');
                $table->integer('tipo_movimiento_id');
                $table->integer('operador_id')->nullable();
                $table->decimal('cantidad', 15, 4);
                $table->decimal('saldo_anterior', 15, 4)->nullable();
                $table->decimal('saldo_nuevo', 15, 4)->nullable();
                $table->string('descripcion', 500)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamp('fecha_movimiento')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->foreign('material_id')->references('material_id')->on('materia_prima_base');
                $table->foreign('tipo_movimiento_id')->references('tipo_movimiento_id')->on('tipo_movimiento');
                // Eliminamos foreign key a operador para evitar bucles
            });
        }

        // =============================================
        // TABLAS DE PROCESOS DE PRODUCCIÓN
        // =============================================

        // Proceso-Máquina (pasos del proceso)
        if (!Schema::hasTable('proceso_maquina')) {
            Schema::create('proceso_maquina', function (Blueprint $table) {
                $table->integer('proceso_maquina_id')->primary();
                $table->integer('proceso_id');
                $table->integer('maquina_id');
                $table->integer('orden_paso');
                $table->string('nombre', 100);
                $table->string('descripcion', 255)->nullable();
                $table->integer('tiempo_estimado')->nullable();
                $table->foreign('proceso_id')->references('proceso_id')->on('proceso');
                $table->foreign('maquina_id')->references('maquina_id')->on('maquina');
            });
        }

        // Variables de proceso-máquina
        if (!Schema::hasTable('variable_proceso_maquina')) {
            Schema::create('variable_proceso_maquina', function (Blueprint $table) {
                $table->integer('variable_id')->primary();
                $table->integer('proceso_maquina_id');
                $table->integer('variable_estandar_id');
                $table->decimal('valor_minimo', 10, 2);
                $table->decimal('valor_maximo', 10, 2);
                $table->decimal('valor_objetivo', 10, 2)->nullable();
                $table->boolean('obligatorio')->default(true);
                $table->foreign('proceso_maquina_id')->references('proceso_maquina_id')->on('proceso_maquina');
                $table->foreign('variable_estandar_id')->references('variable_id')->on('variable_estandar');
            });
        }

        // Registros de proceso-máquina
        if (!Schema::hasTable('registro_proceso_maquina')) {
            Schema::create('registro_proceso_maquina', function (Blueprint $table) {
                $table->integer('registro_id')->primary();
                $table->integer('lote_id');
                $table->integer('proceso_maquina_id');
                $table->integer('operador_id');
                $table->text('variables_ingresadas');
                $table->boolean('cumple_estandar');
                $table->string('observaciones', 500)->nullable();
                $table->timestamp('hora_inicio')->nullable();
                $table->timestamp('hora_fin')->nullable();
                $table->timestamp('fecha_registro')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->foreign('lote_id')->references('lote_id')->on('lote_produccion');
                $table->foreign('proceso_maquina_id')->references('proceso_maquina_id')->on('proceso_maquina');
                // Mantenemos foreign key a operador aquí porque es necesario para trazabilidad
                $table->foreign('operador_id')->references('operador_id')->on('operador');
            });
        }

        // Evaluación final del proceso
        if (!Schema::hasTable('evaluacion_final_proceso')) {
            Schema::create('evaluacion_final_proceso', function (Blueprint $table) {
                $table->integer('evaluacion_id')->primary();
                $table->integer('lote_id');
                $table->integer('inspector_id')->nullable();
                $table->string('razon', 500)->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->timestamp('fecha_evaluacion')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->foreign('lote_id')->references('lote_id')->on('lote_produccion');
                // Eliminamos foreign key a operador para evitar bucles
            });
        }

        // Almacenaje
        if (!Schema::hasTable('almacenaje')) {
            Schema::create('almacenaje', function (Blueprint $table) {
                $table->integer('almacenaje_id')->primary();
                $table->integer('lote_id');
                $table->string('ubicacion', 100);
                $table->string('condicion', 100);
                $table->decimal('cantidad', 15, 4);
                $table->string('observaciones', 500)->nullable();
                $table->decimal('latitud_recojo', 10, 8)->nullable();
                $table->decimal('longitud_recojo', 11, 8)->nullable();
                $table->string('direccion_recojo', 500)->nullable();
                $table->string('referencia_recojo', 255)->nullable();
                $table->timestamp('fecha_almacenaje')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('fecha_retiro')->nullable();
                $table->foreign('lote_id')->references('lote_id')->on('lote_produccion');
            });
        }

        // =============================================
        // TABLAS DE GESTIÓN DE MATERIALES (OPCIONALES)
        // =============================================

        // Solicitudes de material
        if (!Schema::hasTable('solicitud_material')) {
            Schema::create('solicitud_material', function (Blueprint $table) {
                $table->integer('solicitud_id')->primary();
                $table->integer('pedido_id');
                $table->string('numero_solicitud', 50)->unique();
                $table->date('fecha_solicitud')->default(DB::raw('CURRENT_DATE'));
                $table->date('fecha_requerida');
                $table->text('observaciones')->nullable();
                $table->foreign('pedido_id')->references('pedido_id')->on('pedido_cliente');
            });
        }

        // Detalles de solicitud de material
        if (!Schema::hasTable('detalle_solicitud_material')) {
            Schema::create('detalle_solicitud_material', function (Blueprint $table) {
                $table->integer('detalle_id')->primary();
                $table->integer('solicitud_id');
                $table->integer('material_id');
                $table->decimal('cantidad_solicitada', 15, 4);
                $table->decimal('cantidad_aprobada', 15, 4)->nullable();
                $table->foreign('solicitud_id')->references('solicitud_id')->on('solicitud_material');
                $table->foreign('material_id')->references('material_id')->on('materia_prima_base');
            });
        }

        // Respuestas de proveedores
        if (!Schema::hasTable('respuesta_proveedor')) {
            Schema::create('respuesta_proveedor', function (Blueprint $table) {
                $table->integer('respuesta_id')->primary();
                $table->integer('solicitud_id');
                $table->integer('proveedor_id');
                $table->timestamp('fecha_respuesta')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->decimal('cantidad_confirmada', 15, 4)->nullable();
                $table->date('fecha_entrega')->nullable();
                $table->text('observaciones')->nullable();
                $table->decimal('precio', 15, 2)->nullable();
                $table->foreign('solicitud_id')->references('solicitud_id')->on('solicitud_material');
                $table->foreign('proveedor_id')->references('proveedor_id')->on('proveedor');
            });
        }

        // Seguimiento de envíos
        if (!Schema::hasTable('seguimiento_envio_pedido')) {
            Schema::create('seguimiento_envio_pedido', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('pedido_id')->nullable()->index();
                $table->integer('destino_id')->nullable()->index();
                $table->integer('envio_id')->nullable()->index();
                $table->string('codigo_envio')->nullable();
                $table->string('estado')->default('pendiente');
                $table->text('mensaje_error')->nullable();
                $table->json('datos_solicitud')->nullable();
                $table->json('datos_respuesta')->nullable();
                $table->timestamps();
            });
        }

        // =============================================
        // SECUENCIAS PARA POSTGRESQL
        // =============================================
        if (DB::getDriverName() === 'pgsql') {
            $sequences = [
                'unidad_medida_seq', 'tipo_movimiento_seq', 'cliente_seq',
                'categoria_materia_prima_seq', 'proveedor_seq', 'variable_estandar_seq',
                'maquina_seq', 'proceso_seq', 'operador_seq', 'materia_prima_base_seq',
                'materia_prima_seq', 'producto_seq', 'pedido_cliente_seq',
                'producto_pedido_seq', 'destino_pedido_seq', 'producto_destino_pedido_seq',
                'lote_produccion_seq', 'lote_materia_prima_seq', 'registro_movimiento_material_seq',
                'proceso_maquina_seq', 'variable_proceso_maquina_seq', 'registro_proceso_maquina_seq',
                'evaluacion_final_proceso_seq', 'almacenaje_seq', 'solicitud_material_seq',
                'detalle_solicitud_material_seq', 'respuesta_proveedor_seq'
            ];
            
            foreach ($sequences as $seq) {
                DB::statement("CREATE SEQUENCE IF NOT EXISTS {$seq} START WITH 1");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop sequences first (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            $sequences = [
                'respuesta_proveedor_seq', 'detalle_solicitud_material_seq', 'solicitud_material_seq',
                'almacenaje_seq', 'evaluacion_final_proceso_seq', 'registro_proceso_maquina_seq',
                'variable_proceso_maquina_seq', 'proceso_maquina_seq', 'registro_movimiento_material_seq',
                'lote_materia_prima_seq', 'lote_produccion_seq', 'producto_destino_pedido_seq',
                'destino_pedido_seq', 'producto_pedido_seq', 'pedido_cliente_seq', 'producto_seq',
                'materia_prima_seq', 'materia_prima_base_seq', 'operador_seq', 'proceso_seq',
                'maquina_seq', 'variable_estandar_seq', 'proveedor_seq', 'categoria_materia_prima_seq',
                'cliente_seq', 'tipo_movimiento_seq', 'unidad_medida_seq'
            ];
            
            foreach ($sequences as $seq) {
                DB::statement("DROP SEQUENCE IF EXISTS {$seq}");
            }
        }

        // Drop tables in reverse order
        Schema::dropIfExists('seguimiento_envio_pedido');
        Schema::dropIfExists('respuesta_proveedor');
        Schema::dropIfExists('detalle_solicitud_material');
        Schema::dropIfExists('solicitud_material');
        Schema::dropIfExists('almacenaje');
        Schema::dropIfExists('evaluacion_final_proceso');
        Schema::dropIfExists('registro_proceso_maquina');
        Schema::dropIfExists('variable_proceso_maquina');
        Schema::dropIfExists('proceso_maquina');
        Schema::dropIfExists('registro_movimiento_material');
        Schema::dropIfExists('lote_materia_prima');
        Schema::dropIfExists('lote_produccion');
        Schema::dropIfExists('producto_destino_pedido');
        Schema::dropIfExists('destino_pedido');
        Schema::dropIfExists('producto_pedido');
        Schema::dropIfExists('pedido_cliente');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('materia_prima');
        Schema::dropIfExists('materia_prima_base');
        Schema::dropIfExists('operador');
        Schema::dropIfExists('proceso');
        Schema::dropIfExists('maquina');
        Schema::dropIfExists('variable_estandar');
        Schema::dropIfExists('proveedor');
        Schema::dropIfExists('categoria_materia_prima');
        Schema::dropIfExists('cliente');
        Schema::dropIfExists('tipo_movimiento');
        Schema::dropIfExists('unidad_medida');
    }
};

