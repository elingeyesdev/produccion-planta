<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialMovementLog extends Model
{
    protected $table = 'material_movement_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    
    protected $fillable = [
        'log_id',
        'material_id',
        'movement_type_id',
        'user_id',
        'quantity',
        'previous_balance',
        'new_balance',
        'description',
        'movement_date'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'previous_balance' => 'decimal:4',
        'new_balance' => 'decimal:4',
        'movement_date' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterialBase::class, 'material_id', 'material_id');
    }

    public function movementType(): BelongsTo
    {
        return $this->belongsTo(MovementType::class, 'movement_type_id', 'movement_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'user_id', 'operator_id');
    }
}
