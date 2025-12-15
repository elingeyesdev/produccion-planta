<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDestination extends Model
{
    protected $table = 'destino_pedido';
    protected $primaryKey = 'destino_id';
    public $timestamps = true;

    protected $fillable = [
        'destino_id',
        'pedido_id',
        'direccion',
        'referencia',
        'latitud',
        'longitud',
        'nombre_contacto',
        'telefono_contacto',
        'instrucciones_entrega',
        'almacen_origen_id',
        'almacen_origen_nombre',
        'almacen_destino_id',
        'almacen_destino_nombre',
        'almacen_almacen_id'
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'pedido_id', 'pedido_id');
    }

    public function destinationProducts(): HasMany
    {
        return $this->hasMany(OrderDestinationProduct::class, 'destino_id', 'destino_id');
    }
}

