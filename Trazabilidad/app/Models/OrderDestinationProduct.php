<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDestinationProduct extends Model
{
    protected $table = 'producto_destino_pedido';
    protected $primaryKey = 'producto_destino_id';
    public $timestamps = true;
    
    protected $fillable = [
        'producto_destino_id',
        'destino_id',
        'producto_pedido_id',
        'cantidad',
        'observaciones'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(OrderDestination::class, 'destino_id', 'destino_id');
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'producto_pedido_id', 'producto_pedido_id');
    }
}

