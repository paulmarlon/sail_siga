@extends('adminlte::page')

@section('title', 'Gestión de Docente - Oferta Académica')

@section('content_header')
    <div class="container-fluid py-1">
        <div class="row mb-1">
            <div class="col-sm-6">
                <h1 class="h4 mb-0"><i class="fas fa-chalkboard-teacher text-primary"></i> Asignación Docente</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.oferta-academica.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a Oferta Académica
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Alertas de éxito o información --}}
        @if (session('mensaje'))
            <div class="alert alert-{{ session('icono') == 'success' ? 'success' : 'info' }} alert-dismissible fade show py-2 mb-2"
                role="alert" style="font-size: 0.85rem;">
                <i class="icon fas fa-{{ session('icono') == 'success' ? 'check' : 'info' }}"></i> {{ session('mensaje') }}
                <button type="button" class="close py-1" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Columna Izquierda: Formulario de Asignación / Cambio -->
            <div class="col-md-5">
                <div class="card card-secondary card-outline">
                    <div class="card-header py-2">
                        @php
                            $docenteVigente = $oferta->historialDocentes->whereNull('fecha_fin')->first();
                        @endphp
                        <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;"><i
                                class="fas fa-user-plus mr-1"></i>
                            {{ $docenteVigente ? 'Reasignar / Cambiar Docente' : 'Asignar Docente' }}</h3>
                    </div>
                    <form action="{{ route('admin.oferta.docentes.store', $oferta->id) }}" method="POST">
                        @csrf
                        <div class="card-body p-2" style="font-size: 0.85rem;">

                            <div class="form-group mb-2">
                                <label for="docente_id" class="mb-1">Seleccionar Profesor:</label>
                                <select name="docente_id" id="docente_id" class="form-control form-control-sm select2"
                                    required style="width: 100%;">
                                    <option value="">-- Buscar por Nombre o CI --</option>
                                    @foreach ($docentesDisponibles as $docente)
                                        <option value="{{ $docente->id }}">
                                            {{ $docente->persona->ap_paterno }} {{ $docente->persona->ap_materno }}
                                            {{ $docente->persona->nombres }} (CI: {{ $docente->persona->ci }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 mb-2">
                                    <label for="fecha_inicio" class="mb-1">Fecha de Inicio:</label>
                                    <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="form-group col-md-6 mb-2">
                                    <label for="contrato_id" class="mb-1">Nro. Contrato / Memo:</label>
                                    <input type="text" name="contrato_id" class="form-control form-control-sm"
                                        placeholder="Ej: MEMO-084/2026">
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="motivo_cambio" class="mb-1">Motivo de Asignación / Cambio:</label>
                                <textarea name="motivo_cambio" class="form-control form-control-sm" rows="2"
                                    placeholder="Ej: Asignación regular de semestre / Reemplazo"></textarea>
                            </div>
                        </div>
                        <div class="card-footer text-right py-2 bg-light">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Guardar Asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Columna Derecha: Detalle de la Oferta e Historial de Catedráticos -->
            <div class="col-md-7">
                <!-- Detalle de la Oferta -->
                <div class="card card-primary card-outline mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;"><i class="fas fa-book mr-1"></i>
                            Detalle de la Oferta</h3>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-group list-group-flush mb-2" style="font-size: 0.85rem;">
                            <li class="list-group-item px-2 py-1">
                                <b>Materia:</b> <span
                                    class="float-right text-bold text-primary">{{ $oferta->pensum->materia->nombre }}
                                    ({{ $oferta->pensum->materia->sigla }})</span>
                            </li>
                            <li class="list-group-item px-2 py-1">
                                <b>Carrera / Pensum:</b> <span
                                    class="float-right">{{ $oferta->pensum->carrera->sigla ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-2 py-1">
                                <b>Periodo / Gestión:</b> <span class="float-right">{{ $oferta->periodo->nombre }} -
                                    {{ $oferta->periodo->gestion->nombre }}</span>
                            </li>
                            <li class="list-group-item px-2 py-1">
                                <b>Paralelo / Turno:</b> <span
                                    class="float-right badge badge-info">{{ $oferta->paralelo->nombre }} /
                                    {{ $oferta->turno->nombre }}</span>
                            </li>
                            <li class="list-group-item px-2 py-1">
                                <b>Cupo Máximo:</b> <span class="float-right">{{ $oferta->cupo_maximo }} estudiantes</span>
                            </li>
                        </ul>

                        <div class="callout callout-{{ $docenteVigente ? 'success' : 'warning' }} p-2 mb-0"
                            style="font-size: 0.82rem;">
                            <h6 class="font-weight-bold mb-1">Docente Titular Actual:</h6>
                            @if ($docenteVigente)
                                <p class="mb-0 text-bold text-success">
                                    <i class="fas fa-user-check"></i>
                                    {{ $docenteVigente->docente->persona->ap_paterno }}
                                    {{ $docenteVigente->docente->persona->ap_materno }}
                                    {{ $docenteVigente->docente->persona->nombres }}
                                </p>
                                <small class="text-muted">Desde: {{ $docenteVigente->fecha_inicio }} | Contrato:
                                    {{ $docenteVigente->contrato_id ?? 'S/N' }}</small>
                            @else
                                <p class="mb-0 text-danger"><i class="fas fa-exclamation-triangle"></i> Sin docente asignado
                                    para este periodo.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Historial de Catedráticos (Bitácora) -->
                <div class="card card-info card-outline">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;"><i
                                class="fas fa-history mr-1"></i> Historial de Catedráticos</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped table-sm text-nowrap mb-0" style="font-size: 0.8rem;">
                            <thead>
                                <tr class="bg-light">
                                    <th>Docente</th>
                                    <th>Fechas</th>
                                    <th style="min-width: 150px;">Contrato / Motivo</th>
                                    <th>Estado</th>
                                    <th class="text-center" style="width: 40px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($oferta->historialDocentes as $historial)
                                    <tr>
                                        <td class="align-middle">
                                            <span
                                                class="font-weight-bold text-dark">{{ $historial->docente->persona->ap_paterno }}
                                                {{ $historial->docente->persona->nombres }}</span><br>
                                            <small class="text-muted">CI: {{ $historial->docente->persona->ci }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <small><b>Del:</b> {{ $historial->fecha_inicio }}</small><br>
                                            <small><b>Al:</b> {{ $historial->fecha_fin ?? 'Actualidad' }}</small>
                                        </td>
                                        <td class="align-middle text-wrap text-break"
                                            style="max-width: 170px; white-space: normal !important;">
                                            <small><b>Memo:</b> {{ $historial->contrato_id ?? 'N/A' }}</small><br>
                                            <small class="text-muted">{{ $historial->motivo_cambio }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <span
                                                class="badge badge-{{ is_null($historial->fecha_fin) ? 'success' : 'secondary' }}">
                                                {{ is_null($historial->fecha_fin) ? 'Vigente' : 'Concluido' }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if (is_null($historial->fecha_fin))
                                                <form
                                                    action="{{ route('admin.oferta.docentes.concluir', $historial->id) }}"
                                                    method="POST" style="display:inline;"
                                                    onsubmit="return confirm('¿Estás seguro de concluir anticipadamente esta asignación docente?');">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-xs btn-warning px-2"
                                                        title="Concluir contrato actual">
                                                        <i class="fas fa-stop-circle"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted"><i class="fas fa-lock"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No existen registros históricos de docentes para esta oferta académica.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Ajuste de ancho completo para el select y su buscador interno */
        .select2-container {
            width: 100% !important;
        }

        .select2-search__field {
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .select2-container--bootstrap4 .select2-selection {
            height: calc(1.815rem + 2px) !important;
            padding: .25rem .5rem;
            font-size: .875rem;
        }

        /* Limita la altura del menú desplegable del Select2 para que muestre aproximadamente 10 elementos */
        .select2-container--bootstrap4 .select2-results__options {
            max-height: 250px !important;
            overflow-y: auto !important;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializamos Select2 adaptado a Bootstrap 4
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@stop
