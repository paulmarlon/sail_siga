@extends('adminlte::page')

@section('title', 'Editar Inscripción Individual | SIG@')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-edit mr-2 text-success"></i> Editar <b>Inscripción Individual</b></h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.inscripcion-carreras.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.alertas')
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header py-2">
                <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                    Estudiante: {{ $inscripcionCarrera->estudiante->persona->ap_paterno }}
                    {{ $inscripcionCarrera->estudiante->persona->ap_materno }}
                    {{ $inscripcionCarrera->estudiante->persona->nombres }}
                </h3>
            </div>
            <form action="{{ route('admin.inscripcion-carreras.update-individual', $inscripcionCarrera->id) }}"
                method="POST" id="form-edit-individual">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        {{-- Select de Carrera --}}
                        <div class="col-md-6 form-group">
                            <label for="carrera_id" class="small font-weight-bold">Carrera:</label>
                            <select name="carrera_id" id="carrera_id" class="form-control form-control-sm" required>
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id }}"
                                        {{ $inscripcionCarrera->carrera_id == $carrera->id ? 'selected' : '' }}>
                                        {{ $carrera->nombre }} ({{ $carrera->sigla }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="periodo_id" class="small font-weight-bold">Periodo Académico:</label>
                            <select name="periodo_id" id="periodo_id" class="form-control form-control-sm" required>
                                @foreach ($periodos as $periodo)
                                    <option value="{{ $periodo->id }}"
                                        {{ $inscripcionCarrera->periodo_id == $periodo->id ? 'selected' : '' }}>
                                        {{-- Muestra el nombre del periodo y el nombre de la gestión --}}
                                        {{ $periodo->nombre }} | Gestión: {{ $periodo->gestion->nombre ?? 'N/D' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Fecha de Inscripción --}}
                        <div class="col-md-4 form-group">
                            <label for="fecha_inscripcion" class="small font-weight-bold">Fecha de Inscripción:</label>
                            <input type="date" name="fecha_inscripcion" id="fecha_inscripcion"
                                class="form-control form-control-sm"
                                value="{{ $inscripcionCarrera->fecha_inscripcion ? $inscripcionCarrera->fecha_inscripcion->format('Y-m-d') : date('Y-m-d') }}"
                                required>
                        </div>

                        {{-- Select de Estado Académico --}}
                        <div class="col-md-4 form-group">
                            <label for="estado_id" class="small font-weight-bold">Estado Académico:</label>
                            <select name="estado_id" id="estado_id" class="form-control form-control-sm" required>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->id }}"
                                        {{ $inscripcionCarrera->estado_id == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Checkbox Especialidad Activa --}}
                        <div class="col-md-4 form-group d-flex align-items-center pt-4">
                            <div class="form-check">
                                <input type="checkbox" name="es_especialidad_activa" id="es_especialidad_activa"
                                    class="form-check-input" value="1"
                                    {{ $inscripcionCarrera->es_especialidad_activa ? 'checked' : '' }}>
                                <label class="form-check-label small font-weight-bold"
                                    for="es_especialidad_activa">¿Especialidad Activa?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm text-dark">
                        <i class="fas fa-save mr-1"></i> Guardar Cambios Individuales
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#form-edit-individual').on('submit', function() {
                $(this).find('button[type="submit"]').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
            });
        });
    </script>
@stop
