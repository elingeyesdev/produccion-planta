<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    protected $table = 'unidad_medida';
    protected $primaryKey = 'unidad_id';
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
        return $this->hasMany(RawMaterialBase::class, 'unidad_id', 'unidad_id');
    }
}
