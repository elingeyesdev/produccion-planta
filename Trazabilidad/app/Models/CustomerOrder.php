<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $table = 'pedido_cliente';
    protected $primaryKey = 'pedido_id';
    public $timestamps = false;
    
    protected $fillable = [
        'pedido_id',
        'cliente_id',
        'numero_pedido',
        'nombre',
        'estado',
        'fecha_creacion',
        'fecha_entrega',
        'descripcion',
        'observaciones',
        'editable_hasta',
        'aprobado_en',
        'aprobado_por',
        'razon_rechazo',
        'origen_sistema',
        'pedido_almacen_id'
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_entrega' => 'date',
        'editable_hasta' => 'datetime',
        'aprobado_en' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cliente_id', 'cliente_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class, 'pedido_id', 'pedido_id');
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(MaterialRequest::class, 'pedido_id', 'pedido_id');
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'pedido_id', 'pedido_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(OrderDestination::class, 'pedido_id', 'pedido_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'aprobado_por', 'operador_id');
    }

    /**
     * Relación con los envíos creados en plantaCruds
     */
    public function envioTrackings(): HasMany
    {
        return $this->hasMany(OrderEnvioTracking::class, 'pedido_id', 'pedido_id');
    }

    /**
     * Obtener el primer envío exitoso de plantaCruds
     */
    public function getFirstEnvioTracking()
    {
        return $this->envioTrackings()
            ->where('estado', 'success')
            ->whereNotNull('envio_id')
            ->first();
    }

    /**
     * Obtener la URL del PDF de propuesta de vehículos en plantaCruds
     * 
     * @return string|null URL del endpoint o null si no hay envío
     */
    public function getPropuestaVehiculosPdfUrl(): ?string
    {
        $tracking = $this->getFirstEnvioTracking();
        if (!$tracking || !$tracking->envio_id) {
            return null;
        }

        $apiUrl = config('services.plantacruds.api_url', 'http://localhost:8001/api');
        return "{$apiUrl}/envios/{$tracking->envio_id}/propuesta-vehiculos-pdf";
    }

    /**
     * Obtener la URL del endpoint para aprobar/rechazar en plantaCruds
     * 
     * @return string|null URL del endpoint o null si no hay envío
     */
    public function getAprobarRechazarUrl(): ?string
    {
        $tracking = $this->getFirstEnvioTracking();
        if (!$tracking || !$tracking->envio_id) {
            return null;
        }

        $apiUrl = config('services.plantacruds.api_url', 'http://localhost:8001/api');
        return "{$apiUrl}/envios/{$tracking->envio_id}/aprobar-rechazar";
    }

    /**
     * Obtener el ID del envío en plantaCruds
     * 
     * @return int|null ID del envío o null si no hay envío
     */
    public function getPlantaCrudsEnvioId(): ?int
    {
        $tracking = $this->getFirstEnvioTracking();
        return $tracking ? $tracking->envio_id : null;
    }

    /**
     * Verificar si el envío en plantaCruds está en estado "pendiente_aprobacion_trazabilidad"
     * 
     * @return bool true si el envío está pendiente de aprobación, false en caso contrario
     */
    public function isEnvioPendienteAprobacionTrazabilidad(): bool
    {
        $envioId = $this->getPlantaCrudsEnvioId();
        if (!$envioId) {
            \Illuminate\Support\Facades\Log::debug("No hay envioId para pedido {$this->pedido_id}");
            return false;
        }

        try {
            $apiUrl = config('services.plantacruds.api_url', 'http://localhost:8001/api');
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get("{$apiUrl}/envios/{$envioId}");
            
            if ($response->successful()) {
                $envio = $response->json();
                // La respuesta puede venir como {'data': {...}} o directamente {...}
                $estado = $envio['data']['estado'] ?? $envio['estado'] ?? null;
                
                \Illuminate\Support\Facades\Log::debug("Estado del envío {$envioId}: {$estado}", [
                    'envio_response' => $envio,
                    'estado_encontrado' => $estado,
                ]);
                
                $resultado = $estado === 'pendiente_aprobacion_trazabilidad';
                \Illuminate\Support\Facades\Log::debug("Resultado de verificación: " . ($resultado ? 'true' : 'false'));
                
                return $resultado;
            } else {
                \Illuminate\Support\Facades\Log::warning("Error al obtener envío {$envioId}: HTTP {$response->status()}", [
                    'response_body' => $response->body(),
                ]);
                // Si hay error, no mostramos los botones por seguridad
                return false;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Illuminate\Support\Facades\Log::warning("Timeout al verificar estado del envío {$envioId}: " . $e->getMessage());
            // Si hay timeout, no mostramos los botones por seguridad
            return false;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error al verificar estado del envío {$envioId}: " . $e->getMessage());
            // Si hay error, no mostramos los botones por seguridad
            return false;
        }
    }

    /**
     * Verifica si el pedido puede ser editado o cancelado
     */
    public function canBeEdited(): bool
    {
        if ($this->estado !== 'pendiente') {
            return false;
        }

        if ($this->editable_hasta && now()->greaterThan($this->editable_hasta)) {
            return false;
        }

        return true;
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'pedido_id';
    }

    /**
     * Retrieve the model for bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        return $this->where($field, $value)->first();
    }
}
