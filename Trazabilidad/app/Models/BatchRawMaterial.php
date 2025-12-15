<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRawMaterial extends Model
{
    protected $table = 'lote_materia_prima';
    protected $primaryKey = 'lote_material_id';
    public $timestamps = false;
    
    protected $fillable = [
        'lote_material_id',
        'lote_id',
        'materia_prima_id',
        'cantidad_planificada',
        'cantidad_usada'
    ];

    protected $casts = [
        'cantidad_planificada' => 'decimal:4',
        'cantidad_usada' => 'decimal:4',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'lote_id', 'lote_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'materia_prima_id', 'materia_prima_id');
    }
}
