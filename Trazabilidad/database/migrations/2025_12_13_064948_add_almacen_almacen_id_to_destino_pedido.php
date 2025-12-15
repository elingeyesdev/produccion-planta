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
        Schema::table('destino_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('almacen_almacen_id')->nullable()->after('almacen_destino_nombre')->comment('ID del almacén en sistema-almacen-PSIII');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destino_pedido', function (Blueprint $table) {
            $table->dropColumn('almacen_almacen_id');
        });
    }
};
