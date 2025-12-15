@extends('layouts.app')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-medium">Operadores</h1>
  <a href="{{ route('operadores.web.create') }}" class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] hover:border-black rounded-sm text-sm">Nuevo</a>
</div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span class="card-title m-0">Listado</span>
    <a href="{{ route('operadores.web.create') }}" class="btn btn-primary btn-sm">Nuevo</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-striped mb-0 text-sm">
      <thead class="thead-light"><tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Email</th><th class="text-right pr-3">Acciones</th></tr></thead>
      <tbody>
  @foreach($operadores as $o)
    <tr class="border-b"><td>{{ $o->IdOperador }}</td><td>{{ $o->Nombre }}</td><td>{{ $o->Usuario }}</td><td>{{ $o->Email }}</td>
      <td class="text-right pr-3">
        <div class="btn-group btn-group-sm" role="group">
          <a class="btn btn-secondary" href="{{ route('operadores.web.show',$o->operator_id) }}"><i class="far fa-eye mr-1"></i> Ver</a>
          <a class="btn btn-primary" href="{{ route('operadores.web.edit',$o->operator_id) }}"><i class="far fa-edit mr-1"></i> Editar</a>
          <form method="POST" action="{{ route('operadores.web.destroy',$o->operator_id) }}" onsubmit="return confirm('¿Eliminar este operador?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="far fa-trash-alt mr-1"></i> Eliminar</button>
          </form>
        </div>
      </td></tr>
  @endforeach
      </tbody>
    </table>
  </div>
  @if($operadores->hasPages())
  <div class="card-footer clearfix">
    <div class="float-left">
      <small class="text-muted">
        Mostrando {{ $operadores->firstItem() }} a {{ $operadores->lastItem() }} de {{ $operadores->total() }} registros
      </small>
    </div>
    {{ $operadores->links() }}
  </div>
  @endif
</div>
@endsection



