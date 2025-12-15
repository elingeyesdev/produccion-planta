<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionBatch extends Model
{
    protected $table = 'lote_produccion';
    protected $primaryKey = 'lote_id';
    public $timestamps = false;
    
    protected $fillable = [
        'lote_id',
        'pedido_id',
        'codigo_lote',
        'nombre',
        'fecha_creacion',
        'hora_inicio',
        'hora_fin',
        'cantidad_objetivo',
        'cantidad_producida',
        'observaciones'
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'cantidad_objetivo' => 'decimal:4',
        'cantidad_producida' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'pedido_id', 'pedido_id');
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(BatchRawMaterial::class, 'lote_id', 'lote_id');
    }

    public function processMachineRecords(): HasMany
    {
        return $this->hasMany(ProcessMachineRecord::class, 'lote_id', 'lote_id');
    }

    public function finalEvaluation(): HasMany
    {
        return $this->hasMany(ProcessFinalEvaluation::class, 'lote_id', 'lote_id');
    }

    public function latestFinalEvaluation(): HasOne
    {
        return $this->hasOne(ProcessFinalEvaluation::class, 'lote_id', 'lote_id')
            ->latest('fecha_evaluacion');
    }

    public function storage(): HasMany
    {
        return $this->hasMany(Storage::class, 'lote_id', 'lote_id');
    }
}
