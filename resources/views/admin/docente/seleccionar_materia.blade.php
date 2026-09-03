@extends('adminlte::page')

@section('title', 'Portal Docente - Mis Materias y Exámenes')

@section('content_header')
    <div class="card card-outline card-primary shadow-sm mb-2">
        <div class="card-body py-3">
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-chalkboard-teacher text-primary mr-2"></i> Portal Docente: Mis Exámenes y Materias Asignadas
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Seleccione la materia e instancia correspondiente para ingresar a su estación de foliado y registro de
                notas.
            </p>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-light py-2">
                    <h3 class="card-title font-weight-bold" style="font-size: 0.95rem;">
                        <i class="fas fa-list text-primary mr-1"></i> LISTADO DE EVALUACIONES PROGRAMADAS
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if ($materiasAsignadas->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted font-weight-bold">No tienes materias o exámenes programados asignados
                                actualmente en tu cuenta.</p>
                        </div>
                    @else
                        <table class="table table-hover table-striped table-sm m-0 align-middle"
                            style="font-size: 0.88rem;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="50px" class="py-2">#</th>
                                    <th class="py-2 text-left px-3">MATERIA / SIGLA</th>
                                    <th class="py-2 text-left">CARRERA / PENSUM</th>
                                    <th width="100px" class="py-2">TURNO / PARALELO</th>
                                    <th width="100px" class="py-2">INSTANCIA</th>
                                    <th width="120px" class="py-2">MODALIDAD</th>
                                    <th width="140px" class="py-2">ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($materiasAsignadas as $index => $prog)
                                    @php
                                        $oferta = $prog->ofertaAcademica;
                                        $materia = $oferta->pensum->materia ?? null;
                                        $carrera = $oferta->pensum->carrera ?? null;

                                        // Definir color de badge según modalidad
                                        $modalidadLower = strtolower($prog->modalidad ?? '');
                                        $badgeModalidad = 'badge-info';
                                        if ($modalidadLower === 'a_ciegas') {
                                            $badgeModalidad = 'badge-purple';
                                        } elseif ($modalidadLower === 'directa') {
                                            $badgeModalidad = 'badge-success';
                                        } elseif ($modalidadLower === 'dictada') {
                                            $badgeModalidad = 'badge-warning';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle px-3">
                                            <span class="font-weight-bold text-dark">{{ $materia->nombre ?? 'S/N' }}</span>
                                            <br><small class="text-muted font-weight-bold">Sigla:
                                                {{ $materia->sigla ?? 'S/S' }}</small>
                                        </td>
                                        <td class="align-middle">
                                            {{ $carrera->nombre ?? 'S/N' }}
                                            <br><small class="text-muted">{{ $oferta->pensum->grado->nombre ?? '' }}</small>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span
                                                class="badge badge-light border">{{ $oferta->turno->nombre ?? 'S/N' }}</span>
                                            <span
                                                class="badge badge-light border">{{ $oferta->paralelo->nombre ?? 'S/N' }}</span>
                                        </td>
                                        <td class="text-center align-middle font-weight-bold">
                                            <span class="badge badge-primary"
                                                style="font-size: 0.8rem;">{{ $prog->instancia ?? 'EXAMEN' }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge {{ $badgeModalidad }} text-uppercase"
                                                @if ($modalidadLower === 'a_ciegas') style="background-color: #6f42c1; color: #fff;" @endif>
                                                {{ $prog->modalidad ?? 'Ordinario' }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            {{-- Botón para fijar materia/examen en sesión y entrar a la estación de trabajo --}}
                                            <form action="{{ route('docente.fijar-materia') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="programacion_id" value="{{ $prog->id }}">
                                                <button type="submit"
                                                    class="btn btn-success btn-sm font-weight-bold shadow-sm">
                                                    <i class="fas fa-sign-in-alt mr-1"></i> Ingresar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop
