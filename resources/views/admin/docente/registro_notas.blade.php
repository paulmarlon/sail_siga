@extends('adminlte::page')

@section('title', 'Registro de Notas')

@section('content_header')
    <div class="card card-outline card-success shadow-sm mb-2">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-marker text-success mr-2"></i> Registro de Calificaciones: <span
                            class="text-primary">{{ strtoupper($programacion->instancia ?? 'EXAMEN') }}</span>
                    </h4>
                    <div class="text-muted" style="font-size: 0.88rem;">
                        <div class="mb-1">
                            <span><i class="fas fa-book mr-1 text-info"></i> Materia:
                                <strong>{{ $programacion->ofertaAcademica->pensum->materia->nombre ?? 'S/N' }}</strong>
                                ({{ $programacion->ofertaAcademica->pensum->materia->sigla ?? 'S/S' }})</span> &nbsp;|&nbsp;
                            <span><i class="fas fa-graduation-cap mr-1 text-success"></i> Carrera:
                                <strong>{{ $programacion->ofertaAcademica->pensum->carrera->nombre ?? 'S/N' }}</strong></span>
                        </div>
                        <div>
                            <span><i class="fas fa-layer-group mr-1 text-warning"></i> Semestre/Grado:
                                <strong>{{ $programacion->ofertaAcademica->pensum->grado->nombre ?? 'S/N' }}</strong></span>
                            &nbsp;|&nbsp;
                            <span><i class="fas fa-columns mr-1 text-secondary"></i> Paralelo:
                                <strong>{{ $programacion->ofertaAcademica->paralelo->nombre ?? 'S/N' }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <a href="{{ route('docente.seleccionar-materia') }}"
                        class="btn btn-outline-secondary shadow-sm font-weight-bold mr-1">
                        <i class="fas fa-exchange-alt mr-1"></i> Cambiar Materia
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row pt-1">
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

            <form action="{{ route('docente.notas.guardar') }}" method="POST">
                @csrf

                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold" style="font-size: 0.9rem;">
                            <i class="fas fa-list-alt text-success mr-1"></i> NÓMINA DE ESTUDIANTES Y CALIFICACIONES
                        </h3>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> Guardar Calificaciones
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped table-sm m-0" style="font-size: 0.82rem;">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="50px" class="py-2">#</th>
                                    <th width="130px" class="py-2">CÓDIGO FOLIO</th>
                                    <th width="120px" class="py-2">RU</th>
                                    <th class="py-2 text-left px-3">APELLIDOS Y NOMBRES</th>
                                    <th width="140px" class="py-2">NOTA (0 - 100)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estudiantesMatriculados as $index => $mat)
                                    @php
                                        $est = $mat->estudiante;
                                        $p = $est->persona ?? null;
                                        $nombreCompleto = $p
                                            ? trim(
                                                ($p->ap_paterno ?? '') .
                                                    ' ' .
                                                    ($p->ap_materno ?? '') .
                                                    ', ' .
                                                    ($p->nombres ?? ''),
                                            )
                                            : 'SIN DATOS';

                                        $folioAsociado = $programacion->foliosExamen
                                            ->where('estudiante_id', $est->id)
                                            ->first();
                                        $codigoFolio = $folioAsociado->codigo_folio ?? 'SIN FOLIO';
                                        $notaActual = $folioAsociado->nota ?? '';
                                    @endphp
                                    <tr>
                                        <td class="text-center font-weight-bold align-middle bg-light">{{ $index + 1 }}
                                        </td>

                                        <td class="text-center font-weight-bold text-purple align-middle">
                                            {{ $codigoFolio }}
                                        </td>

                                        <td class="text-center font-weight-bold text-primary align-middle">
                                            {{ $est->registro_universitario }}
                                        </td>

                                        <td class="align-middle px-3 text-uppercase font-weight-bold text-dark">
                                            {{ $nombreCompleto }}
                                        </td>

                                        <td class="text-center align-middle p-1">
                                            <input type="hidden" name="notas[{{ $index }}][estudiante_id]"
                                                value="{{ $est->id }}">
                                            <input type="number" name="notas[{{ $index }}][calificacion]"
                                                class="form-control form-control-sm text-center font-weight-bold input-nota"
                                                style="font-size: 0.9rem;" min="0" max="100" step="0.01"
                                                value="{{ $notaActual }}" placeholder="0 - 100" autocomplete="off">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-light text-right py-2">
                        <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> Guardar Calificaciones
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            // Navegación fluida con las flechas del teclado y enter entre los inputs de notas
            $(document).on('keydown', '.input-nota', function(e) {
                let currentInput = $(this);
                let inputs = $('.input-nota');
                let index = inputs.index(currentInput);

                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    if (index + 1 < inputs.length) {
                        inputs.eq(index + 1).focus().select();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (index - 1 >= 0) {
                        inputs.eq(index - 1).focus().select();
                    }
                }
            });
        });
    </script>
@stop
