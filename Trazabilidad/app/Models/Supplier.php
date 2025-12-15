<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'proveedor';
    protected $primaryKey = 'proveedor_id';
    public $timestamps = false;
    
    protected $fillable = [
        'proveedor_id',
        'razon_social',
        'nombre_comercial',
        'nit',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'proveedor_id', 'proveedor_id');
    }

    public function supplierResponses(): HasMany
    {
        return $this->hasMany(SupplierResponse::class, 'proveedor_id', 'proveedor_id');
    }
}

