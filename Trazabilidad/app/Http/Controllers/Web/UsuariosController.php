<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $query = Operator::with('roles');
        
        // Filtro por rol
        if ($request->has('rol') && $request->rol) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->rol);
            });
        }
        
        // Filtro por estado
        if ($request->has('estado') && $request->estado) {
            if ($request->estado === 'activo') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivo') {
                $query->where('activo', false);
            }
        }
        
        // Filtro por búsqueda (nombre, apellido, usuario, email)
        if ($request->has('buscar') && $request->buscar) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('apellido', 'like', '%' . $buscar . '%')
                  ->orWhere('usuario', 'like', '%' . $buscar . '%')
                  ->orWhere('email', 'like', '%' . $buscar . '%');
            });
        }
        
        $usuarios = $query->orderBy('nombre', 'asc')
            ->paginate(15)
            ->appends($request->query());

        // Ya no usamos OperatorRole, Spatie maneja los roles
        $roles = \Spatie\Permission\Models\Role::all();

        return view('usuarios', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        // Forzar idioma español
        App::setLocale('es');
        
        $rules = [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'usuario' => 'required|string|max:60|unique:operador,usuario',
            'password' => 'required|string|min:6',
            'rol' => 'required|string|in:admin,operador,cliente',
        ];

        // Validar email solo si se proporciona y no está vacío
        if ($request->filled('email') && trim($request->email) !== '') {
            $rules['email'] = 'email|max:100|unique:operador,email';
        } else {
            $rules['email'] = 'nullable|email|max:100';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Sincronizar la secuencia con el máximo ID existente
            $maxId = Operator::max('operador_id') ?? 0;
            if ($maxId > 0) {
            try {
                    DB::statement("SELECT setval('operador_seq', $maxId, true)");
            } catch (\Exception $e) {
                    // Si la secuencia no existe, crearla
                    DB::statement("CREATE SEQUENCE IF NOT EXISTS operador_seq START WITH " . ($maxId + 1));
                }
            }
            
            // Insertar usando SQL directo con nextval para evitar conflictos
            $passwordHash = Hash::make($request->password);
            $email = $request->filled('email') && trim($request->email) !== '' ? trim($request->email) : null;
            
            $operatorId = DB::selectOne("
                INSERT INTO operador (operador_id, nombre, apellido, usuario, password_hash, email, activo)
                VALUES (nextval('operador_seq'), ?, ?, ?, ?, ?, ?)
                RETURNING operador_id
            ", [
                $request->nombre,
                $request->apellido,
                $request->usuario,
                $passwordHash,
                $email,
                true
            ])->operador_id;
            
            // Obtener el operador creado
            $operator = Operator::find($operatorId);
            
            // Asignar el rol de Spatie
            $spatieRole = \Spatie\Permission\Models\Role::where('name', $request->rol)->first();
            if ($spatieRole && $operator) {
                $operator->syncRoles([$spatieRole]);
            }

            return redirect()->route('usuarios')
                ->with('success', 'Usuario creado exitosamente')
                ->withInput([]); // Limpiar los valores old() después de éxito
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $usuario = Operator::with('roles')->findOrFail($id);
        $usuarios = Operator::with('roles')
            ->orderBy('nombre', 'asc')
            ->paginate(15);
        $roles = \Spatie\Permission\Models\Role::all();
        
        return view('usuarios', compact('usuarios', 'usuario', 'roles'))->with('editing', true);
    }

    public function update(Request $request, $id)
    {
        // Forzar idioma español
        App::setLocale('es');
        
        $rules = [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'usuario' => 'required|string|max:60|unique:operador,usuario,' . $id . ',operador_id',
            'rol' => 'required|string|in:admin,operador,cliente',
            'activo' => 'nullable|boolean',
        ];

        // Validar email solo si se proporciona y no está vacío
        if ($request->filled('email') && trim($request->email) !== '') {
            $rules['email'] = 'email|max:100|unique:operador,email,' . $id . ',operador_id';
        } else {
            $rules['email'] = 'nullable|email|max:100';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $usuario = Operator::findOrFail($id);
            $data = [
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'usuario' => $request->usuario,
                'email' => $request->filled('email') && trim($request->email) !== '' ? trim($request->email) : null,
                'activo' => $request->has('activo') && $request->activo == '1'
            ];

            if ($request->filled('password')) {
                $data['password_hash'] = Hash::make($request->password);
            }

            $usuario->update($data);
            
            // Actualizar rol de Spatie
            $spatieRole = \Spatie\Permission\Models\Role::where('name', $request->rol)->first();
            if ($spatieRole) {
                $usuario->syncRoles([$spatieRole]);
            }

            return redirect()->route('usuarios')
                ->with('success', 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        // Forzar idioma español
        App::setLocale('es');
        
        try {
            $usuario = Operator::findOrFail($id);
            
            // No permitir eliminar el usuario actual
            if (auth()->check() && auth()->user()->operador_id == $id) {
                return redirect()->route('usuarios')
                    ->with('error', 'No puedes eliminar tu propio usuario.');
            }
            
            // Eliminar roles asignados
            $usuario->syncRoles([]);
            
            // Eliminar el usuario
            $usuario->delete();
            
            return redirect()->route('usuarios')
                ->with('success', 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('usuarios')
                ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }
}

