<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Register a new operator
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'usuario' => 'required|string|max:60|unique:operador,usuario',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Generate ID manually if needed (checking max ID)
            $maxId = Operator::max('operador_id') ?? 0;
            $nextId = $maxId + 1;

            $operator = Operator::create([
                'operador_id' => $nextId,
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'usuario' => $request->usuario,
                'password_hash' => Hash::make($request->password),
                'email' => $request->email,
                'activo' => true
            ]);

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'operador_id' => $operator->operador_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login operator
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $credentials = $request->only('username', 'password');
        
        // Find operator by usuario (Spanish column name)
        $operator = Operator::where('usuario', $credentials['username'])->first();
        
        if (!$operator || !Hash::check($credentials['password'], $operator->password_hash)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        try {
            $token = JWTAuth::fromUser($operator);
            
            return response()->json([
                'token' => $token,
                'operator' => [
                    'operador_id' => $operator->operador_id,
                    'nombre' => $operator->nombre,
                    'apellido' => $operator->apellido,
                    'usuario' => $operator->usuario,
                    'email' => $operator->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated operator
     */
    public function me(): JsonResponse
    {
        try {
            $operator = auth()->user();
            
            if (!$operator) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'operador_id' => $operator->operador_id,
                'nombre' => $operator->nombre,
                'apellido' => $operator->apellido,
                'usuario' => $operator->usuario,
                'email' => $operator->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout operator
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

