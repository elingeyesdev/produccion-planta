<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessMachineVariable extends Model
{
    protected $table = 'variable_proceso_maquina';
    protected $primaryKey = 'variable_id';
    public $timestamps = false;
    
    protected $fillable = [
        'variable_id',
        'proceso_maquina_id',
        'variable_estandar_id',
        'valor_minimo',
        'valor_maximo',
        'valor_objetivo',
        'obligatorio'
    ];

    protected $casts = [
        'valor_minimo' => 'decimal:2',
        'valor_maximo' => 'decimal:2',
        'valor_objetivo' => 'decimal:2',
        'obligatorio' => 'boolean',
    ];

    public function processMachine(): BelongsTo
    {
        return $this->belongsTo(ProcessMachine::class, 'proceso_maquina_id', 'proceso_maquina_id');
    }

    public function standardVariable(): BelongsTo
    {
        return $this->belongsTo(StandardVariable::class, 'variable_estandar_id', 'variable_id');
    }
}
