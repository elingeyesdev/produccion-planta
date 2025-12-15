<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardVariable extends Model
{
    protected $table = 'variable_estandar';
    protected $primaryKey = 'variable_id';
    public $timestamps = false;
    
    protected $fillable = [
        'variable_id',
        'codigo',
        'nombre',
        'unidad',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function processMachineVariables(): HasMany
    {
        return $this->hasMany(ProcessMachineVariable::class, 'variable_estandar_id', 'variable_id');
    }
}
