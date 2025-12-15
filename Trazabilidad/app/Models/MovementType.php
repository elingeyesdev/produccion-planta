<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovementType extends Model
{
    protected $table = 'movement_type';
    protected $primaryKey = 'movement_type_id';
    public $timestamps = false;
    
    protected $fillable = [
        'code',
        'name',
        'affects_stock',
        'is_entry',
        'active'
    ];

    protected $casts = [
        'affects_stock' => 'boolean',
        'is_entry' => 'boolean',
        'active' => 'boolean',
    ];

    public function movementLogs(): HasMany
    {
        return $this->hasMany(MaterialMovementLog::class, 'movement_type_id', 'movement_type_id');
    }
}
