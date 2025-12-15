<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migración de datos: Copiar datos de tablas en inglés a tablas en español
     * 
     * Este script migra los datos existentes antes de eliminar las tablas antiguas
     */
    public function up(): void
    {
        // Solo ejecutar si existen las tablas antiguas
        if (!Schema::hasTable('unidad_medida') && Schema::hasTable('unit_of_measure')) {
            // Migrar unidad_medida
            DB::statement("
                INSERT INTO unidad_medida (unidad_id, codigo, nombre, descripcion, activo)
                SELECT unit_id, code, name, description, active
                FROM unit_of_measure
            ");
        }

        if (!Schema::hasTable('tipo_movimiento') && Schema::hasTable('movement_type')) {
            // Migrar tipo_movimiento
            DB::statement("
                INSERT INTO tipo_movimiento (tipo_movimiento_id, codigo, nombre, afecta_stock, es_entrada, activo)
                SELECT movement_type_id, code, name, affects_stock, is_entry, active
                FROM movement_type
            ");
        }

        if (!Schema::hasTable('cliente') && Schema::hasTable('customer')) {
            // Migrar cliente
            DB::statement("
                INSERT INTO cliente (cliente_id, razon_social, nombre_comercial, nit, direccion, telefono, email, contacto, activo)
                SELECT customer_id, business_name, trading_name, tax_id, address, phone, email, contact_person, active
                FROM customer
            ");
        }

        if (!Schema::hasTable('categoria_materia_prima') && Schema::hasTable('raw_material_category')) {
            // Migrar categoria_materia_prima
            DB::statement("
                INSERT INTO categoria_materia_prima (categoria_id, codigo, nombre, descripcion, activo)
                SELECT category_id, code, name, description, active
                FROM raw_material_category
            ");
        }

        if (!Schema::hasTable('proveedor') && Schema::hasTable('supplier')) {
            // Migrar proveedor
            DB::statement("
                INSERT INTO proveedor (proveedor_id, razon_social, nombre_comercial, nit, contacto, telefono, email, direccion, activo)
                SELECT supplier_id, business_name, trading_name, tax_id, contact_person, phone, email, address, active
                FROM supplier
            ");
        }

        if (!Schema::hasTable('variable_estandar') && Schema::hasTable('standard_variable')) {
            // Migrar variable_estandar
            DB::statement("
                INSERT INTO variable_estandar (variable_id, codigo, nombre, unidad, descripcion, activo)
                SELECT variable_id, code, name, unit, description, active
                FROM standard_variable
            ");
        }

        if (!Schema::hasTable('maquina') && Schema::hasTable('machine')) {
            // Migrar maquina
            DB::statement("
                INSERT INTO maquina (maquina_id, codigo, nombre, descripcion, imagen_url, activo)
                SELECT machine_id, code, name, description, image_url, active
                FROM machine
            ");
        }

        if (!Schema::hasTable('proceso') && Schema::hasTable('process')) {
            // Migrar proceso
            DB::statement("
                INSERT INTO proceso (proceso_id, codigo, nombre, descripcion, activo)
                SELECT process_id, code, name, description, active
                FROM process
            ");
        }

        if (!Schema::hasTable('operador') && Schema::hasTable('operator')) {
            // Migrar operador (sin role_id porque Spatie maneja los roles)
            DB::statement("
                INSERT INTO operador (operador_id, nombre, apellido, usuario, password_hash, email, activo)
                SELECT operator_id, first_name, last_name, username, password_hash, email, active
                FROM operator
            ");
        }

        if (!Schema::hasTable('materia_prima_base') && Schema::hasTable('raw_material_base')) {
            // Migrar materia_prima_base
            DB::statement("
                INSERT INTO materia_prima_base (material_id, categoria_id, unidad_id, codigo, nombre, descripcion, cantidad_disponible, stock_minimo, stock_maximo, activo)
                SELECT material_id, category_id, unit_id, code, name, description, available_quantity, minimum_stock, maximum_stock, active
                FROM raw_material_base
            ");
        }

        if (!Schema::hasTable('materia_prima') && Schema::hasTable('raw_material')) {
            // Migrar materia_prima
            DB::statement("
                INSERT INTO materia_prima (materia_prima_id, material_id, proveedor_id, lote_proveedor, numero_factura, fecha_recepcion, fecha_vencimiento, cantidad, cantidad_disponible, conformidad_recepcion, observaciones)
                SELECT raw_material_id, material_id, supplier_id, supplier_batch, invoice_number, receipt_date, expiration_date, quantity, available_quantity, receipt_conformity, observations
                FROM raw_material
            ");
        }

        if (!Schema::hasTable('producto') && Schema::hasTable('product')) {
            // Migrar producto
            DB::statement("
                INSERT INTO producto (producto_id, codigo, nombre, tipo, peso, unidad_id, descripcion, activo, created_at, updated_at)
                SELECT product_id, code, name, type, weight, unit_id, description, active, created_at, updated_at
                FROM product
            ");
        }

        if (!Schema::hasTable('pedido_cliente') && Schema::hasTable('customer_order')) {
            // Migrar pedido_cliente
            DB::statement("
                INSERT INTO pedido_cliente (pedido_id, cliente_id, numero_pedido, nombre, cantidad, estado, fecha_creacion, fecha_entrega, prioridad, descripcion, observaciones, editable_hasta, aprobado_en, aprobado_por, razon_rechazo)
                SELECT order_id, customer_id, order_number, name, quantity, status, creation_date, delivery_date, priority, description, observations, editable_until, approved_at, approved_by, rejection_reason
                FROM customer_order
            ");
        }

        if (!Schema::hasTable('producto_pedido') && Schema::hasTable('order_product')) {
            // Migrar producto_pedido
            DB::statement("
                INSERT INTO producto_pedido (producto_pedido_id, pedido_id, producto_id, cantidad, estado, razon_rechazo, aprobado_por, aprobado_en, observaciones, created_at, updated_at)
                SELECT order_product_id, order_id, product_id, quantity, status, rejection_reason, approved_by, approved_at, observations, created_at, updated_at
                FROM order_product
            ");
        }

        if (!Schema::hasTable('destino_pedido') && Schema::hasTable('order_destination')) {
            // Migrar destino_pedido
            DB::statement("
                INSERT INTO destino_pedido (destino_id, pedido_id, direccion, referencia, latitud, longitud, nombre_contacto, telefono_contacto, instrucciones_entrega, almacen_origen_id, almacen_origen_nombre, almacen_destino_id, almacen_destino_nombre, created_at, updated_at)
                SELECT destination_id, order_id, address, reference, latitude, longitude, contact_name, contact_phone, delivery_instructions, almacen_origen_id, almacen_origen_nombre, almacen_destino_id, almacen_destino_nombre, created_at, updated_at
                FROM order_destination
            ");
        }

        if (!Schema::hasTable('producto_destino_pedido') && Schema::hasTable('order_destination_product')) {
            // Migrar producto_destino_pedido
            DB::statement("
                INSERT INTO producto_destino_pedido (producto_destino_id, destino_id, producto_pedido_id, cantidad, observaciones, created_at, updated_at)
                SELECT destination_product_id, destination_id, order_product_id, quantity, observations, created_at, updated_at
                FROM order_destination_product
            ");
        }

        if (!Schema::hasTable('lote_produccion') && Schema::hasTable('production_batch')) {
            // Migrar lote_produccion
            DB::statement("
                INSERT INTO lote_produccion (lote_id, pedido_id, codigo_lote, nombre, fecha_creacion, hora_inicio, hora_fin, cantidad_objetivo, cantidad_producida, observaciones)
                SELECT batch_id, order_id, batch_code, name, creation_date, start_time, end_time, target_quantity, produced_quantity, observations
                FROM production_batch
            ");
        }

        if (!Schema::hasTable('lote_materia_prima') && Schema::hasTable('batch_raw_material')) {
            // Migrar lote_materia_prima
            DB::statement("
                INSERT INTO lote_materia_prima (lote_material_id, lote_id, materia_prima_id, cantidad_planificada, cantidad_usada)
                SELECT batch_material_id, batch_id, raw_material_id, planned_quantity, used_quantity
                FROM batch_raw_material
            ");
        }

        if (!Schema::hasTable('registro_movimiento_material') && Schema::hasTable('material_movement_log')) {
            // Migrar registro_movimiento_material
            DB::statement("
                INSERT INTO registro_movimiento_material (registro_id, material_id, tipo_movimiento_id, operador_id, cantidad, saldo_anterior, saldo_nuevo, descripcion, observaciones, fecha_movimiento)
                SELECT log_id, material_id, movement_type_id, user_id, quantity, previous_balance, new_balance, description, observations, movement_date
                FROM material_movement_log
            ");
        }

        if (!Schema::hasTable('proceso_maquina') && Schema::hasTable('process_machine')) {
            // Migrar proceso_maquina
            DB::statement("
                INSERT INTO proceso_maquina (proceso_maquina_id, proceso_id, maquina_id, orden_paso, nombre, descripcion, tiempo_estimado)
                SELECT process_machine_id, process_id, machine_id, step_order, name, description, estimated_time
                FROM process_machine
            ");
        }

        if (!Schema::hasTable('variable_proceso_maquina') && Schema::hasTable('process_machine_variable')) {
            // Migrar variable_proceso_maquina
            DB::statement("
                INSERT INTO variable_proceso_maquina (variable_id, proceso_maquina_id, variable_estandar_id, valor_minimo, valor_maximo, valor_objetivo, obligatorio)
                SELECT variable_id, process_machine_id, standard_variable_id, min_value, max_value, target_value, mandatory
                FROM process_machine_variable
            ");
        }

        if (!Schema::hasTable('registro_proceso_maquina') && Schema::hasTable('process_machine_record')) {
            // Migrar registro_proceso_maquina
            DB::statement("
                INSERT INTO registro_proceso_maquina (registro_id, lote_id, proceso_maquina_id, operador_id, variables_ingresadas, cumple_estandar, observaciones, hora_inicio, hora_fin, fecha_registro)
                SELECT record_id, batch_id, process_machine_id, operator_id, entered_variables, meets_standard, observations, start_time, end_time, record_date
                FROM process_machine_record
            ");
        }

        if (!Schema::hasTable('evaluacion_final_proceso') && Schema::hasTable('process_final_evaluation')) {
            // Migrar evaluacion_final_proceso
            DB::statement("
                INSERT INTO evaluacion_final_proceso (evaluacion_id, lote_id, inspector_id, razon, observaciones, fecha_evaluacion)
                SELECT evaluation_id, batch_id, inspector_id, reason, observations, evaluation_date
                FROM process_final_evaluation
            ");
        }

        if (!Schema::hasTable('almacenaje') && Schema::hasTable('storage')) {
            // Migrar almacenaje
            DB::statement("
                INSERT INTO almacenaje (almacenaje_id, lote_id, ubicacion, condicion, cantidad, observaciones, latitud_recojo, longitud_recojo, direccion_recojo, referencia_recojo, fecha_almacenaje, fecha_retiro)
                SELECT storage_id, batch_id, location, condition, quantity, observations, pickup_latitude, pickup_longitude, pickup_address, pickup_reference, storage_date, retrieval_date
                FROM storage
            ");
        }

        if (!Schema::hasTable('solicitud_material') && Schema::hasTable('material_request')) {
            // Migrar solicitud_material
            DB::statement("
                INSERT INTO solicitud_material (solicitud_id, pedido_id, numero_solicitud, fecha_solicitud, fecha_requerida, prioridad, observaciones)
                SELECT request_id, order_id, request_number, request_date, required_date, priority, observations
                FROM material_request
            ");
        }

        if (!Schema::hasTable('detalle_solicitud_material') && Schema::hasTable('material_request_detail')) {
            // Migrar detalle_solicitud_material
            DB::statement("
                INSERT INTO detalle_solicitud_material (detalle_id, solicitud_id, material_id, cantidad_solicitada, cantidad_aprobada)
                SELECT detail_id, request_id, material_id, requested_quantity, approved_quantity
                FROM material_request_detail
            ");
        }

        if (!Schema::hasTable('respuesta_proveedor') && Schema::hasTable('supplier_response')) {
            // Migrar respuesta_proveedor
            DB::statement("
                INSERT INTO respuesta_proveedor (respuesta_id, solicitud_id, proveedor_id, fecha_respuesta, cantidad_confirmada, fecha_entrega, observaciones, precio)
                SELECT response_id, request_id, supplier_id, response_date, confirmed_quantity, delivery_date, observations, price
                FROM supplier_response
            ");
        }

        if (!Schema::hasTable('seguimiento_envio_pedido') && Schema::hasTable('order_envio_tracking')) {
            // Migrar seguimiento_envio_pedido
            DB::statement("
                INSERT INTO seguimiento_envio_pedido (id, pedido_id, destino_id, envio_id, codigo_envio, estado, mensaje_error, datos_solicitud, datos_respuesta, created_at, updated_at)
                SELECT id, order_id, destination_id, envio_id, envio_codigo, status, error_message, request_data, response_data, created_at, updated_at
                FROM order_envio_tracking
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada en down porque esta migración solo copia datos
        // Las tablas antiguas se eliminarán en otra migración
    }
};

