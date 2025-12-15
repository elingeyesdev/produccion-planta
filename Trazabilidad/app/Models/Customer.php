<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'cliente';
    protected $primaryKey = 'cliente_id';
    public $timestamps = false;
    
    protected $fillable = [
        'cliente_id',
        'razon_social',
        'nombre_comercial',
        'nit',
        'direccion',
        'telefono',
        'email',
        'contacto',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class, 'cliente_id', 'cliente_id');
    }
}
