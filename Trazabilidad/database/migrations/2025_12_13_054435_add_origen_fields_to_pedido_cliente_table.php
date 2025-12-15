<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedido_cliente', function (Blueprint $table) {
            $table->string('origen_sistema')->nullable()->after('razon_rechazo')->comment('Sistema de origen: almacen o null');
            $table->unsignedBigInteger('pedido_almacen_id')->nullable()->after('origen_sistema')->comment('ID del pedido en sistema-almacen-PSIII');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_cliente', function (Blueprint $table) {
            $table->dropColumn(['origen_sistema', 'pedido_almacen_id']);
        });
    }
};
