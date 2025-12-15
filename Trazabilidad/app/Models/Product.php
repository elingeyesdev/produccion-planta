<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'producto_id';
    public $timestamps = true;
    
    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'peso',
        'precio_unitario',
        'unidad_id',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'peso' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unidad_id', 'unidad_id');
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'producto_id', 'producto_id');
    }
}












