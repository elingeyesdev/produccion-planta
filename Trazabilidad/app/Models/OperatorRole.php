<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperatorRole extends Model
{
    protected $table = 'operator_role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    
    protected $fillable = [
        'role_id',
        'code',
        'name',
        'description',
        'access_level',
        'active'
    ];

    protected $casts = [
        'access_level' => 'integer',
        'active' => 'boolean',
    ];

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class, 'role_id', 'role_id');
    }
}
