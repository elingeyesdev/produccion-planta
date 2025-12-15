<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Eliminar campo prioridad de todas las tablas
     */
    public function up(): void
    {
        // Eliminar prioridad de pedido_cliente
        if (Schema::hasTable('pedido_cliente') && Schema::hasColumn('pedido_cliente', 'prioridad')) {
            Schema::table('pedido_cliente', function (Blueprint $table) {
                $table->dropColumn('prioridad');
            });
        }

        // Eliminar prioridad de solicitud_material
        if (Schema::hasTable('solicitud_material') && Schema::hasColumn('solicitud_material', 'prioridad')) {
            Schema::table('solicitud_material', function (Blueprint $table) {
                $table->dropColumn('prioridad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pedido_cliente') && !Schema::hasColumn('pedido_cliente', 'prioridad')) {
            Schema::table('pedido_cliente', function (Blueprint $table) {
                $table->integer('prioridad')->default(1)->after('fecha_entrega');
            });
        }

        if (Schema::hasTable('solicitud_material') && !Schema::hasColumn('solicitud_material', 'prioridad')) {
            Schema::table('solicitud_material', function (Blueprint $table) {
                $table->integer('prioridad')->default(1)->after('fecha_requerida');
            });
        }
    }
};

