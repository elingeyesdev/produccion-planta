@extends('layouts.app')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0">Proveedor #{{ $proveedor->IdProveedor }}</h3>
    <div class="btn-group btn-group-sm" role="group">
      <a href="{{ route('proveedores.web.edit',$proveedor->supplier_id) }}" class="btn btn-primary"><i class="far fa-edit mr-1"></i> Editar</a>
      <a href="{{ route('proveedores.web.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
    </div>
  </div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9">{{ $proveedor->Nombre }}</dd>
      <dt class="col-sm-3">Contacto</dt><dd class="col-sm-9">{{ $proveedor->Contacto }}</dd>
      <dt class="col-sm-3">Telefono</dt><dd class="col-sm-9">{{ $proveedor->Telefono }}</dd>
      <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $proveedor->Email }}</dd>
      <dt class="col-sm-3">Direccion</dt><dd class="col-sm-9">{{ $proveedor->Direccion }}</dd>
    </dl>
  </div>
</div>
@endsection



