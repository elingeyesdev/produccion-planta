<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessMachine extends Model
{
    protected $table = 'proceso_maquina';
    protected $primaryKey = 'proceso_maquina_id';
    public $timestamps = false;
    
    protected $fillable = [
        'proceso_maquina_id',
        'proceso_id',
        'maquina_id',
        'orden_paso',
        'nombre',
        'descripcion',
        'tiempo_estimado'
    ];

    protected $casts = [
        'orden_paso' => 'integer',
        'tiempo_estimado' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'proceso_id', 'proceso_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'maquina_id', 'maquina_id');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(ProcessMachineVariable::class, 'proceso_maquina_id', 'proceso_maquina_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(ProcessMachineRecord::class, 'proceso_maquina_id', 'proceso_maquina_id');
    }
}
