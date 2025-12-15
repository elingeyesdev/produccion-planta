<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $table = 'solicitud_material';
    protected $primaryKey = 'solicitud_id';
    public $timestamps = false;
    
    protected $fillable = [
        'solicitud_id',
        'pedido_id',
        'numero_solicitud',
        'fecha_solicitud',
        'fecha_requerida',
        'observaciones',
        'direccion',
        'latitud',
        'longitud'
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_requerida' => 'date',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'pedido_id', 'pedido_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(MaterialRequestDetail::class, 'solicitud_id', 'solicitud_id');
    }

    public function supplierResponses(): HasMany
    {
        return $this->hasMany(SupplierResponse::class, 'solicitud_id', 'solicitud_id');
    }
}
