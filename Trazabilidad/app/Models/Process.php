<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    protected $table = 'proceso';
    protected $primaryKey = 'proceso_id';
    public $timestamps = false;
    
    protected $fillable = [
        'proceso_id',
        'codigo',
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function processMachines(): HasMany
    {
        return $this->hasMany(ProcessMachine::class, 'proceso_id', 'proceso_id');
    }
}

