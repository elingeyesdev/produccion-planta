<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessMachineRecord extends Model
{
    protected $table = 'registro_proceso_maquina';
    protected $primaryKey = 'registro_id';
    public $timestamps = false;
    
    protected $fillable = [
        'registro_id',
        'lote_id',
        'proceso_maquina_id',
        'operador_id',
        'variables_ingresadas',
        'cumple_estandar',
        'observaciones',
        'hora_inicio',
        'hora_fin',
        'fecha_registro'
    ];

    protected $casts = [
        'variables_ingresadas' => 'array',
        'cumple_estandar' => 'boolean',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'fecha_registro' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'lote_id', 'lote_id');
    }

    public function processMachine(): BelongsTo
    {
        return $this->belongsTo(ProcessMachine::class, 'proceso_maquina_id', 'proceso_maquina_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operador_id', 'operador_id');
    }
}
