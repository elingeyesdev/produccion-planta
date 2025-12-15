@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Nuevo Proveedor</h1>
<form method="POST" action="{{ route('proveedores.web.store') }}" class="space-y-3 max-w-xl">
  @csrf
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre') }}" class="border p-2 w-full" required></div>
  <div><label class="block text-sm">Contacto</label><input name="Contacto" value="{{ old('Contacto') }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Telefono</label><input name="Telefono" value="{{ old('Telefono') }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Email</label><input name="Email" type="email" value="{{ old('Email') }}" class="border p-2 w-full"></div>
  <div><label class="block text-sm">Direccion</label><input name="Direccion" value="{{ old('Direccion') }}" class="border p-2 w-full"></div>
  <div class="pt-2">
    <button class="underline">Guardar</button>
    <a href="{{ route('proveedores.web.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



