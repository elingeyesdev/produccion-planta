<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterialCategory extends Model
{
    protected $table = 'categoria_materia_prima';
    protected $primaryKey = 'categoria_id';
    public $timestamps = false;
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function rawMaterialBases(): HasMany
    {
        return $this->hasMany(RawMaterialBase::class, 'categoria_id', 'categoria_id');
    }
}
