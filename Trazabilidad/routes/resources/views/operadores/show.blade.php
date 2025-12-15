@extends('layouts.app')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0">Operador #{{ $operador->IdOperador }}</h3>
    <div class="btn-group btn-group-sm" role="group">
      <a href="{{ route('operadores.web.edit',$operador->operator_id) }}" class="btn btn-primary"><i class="far fa-edit mr-1"></i> Editar</a>
      <a href="{{ route('operadores.web.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
    </div>
  </div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9">{{ $operador->Nombre }}</dd>
      <dt class="col-sm-3">Cargo</dt><dd class="col-sm-9">{{ $operador->Cargo }}</dd>
      <dt class="col-sm-3">Usuario</dt><dd class="col-sm-9">{{ $operador->Usuario }}</dd>
      <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $operador->Email }}</dd>
    </dl>
  </div>
</div>
@endsection



