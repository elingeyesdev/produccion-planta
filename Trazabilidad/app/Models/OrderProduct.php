<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderProduct extends Model
{
    protected $table = 'producto_pedido';
    protected $primaryKey = 'producto_pedido_id';
    public $timestamps = true;
    
    protected $fillable = [
        'producto_pedido_id',
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio',
        'estado',
        'razon_rechazo',
        'aprobado_por',
        'aprobado_en',
        'observaciones'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio' => 'decimal:2',
        'aprobado_en' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'pedido_id', 'pedido_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id', 'producto_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'aprobado_por', 'operador_id');
    }

    public function destinationProducts(): HasMany
    {
        return $this->hasMany(OrderDestinationProduct::class, 'producto_pedido_id', 'producto_pedido_id');
    }
}

