<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Storage extends Model
{
    protected $table = 'almacenaje';
    protected $primaryKey = 'almacenaje_id';
    public $timestamps = false;
    
    protected $fillable = [
        'almacenaje_id',
        'lote_id',
        'ubicacion',
        'condicion',
        'cantidad',
        'observaciones',
        'latitud_recojo',
        'longitud_recojo',
        'direccion_recojo',
        'referencia_recojo',
        'fecha_almacenaje',
        'fecha_retiro'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'latitud_recojo' => 'decimal:8',
        'longitud_recojo' => 'decimal:8',
        'fecha_almacenaje' => 'datetime',
        'fecha_retiro' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'lote_id', 'lote_id');
    }
}
