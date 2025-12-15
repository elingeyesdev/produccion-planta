@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Editar Proveedor</h1>
<form method="POST" action="{{ route('proveedores.web.update',$proveedor->supplier_id) }}" class="space-y-3 max-w-xl">
  @csrf @method('PUT')
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre',$proveedor->Nombre) }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">Contacto</label><input name="Contacto" value="{{ old('Contacto',$proveedor->Contacto) }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Telefono</label><input name="Telefono" value="{{ old('Telefono',$proveedor->Telefono) }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Email</label><input name="Email" type="email" value="{{ old('Email',$proveedor->Email) }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Direccion</label><input name="Direccion" value="{{ old('Direccion',$proveedor->Direccion) }}" class="border p-2 w-full"></div>
  <div class="pt-2">
    <button class="underline">Actualizar</button>
    <a href="{{ route('proveedores.web.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



