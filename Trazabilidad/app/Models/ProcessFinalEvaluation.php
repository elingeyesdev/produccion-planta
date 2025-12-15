<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessFinalEvaluation extends Model
{
    protected $table = 'evaluacion_final_proceso';
    protected $primaryKey = 'evaluacion_id';
    public $timestamps = false;
    
    protected $fillable = [
        'evaluacion_id',
        'lote_id',
        'inspector_id',
        'razon',
        'observaciones',
        'fecha_evaluacion'
    ];

    protected $casts = [
        'fecha_evaluacion' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'lote_id', 'lote_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'inspector_id', 'operador_id');
    }
}
