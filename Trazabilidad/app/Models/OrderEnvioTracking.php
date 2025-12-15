<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEnvioTracking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seguimiento_envio_pedido';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pedido_id',
        'destino_id',
        'envio_id',
        'codigo_envio',
        'estado',
        'mensaje_error',
        'datos_solicitud',
        'datos_respuesta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'datos_solicitud' => 'array',
        'datos_respuesta' => 'array',
    ];

    /**
     * Get the customer order that owns the tracking record.
     */
    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'pedido_id', 'pedido_id');
    }

    /**
     * Get the destination that owns the tracking record.
     */
    public function destination()
    {
        return $this->belongsTo(OrderDestination::class, 'destino_id', 'destino_id');
    }

    /**
     * Scope a query to only include successful trackings.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('estado', 'success');
    }

    /**
     * Scope a query to only include failed trackings.
     */
    public function scopeFailed($query)
    {
        return $query->where('estado', 'failed');
    }

    /**
     * Scope a query to only include pending trackings.
     */
    public function scopePending($query)
    {
        return $query->where('estado', 'pending');
    }
}
