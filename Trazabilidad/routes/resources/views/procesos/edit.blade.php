@extends('layouts.app')
@section('content')
<h1 class="text-xl font-medium mb-4">Editar Proceso</h1>
<form method="POST" action="{{ route('procesos.update',$proceso->IdProceso) }}" class="space-y-3 max-w-xl">
  @csrf @method('PUT')
  <div><label class="block text-sm">Nombre</label><input name="Nombre" value="{{ old('Nombre',$proceso->Nombre) }}" class="border p-2 w-full" required></div>
  <div class="pt-2">
    <button class="underline">Actualizar</button>
    <a href="{{ route('procesos.index') }}" class="underline ml-3">Cancelar</a>
  </div>
</form>
@endsection



