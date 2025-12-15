@extends('layouts.app')

@section('page_title', 'Gestión de Operadores')

@section('content')
<!-- Estadísticas de Operadores -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>15</h3>
                <p>Total Operadores</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-cog"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>12</h3>
                <p>Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>2</h3>
                <p>En Capacitación</p>
            </div>
            <div class="icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>1</h3>
                <p>Inactivos</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Operadores -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-1"></i>
            Listado de Operadores
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCrearOperador">
                <i class="fas fa-plus"></i> Nuevo Operador
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#O001</td>
                        <td>Ana García</td>
                        <td>ana.garcia@empresa.com</td>
                        <td>+1 234-567-8900</td>
                        <td><span class="badge badge-primary">Supervisor</span></td>
                        <td><span class="badge badge-success">Activo</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#O002</td>
                        <td>Carlos López</td>
                        <td>carlos.lopez@empresa.com</td>
                        <td>+1 234-567-8901</td>
                        <td><span class="badge badge-info">Operador</span></td>
                        <td><span class="badge badge-success">Activo</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#O003</td>
                        <td>María Rodríguez</td>
                        <td>maria.rodriguez@empresa.com</td>
                        <td>+1 234-567-8902</td>
                        <td><span class="badge badge-info">Operador</span></td>
                        <td><span class="badge badge-warning">En Capacitación</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer clearfix">
        <ul class="pagination pagination-sm m-0 float-right">
            <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
        </ul>
    </div>
</div>

<!-- Modal para Crear Operador -->
<div class="modal fade" id="modalCrearOperador" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Operador</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreOperador">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombreOperador" placeholder="Ej: Ana García">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emailOperador">Email</label>
                                <input type="email" class="form-control" id="emailOperador" placeholder="ana.garcia@empresa.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefonoOperador">Teléfono</label>
                                <input type="tel" class="form-control" id="telefonoOperador" placeholder="+1 234-567-8900">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rolOperador">Rol</label>
                                <select class="form-control" id="rolOperador">
                                    <option value="operador">Operador</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="departamentoOperador">Departamento</label>
                        <select class="form-control" id="departamentoOperador">
                            <option value="produccion">Producción</option>
                            <option value="calidad">Calidad</option>
                            <option value="almacen">Almacén</option>
                            <option value="administracion">Administración</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar Operador</button>
            </div>
        </div>
    </div>
</div>
@endsection
