<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar campos de precio a productos y pedidos
     */
    public function up(): void
    {
        // Agregar precio_unitario a producto
        if (Schema::hasTable('producto') && !Schema::hasColumn('producto', 'precio_unitario')) {
            Schema::table('producto', function (Blueprint $table) {
                $table->decimal('precio_unitario', 15, 2)->nullable()->after('peso')->comment('Precio unitario del producto');
            });
        }

        // Agregar precio a producto_pedido (precio total por cantidad)
        if (Schema::hasTable('producto_pedido') && !Schema::hasColumn('producto_pedido', 'precio')) {
            Schema::table('producto_pedido', function (Blueprint $table) {
                $table->decimal('precio', 15, 2)->nullable()->after('cantidad')->comment('Precio total (precio_unitario * cantidad)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('producto_pedido') && Schema::hasColumn('producto_pedido', 'precio')) {
            Schema::table('producto_pedido', function (Blueprint $table) {
                $table->dropColumn('precio');
            });
        }

        if (Schema::hasTable('producto') && Schema::hasColumn('producto', 'precio_unitario')) {
            Schema::table('producto', function (Blueprint $table) {
                $table->dropColumn('precio_unitario');
            });
        }
    }
};

