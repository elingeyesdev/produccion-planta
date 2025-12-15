@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-medium">Máquinas</h1>
  <a href="{{ route('maquinas.create') }}" class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] hover:border-black rounded-sm text-sm">Nueva</a>
</div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="card-title m-0">Listado</span>
    <a href="{{ route('maquinas.create') }}" class="btn btn-primary btn-sm">Nueva</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-striped mb-0 text-sm">
      <thead class="thead-light"><tr><th>ID</th><th>Imagen</th><th>Código</th><th>Nombre</th><th class="text-right pr-3">Acciones</th></tr></thead>
      <tbody>
  @foreach($maquinas as $m)
    <tr class="border-b">
      <td>{{ $m->machine_id }}</td>
      <td>
        @if($m->image_url)
          <img src="{{ $m->image_url }}" alt="{{ $m->name }}" 
               class="img-thumbnail" style="max-width: 80px; max-height: 80px; object-fit: cover;">
        @else
          <span class="text-muted"><i class="fas fa-image"></i> Sin imagen</span>
        @endif
      </td>
      <td>{{ $m->code }}</td>
      <td>{{ $m->name }}</td>
      <td class="text-right pr-3">
        <div class="btn-group btn-group-sm" role="group">
          <a class="btn btn-secondary" href="{{ route('maquinas.show',$m->machine_id) }}"><i class="far fa-eye mr-1"></i> Ver</a>
          <a class="btn btn-primary" href="{{ route('maquinas.edit',$m->machine_id) }}"><i class="far fa-edit mr-1"></i> Editar</a>
          <form method="POST" action="{{ route('maquinas.destroy',$m->machine_id) }}" onsubmit="return confirm('¿Eliminar esta máquina?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="far fa-trash-alt mr-1"></i> Eliminar</button>
          </form>
        </div>
      </td></tr>
  @endforeach
      </tbody>
    </table>
  </div>
  @if($maquinas->hasPages())
  <div class="card-footer clearfix">
    <div class="float-left">
      <small class="text-muted">
        Mostrando {{ $maquinas->firstItem() }} a {{ $maquinas->lastItem() }} de {{ $maquinas->total() }} registros
      </small>
    </div>
    {{ $maquinas->links() }}
  </div>
  @endif
</div>
@endsection



