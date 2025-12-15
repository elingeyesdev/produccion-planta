<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierResponse extends Model
{
    protected $table = 'supplier_response';
    protected $primaryKey = 'response_id';
    public $timestamps = false;
    
    protected $fillable = [
        'request_id',
        'supplier_id',
        'response_date',
        'confirmed_quantity',
        'delivery_date',
        'observations',
        'price'
    ];

    protected $casts = [
        'response_date' => 'datetime',
        'confirmed_quantity' => 'decimal:4',
        'delivery_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'request_id', 'request_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }
}
