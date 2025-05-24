@extends('layouts.admin')

@section('title', 'Gestión de Aprendices - SENA Control de Asistencia')

@section('page-title', 'Gestión de Aprendices')

@section('content')
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-users"></i> Listado de Aprendices
        </div>
        <div class="card-actions">
            <a href="#" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuevo Aprendiz
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre Completo</th>
                        <th>Programa</th>
                        <th>Ficha</th>
                        <th>Jornada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aprendices as $aprendiz)
                    <tr>
                        <td>{{ $aprendiz->documento_identidad }}</td>
                        <td>{{ $aprendiz->nombres_completos }}</td>
                        <td>{{ $aprendiz->programaFormacion ? $aprendiz->programaFormacion->nombre_programa : 'N/A' }}</td>
                        <td>{{ $aprendiz->programaFormacion ? $aprendiz->programaFormacion->numero_ficha : 'N/A' }}</td>
                        <td>{{ $aprendiz->jornada ? $aprendiz->jornada->nombre : 'N/A' }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay aprendices registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            {{ $aprendices->links() }}
        </div>
    </div>
</div>
@endsection 