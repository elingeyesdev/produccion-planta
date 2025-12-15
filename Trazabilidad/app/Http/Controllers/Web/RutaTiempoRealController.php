<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RutaTiempoRealController extends Controller
{
    /**
     * Mostrar el mapa de rutas en tiempo real
     * Consume la API de plantaCruds para obtener los envíos activos
     */
    public function index()
    {
        // URL de la API de plantaCruds
        $plantaCrudsApiUrl = env('PLANTA_CRUDS_API_URL', 'http://localhost:8001');
        
        return view('rutas-tiempo-real.index', compact('plantaCrudsApiUrl'));
    }
}

