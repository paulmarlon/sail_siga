@extends('adminlte::page')

@section('title', 'Detalle de Carga Académica')

@section('css')
    <style>
        .table th,
        .table td {
            padding: 0.5rem 0.75rem !important;
            vertical-align: middle !important;
            font-size: 0.85rem;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-id-card text-primary mr-2"></i> Ficha de Carga Académica</h1>
            <p class="text-muted mb-0">Detalle microscópico de asignaturas inscritas por periodo.</p>
        </div>
        <div>
            <!-- Botón para añadir una materia suelta a este estudiante en este periodo -->
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                data-target="#modalAdicionQuirurgica">
                <i class="fas fa-plus-circle mr-1"></i> Adición Quirúrgica de Materia
            </button>
            <a href="{{ route('admin.matriculacion-materias.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Información General del Estudiante -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold text-dark" style="font-size: 0.95rem;">
                <i class="fas fa-user-graduate mr-1 text-primary"></i> Datos del Estudiante
            </h3>
        </div>
        <div class="card-body py-2 bg-light">
            <div class="row">
                <div class="col-md-4">
                    <strong>Estudiante:</strong> {{ $estudiante->persona->ap_paterno }}
                    {{ $estudiante->persona->ap_materno }} {{ $estudiante->persona->nombres }}
                </div>
                <div class="col-md-3">
                    <strong>CI:</strong> {{ $estudiante->persona->ci }}
                </div>
                <div class="col-md-3">
                    <strong>RU:</strong> {{ $estudiante->registro_universitario }}
                </div>
                <div class="col-md-2">
                    <strong>Periodo:</strong> <span class="badge badge-info">{{ $periodo->nombre ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Asignaturas Inscritas -->
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                <i class="fas fa-book mr-1 text-success"></i> Materias Inscritas en el Periodo
                ({{ $matriculaciones->count() }})
            </h3>
        </div>
        <div class="card-body p-2">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 60px;">Sigla</th>
                        <th>Nombre de la Materia</th>
                        <th>Carrera / Grado</th>
                        <th>Turno / Paralelo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matriculaciones as $mat)
                        <tr>
                            <td><span
                                    class="badge badge-secondary">{{ $mat->oferta->pensum->materia->sigla ?? 'N/A' }}</span>
                            </td>
                            <td class="font-weight-bold">{{ $mat->oferta->pensum->materia->nombre ?? 'Sin nombre' }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $mat->oferta->pensum->carrera->nombre ?? 'S/C' }} -
                                    {{ $mat->oferta->pensum->grado->nombre ?? 'S/G' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ $mat->oferta->turno->nombre ?? 'S/T' }}</span> /
                                <span class="badge badge-light border">P:
                                    {{ $mat->oferta->paralelo->nombre ?? 'S/P' }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $slugEstado = $mat->estado->slug ?? 'default';
                                    $badgeClass = match ($slugEstado) {
                                        'matriculado', 'activo' => 'badge-success',
                                        'retirado', 'descontinuado' => 'badge-danger',
                                        'congelado' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span
                                    class="badge {{ $badgeClass }} px-2 py-1">{{ $mat->estado->nombre ?? 'Desconocido' }}</span>
                            </td>
                            <td class="text-center">
                                <!-- Botón para eliminar o retirar de forma quirúrgica esta materia -->
                                <form action="{{ route('admin.matriculacion-materias.destroy', $mat->id) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('¿Estás seguro de retirar esta materia de la carga del estudiante?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" title="Retirar Materia">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL DE ADICIÓN QUIRÚRGICA ================= -->
    <div class="modal fade" id="modalAdicionQuirurgica" tabindex="-1" role="dialog"
        aria-labelledby="modalAdicionQuirurgicaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.matriculacion-materias.store-single') }}" method="POST"
                    id="form-adicion-quirurgica">
                    @csrf
                    <!-- IDs ocultos necesarios para saber a quién y en qué periodo inscribir -->
                    <input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
                    <input type="hidden" name="periodo_id" value="{{ $periodo->id ?? '' }}">

                    <div class="modal-header bg-primary py-2">
                        <h5 class="modal-title font-weight-bold text-white" id="modalAdicionQuirurgicaLabel"
                            style="font-size: 0.95rem;">
                            <i class="fas fa-stethoscope mr-1"></i> Adición Quirúrgica: Inscribir Materia Individual
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Utiliza los filtros para acotar la búsqueda y selecciona la materia que deseas añadir de forma
                            individual al récord del estudiante.
                        </p>

                        <!-- FILTROS DE BÚSQUEDA RÁPIDA -->
                        <div class="form-row mb-3 bg-light p-2 rounded border">
                            <div class="col-12 mb-2">
                                <select id="modal-q-carrera" class="form-control form-control-sm">
                                    <option value="">-- Filtrar por Carrera --</option>
                                    @foreach ($carreras as $car)
                                        <option value="{{ strtolower($car->nombre) }}">{{ $car->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-1">
                                <select id="modal-q-grado" class="form-control form-control-sm">
                                    <option value="">-- Grado / Semestre --</option>
                                    @foreach ($grados as $gra)
                                        <option value="{{ strtolower($gra->nombre) }}">{{ $gra->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-1">
                                <select id="modal-q-turno" class="form-control form-control-sm">
                                    <option value="">-- Turno --</option>
                                    @foreach ($turnos as $tur)
                                        <option value="{{ strtolower($tur->nombre) }}">{{ $tur->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-1">
                                <select id="modal-q-paralelo" class="form-control form-control-sm">
                                    <option value="">-- Paralelo --</option>
                                    @foreach ($paralelos as $par)
                                        <option value="{{ strtolower($par->nombre) }}">{{ $par->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- LISTA DE OFERTAS ACADÉMICAS FILTRABLES (RADIO BUTTONS PARA SELECCIÓN ÚNICA) -->
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-secondary">Seleccionar Materia de la Oferta:</label>
                            <div id="lista-ofertas-quirurgica" class="border rounded p-2 bg-white"
                                style="max-height: 250px; overflow-y: auto;">
                                @foreach ($ofertas as $oferta)
                                    @php
                                        $nombreCarrera = $oferta->pensum->carrera->nombre ?? '';
                                        $nombreGrado = $oferta->pensum->grado->nombre ?? '';
                                        $nombreMateria = $oferta->pensum->materia->nombre ?? 'N/A';
                                        $siglaMateria = $oferta->pensum->materia->sigla ?? 'S/S';
                                        $nombrePeriodo = $oferta->periodo->nombre ?? '';
                                        $nombreTurno = $oferta->turno->nombre ?? '';
                                        $nombreParalelo = $oferta->paralelo->nombre ?? '';
                                    @endphp
                                    <div class="oferta-quirurgica-item custom-control custom-radio mb-2 px-2 py-1 border-bottom"
                                        data-carrera="{{ strtolower($nombreCarrera) }}"
                                        data-grado="{{ strtolower($nombreGrado) }}"
                                        data-turno="{{ strtolower($nombreTurno) }}"
                                        data-paralelo="{{ strtolower($nombreParalelo) }}" style="font-size: 0.78rem;">

                                        <input type="radio" name="oferta_id" value="{{ $oferta->id }}"
                                            id="oferta_q_{{ $oferta->id }}" class="custom-control-input" required>

                                        <label
                                            class="custom-control-label font-weight-normal text-dark w-100 cursor-pointer"
                                            for="oferta_q_{{ $oferta->id }}">
                                            <span class="badge badge-info px-1">{{ $siglaMateria }}</span>
                                            <strong class="text-dark">{{ $nombreMateria }}</strong>
                                            <span class="text-muted d-block" style="font-size: 0.7rem;">
                                                {{ $nombreCarrera }} | {{ $nombreGrado }} | Turno: {{ $nombreTurno }} |
                                                Paralelo: {{ $nombreParalelo }}
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- ESTADO ACADÉMICO PARA LA MATERIA INDIVIDUAL -->
                        <div class="form-group mb-0">
                            <label for="estado_id_single" class="font-weight-bold small text-secondary">Estado Académico
                                Inicial:</label>
                            <select name="estado_id" id="estado_id_single" class="form-control form-control-sm" required>
                                <option value="">-- Seleccionar Estado --</option>
                                @foreach ($estados as $est)
                                    <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold">
                            <i class="fas fa-check mr-1"></i> Registrar Adición Quirúrgica
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qCarrera = document.getElementById('modal-q-carrera');
            const qGrado = document.getElementById('modal-q-grado');
            const qTurno = document.getElementById('modal-q-turno');
            const qParalelo = document.getElementById('modal-q-paralelo');

            function filtrarOfertaQuirurgica() {
                const car = qCarrera.value.toLowerCase().trim();
                const gra = qGrado.value.toLowerCase().trim();
                const tur = qTurno.value.toLowerCase().trim();
                const par = qParalelo.value.toLowerCase().trim();

                document.querySelectorAll('.oferta-quirurgica-item').forEach(item => {
                    const itemCar = item.getAttribute('data-carrera').toLowerCase();
                    const itemGra = item.getAttribute('data-grado').toLowerCase();
                    const itemTur = item.getAttribute('data-turno').toLowerCase();
                    const itemPar = item.getAttribute('data-paralelo').toLowerCase();

                    let match = true;
                    if (car && !itemCar.includes(car)) match = false;
                    if (gra && !itemGra.includes(gra)) match = false;
                    if (tur && !itemTur.includes(tur)) match = false;
                    if (par && !itemPar.includes(par)) match = false;

                    item.style.display = match ? '' : 'none';
                });
            }

            if (qCarrera) qCarrera.addEventListener('change', filtrarOfertaQuirurgica);
            if (qGrado) qGrado.addEventListener('change', filtrarOfertaQuirurgica);
            if (qTurno) qTurno.addEventListener('change', filtrarOfertaQuirurgica);
            if (qParalelo) qParalelo.addEventListener('change', filtrarOfertaQuirurgica);
        });
    </script>
@stop
