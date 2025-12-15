<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestDetail extends Model
{
    protected $table = 'detalle_solicitud_material';
    protected $primaryKey = 'detalle_id';
    public $timestamps = false;
    
    protected $fillable = [
        'detalle_id',
        'solicitud_id',
        'material_id',
        'cantidad_solicitada',
        'cantidad_aprobada'
    ];

    protected $casts = [
        'cantidad_solicitada' => 'decimal:4',
        'cantidad_aprobada' => 'decimal:4',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'solicitud_id', 'solicitud_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterialBase::class, 'material_id', 'material_id');
    }
}
