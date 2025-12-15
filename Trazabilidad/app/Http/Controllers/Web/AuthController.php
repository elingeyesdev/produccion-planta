<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $operator = Operator::where('usuario', $request->username)->first();

        if (!$operator || !Hash::check($request->password, $operator->password_hash)) {
            return redirect()->back()
                ->with('error', 'Credenciales inválidas')
                ->withInput();
        }

        if (!$operator->activo) {
            return redirect()->back()
                ->with('error', 'Usuario inactivo')
                ->withInput();
        }

        // Autenticar usando el guard 'web' para sesión
        Auth::guard('web')->login($operator);

        // Redirigir según el rol de Spatie
        if ($operator->hasRole('cliente')) {
            return redirect()->route('dashboard-cliente');
        }

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}

