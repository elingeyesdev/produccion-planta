<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $table = 'maquina';
    protected $primaryKey = 'maquina_id';
    public $timestamps = false;
    
    protected $fillable = [
        'maquina_id',
        'codigo',
        'nombre',
        'descripcion',
        'imagen_url',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'operador_maquina', 'maquina_id', 'operador_id');
    }

    public function processMachines(): HasMany
    {
        return $this->hasMany(ProcessMachine::class, 'maquina_id', 'maquina_id');
    }
}

