@extends('adminlte::page') {{-- O tu layout principal --}}

@section('title', 'Gestión de Folios y Exámenes')

@section('content_header')
    <h1><i class="fas fa-file-alt mr-2"></i> Gestión de Folios de Exámenes</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Listado de Programaciones de Exámenes</h3>
        </div>
        <div class="card-body">
            {{-- Formulario de Filtros --}}
            <form method="GET" action="{{ route('admin.folio-examens.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label>Periodo:</label>
                        <select name="periodo_id" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            @foreach ($periodos as $p)
                                <option value="{{ $p->id }}" {{ request('periodo_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Carrera:</label>
                        <select name="carrera_id" class="form-control form-control-sm">
                            <option value="">-- Todas --</option>
                            @foreach ($carreras as $c)
                                <option value="{{ $c->id }}" {{ request('carrera_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label>Buscar Materia / Sigla:</label>
                        <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                            class="form-control form-control-sm" placeholder="Ej. Informática...">
                    </div>
                    <div class="col-md-3 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm mr-2"><i class="fas fa-search"></i>
                            Filtrar</button>
                        <a href="{{ route('admin.folio-examens.index') }}" class="btn btn-secondary btn-sm"><i
                                class="fas fa-redo"></i> Limpiar</a>
                    </div>
                </div>
            </form>

            {{-- Tabla de Resultados --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asignatura</th>
                            <th>Paralelo</th>
                            <th>Turno</th>
                            <th>Gestión</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($listaProgramaciones as $prog)
                            <tr>
                                <td>{{ $prog->id }}</td>
                                <td>{{ $prog->ofertaAcademica->pensum->materia->nombre ?? 'N/A' }}</td>
                                <td>{{ $prog->ofertaAcademica->paralelo->nombre ?? 'N/A' }}</td>
                                <td>{{ $prog->ofertaAcademica->turno->nombre ?? 'N/A' }}</td>
                                <td>{{ $prog->ofertaAcademica->periodo->gestion->nombre ?? 'N/A' }}</td>
                                <td>
                                    @if ($prog->bloqueado)
                                        <span class="badge badge-danger">Bloqueado</span>
                                    @else
                                        <span class="badge badge-success">Editable</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.folio-examens.plantilla', $prog->id) }}"
                                        class="btn btn-info btn-sm" title="Gestionar Plantilla de Folios">
                                        <i class="fas fa-table"></i> Plantilla
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No se encontraron programaciones de
                                    exámenes registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-3">
                {{ $listaProgramaciones->links() }}
            </div>
        </div>
    </div>
@stop
