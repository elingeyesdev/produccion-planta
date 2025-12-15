@extends('layouts.app')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0">Proceso #{{ $proceso->IdProceso }}</h3>
    <div class="btn-group btn-group-sm" role="group">
      <a href="{{ route('procesos.edit',$proceso->IdProceso) }}" class="btn btn-primary"><i class="far fa-edit mr-1"></i> Editar</a>
      <a href="{{ route('procesos.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
    </div>
  </div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9">{{ $proceso->Nombre }}</dd>
    </dl>
  </div>
</div>
@endsection



