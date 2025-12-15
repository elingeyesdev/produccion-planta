<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $table = 'materia_prima';
    protected $primaryKey = 'materia_prima_id';
    public $timestamps = false;
    
    protected $fillable = [
        'materia_prima_id',
        'material_id',
        'proveedor_id',
        'lote_proveedor',
        'numero_factura',
        'fecha_recepcion',
        'fecha_vencimiento',
        'cantidad',
        'cantidad_disponible',
        'conformidad_recepcion',
        'observaciones'
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_vencimiento' => 'date',
        'cantidad' => 'decimal:4',
        'cantidad_disponible' => 'decimal:4',
        'conformidad_recepcion' => 'boolean',
    ];

    public function materialBase(): BelongsTo
    {
        return $this->belongsTo(RawMaterialBase::class, 'material_id', 'material_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'proveedor_id', 'proveedor_id');
    }

    public function batchRawMaterials(): HasMany
    {
        return $this->hasMany(BatchRawMaterial::class, 'materia_prima_id', 'materia_prima_id');
    }
}
