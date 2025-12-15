<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterialBase extends Model
{
    protected $table = 'materia_prima_base';
    protected $primaryKey = 'material_id';
    public $timestamps = false;
    
    protected $fillable = [
        'material_id',
        'categoria_id',
        'unidad_id',
        'codigo',
        'nombre',
        'descripcion',
        'cantidad_disponible',
        'stock_minimo',
        'stock_maximo',
        'activo'
    ];

    protected $casts = [
        'cantidad_disponible' => 'decimal:4',
        'stock_minimo' => 'decimal:4',
        'stock_maximo' => 'decimal:4',
        'activo' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RawMaterialCategory::class, 'categoria_id', 'categoria_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unidad_id', 'unidad_id');
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'material_id', 'material_id');
    }

    public function batchRawMaterials(): HasMany
    {
        return $this->hasMany(BatchRawMaterial::class, 'material_id', 'material_id');
    }

    public function movementLogs(): HasMany
    {
        return $this->hasMany(MaterialMovementLog::class, 'material_id', 'material_id');
    }
}
