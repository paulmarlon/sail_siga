@extends('adminlte::page')

@section('title', 'Planilla de Calificaciones')
@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-8">
            <h1 class="m-0 text-dark fw-bold">
                <i class="fas fa-file-excel text-success mr-2"></i> Planilla Estilo Hoja de Cálculo
            </h1>
            <p class="text-muted mb-0 mt-1">
                {{-- BÚSQUEDA SEGURA DEL DOCENTE USANDO LA RELACIÓN ELOQUENTE DE LA OFERTA ACADÉMICA --}}
                @php
                    $nombreDocente = 'No asignado';
                    try {
                        $oferta = $programacion->ofertaAcademica;
                        if ($oferta) {
                            $historialVigente =
                                $oferta
                                    ->historialDocentes()
                                    ->whereNull('fecha_fin')
                                    ->with('docente.persona')
                                    ->first() ??
                                $oferta->historialDocentes()->orderBy('id', 'desc')->with('docente.persona')->first();

                            if (
                                $historialVigente &&
                                $historialVigente->docente &&
                                $historialVigente->docente->persona
                            ) {
                                $pDoc = $historialVigente->docente->persona;
                                $nombreDocente = trim(
                                    ($pDoc->nombres ?? '') .
                                        ' ' .
                                        ($pDoc->ap_paterno ?? '') .
                                        ' ' .
                                        ($pDoc->ap_materno ?? ''),
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        $nombreDocente = 'No asignado';
                    }

                    // Identificar etiqueta amigable del componente / evaluación
                    $nroParcial = $programacion->nro_parcial ?? 1;
                    $tipoComp = $programacion->tipo_componente ?? ($programacion->tipo ?? 'examen');

                    // Diccionario para mostrar nombres limpios y legibles
                    $nombresComponentes = [
                        'examen' => 'Examen Parcial',
                        'trabajo_practico' => 'Trabajo Práctico',
                        'practico' => 'Práctico',
                        'exposicion' => 'Exposición',
                        'proyecto' => 'Proyecto',
                        'evaluacion' => 'Evaluación',
                    ];
                    $etiquetaComponente = $nombresComponentes[$tipoComp] ?? ucwords(str_replace('_', ' ', $tipoComp));
                    $evaluacionTexto = "{$nroParcial}° Parcial - {$etiquetaComponente}";

                    if (isset($programacion->ponderacion_componente)) {
                        $evaluacionTexto .= " ({$programacion->ponderacion_componente}% del parcial)";
                    }
                @endphp
                <strong>Evaluación:</strong> <span class="text-success font-weight-bold">{{ $evaluacionTexto }}</span> |
                <strong>Docente:</strong> {{ $nombreDocente }} |
                <strong>Materia:</strong> {{ $programacion->ofertaAcademica->pensum->materia->nombre ?? 'Materia' }} |
                <strong>Paralelo:</strong> {{ $programacion->ofertaAcademica->paralelo->nombre ?? 'N/A' }}
            </p>
        </div>
        <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
            <span class="badge badge-success text-uppercase px-3 py-2 font-weight-normal mb-2 d-inline-block"
                style="font-size: 0.9rem;">
                <i class="fas fa-table mr-1"></i> Modo Hoja de Cálculo
            </span>
            <div>
                <a href="{{ route('docente.dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">

        {{-- Alerta si está bloqueado --}}
        @if ($programacion->bloqueado)
            <div class="alert alert-danger shadow-sm" role="alert">
                <i class="fas fa-lock mr-2"></i> Esta evaluación se encuentra <strong>bloqueada</strong> por la
                administración. No es posible realizar modificaciones.
            </div>
        @endif

        {{-- Formulario de Calificaciones --}}
        <form id="formCalificacionesDirectas">
            @csrf
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header bg-white py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="text-muted small">
                                <i class="fas fa-info-circle mr-1 text-info"></i> Usa las <strong>flechas del
                                    teclado</strong>, <strong>Enter</strong> o <strong>Tab</strong> para moverte rápidamente
                                entre las celdas.
                            </span>
                        </div>
                        <div class="col-md-6 text-md-right mt-2 mt-md-0">
                            <div class="input-group input-group-sm w-75 ml-auto">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="buscadorEstudiante" class="form-control"
                                    placeholder="Filtrar estudiante...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 68vh; overflow-y: auto;">
                        <table class="table table-bordered table-striped table-hover m-0 tabla-excel" id="tablaNotas">
                            <thead class="bg-success text-white text-center" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 5%; vertical-align: middle;">#</th>
                                    <th style="width: 15%; vertical-align: middle;">N° Registro</th>
                                    <th
                                        style="width: 35%; text-align: left !important; padding-left: 15px; vertical-align: middle;">
                                        Nombres y Apellidos</th>
                                    <th style="width: 20%; vertical-align: middle;">Nota (0 - 100)</th>
                                    <th style="width: 25%; vertical-align: middle;">Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- ORDENAMIENTO ESTRICTO: Apellido Paterno, Apellido Materno, Nombres --}}
                                @php
                                    $matriculacionesOrdenadas = $matriculaciones->sort(function ($a, $b) {
                                        $patA = strtolower($a->estudiante->persona->ap_paterno ?? '');
                                        $patB = strtolower($b->estudiante->persona->ap_paterno ?? '');
                                        if ($patA !== $patB) {
                                            return strcmp($patA, $patB);
                                        }

                                        $matA = strtolower($a->estudiante->persona->ap_materno ?? '');
                                        $matB = strtolower($b->estudiante->persona->ap_materno ?? '');
                                        if ($matA !== $matB) {
                                            return strcmp($matA, $matB);
                                        }

                                        $nomA = strtolower($a->estudiante->persona->nombres ?? '');
                                        $nomB = strtolower($b->estudiante->persona->nombres ?? '');
                                        return strcmp($nomA, $nomB);
                                    });
                                @endphp

                                @forelse($matriculacionesOrdenadas as $index => $mat)
                                    @php
                                        $calificacionParcial = $mat->calificacionesParciales->first();
                                        $tipoComp = $programacion->tipo_componente ?? ($programacion->tipo ?? 'examen');
                                        $detalle = $calificacionParcial
                                            ? $calificacionParcial->detalles
                                                ->where('tipo_componente', $tipoComp)
                                                ->first()
                                            : null;

                                        $notaActual = $detalle ? $detalle->nota : '';
                                        $obsActual = $detalle ? $detalle->observacion : '';

                                        $personaEst = $mat->estudiante->persona ?? null;
                                        $nombresEst = trim($personaEst->nombres ?? '');
                                        $paternoEst = trim($personaEst->ap_paterno ?? '');
                                        $maternoEst = trim($personaEst->ap_materno ?? '');

                                        $nombreCompleto = trim("{$paternoEst} {$maternoEst} {$nombresEst}");

                                    $registroUniversitario = $mat->estudiante->registro_universitario ?? 'S/N'; @endphp
                                    <tr class="fila-estudiante">
                                        <td class="text-center font-weight-bold text-muted align-middle bg-light">
                                            {{ $loop->iteration }}</td>
                                        <td class="text-center text-muted font-monospace small align-middle bg-light">
                                            {{ $registroUniversitario }}
                                        </td>
                                        <td class="font-weight-bold text-dark text-wrap align-middle">
                                            {{ $nombreCompleto ?: 'Sin Nombre Registrado' }}
                                        </td>
                                        {{-- Celda de Nota estilo Hoja de Cálculo --}}
                                        <td class="p-0 align-middle text-center position-relative">
                                            <input type="number" name="notas[{{ $mat->id }}]"
                                                value="{{ $notaActual }}"
                                                class="form-control text-center font-weight-bold input-celda input-nota border-0 rounded-0"
                                                data-col="0" min="0" max="100" step="0.01"
                                                placeholder="0.00"
                                                style="height: 45px; background: transparent; font-size: 1.1rem;"
                                                {{ $programacion->bloqueado ? 'disabled' : '' }}>
                                        </td>
                                        {{-- Celda de Observación estilo Hoja de Cálculo --}}
                                        <td class="p-0 align-middle position-relative">
                                            <input type="text" name="observaciones[{{ $mat->id }}]"
                                                value="{{ $obsActual }}"
                                                class="form-control input-celda input-obs border-0 rounded-0 px-3"
                                                data-col="1" placeholder="Opcional..."
                                                style="height: 45px; background: transparent;"
                                                {{ $programacion->bloqueado ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                                            <p class="mb-0">No hay estudiantes matriculados en esta oferta académica.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted font-weight-semibold">
                        <i class="fas fa-users mr-1"></i> Total estudiantes:
                        <strong>{{ $matriculaciones->count() }}</strong>
                    </span>
                    @if (!$programacion->bloqueado && $matriculaciones->count() > 0)
                        <button type="submit" id="btnGuardar" class="btn btn-success px-5 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-1"></i> Guardar Calificaciones
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
@stop

@push('css')
    <style>
        /* Estilos visuales para imitar una hoja de cálculo tipo Excel */
        .tabla-excel th {
            border-color: #28a745 !important;
        }

        .tabla-excel td {
            border-color: #dee2e6 !important;
            vertical-align: middle !important;
        }

        /* Al hacer foco en los inputs de nota u observación se activa el borde verde estilo Excel */
        .input-celda:focus {
            background-color: #fff !important;
            box-shadow: inset 0 0 0 2px #28a745 !important;
            outline: none;
        }

        .input-nota:focus {
            color: #155724;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Buscador rápido en tiempo real
            const buscador = document.getElementById('buscadorEstudiante');
            if (buscador) {
                buscador.addEventListener('keyup', function() {
                    const filtro = this.value.toLowerCase();
                    const filas = document.querySelectorAll('.fila-estudiante');

                    filas.forEach(fila => {
                        const textoFila = fila.textContent.toLowerCase();
                        if (textoFila.includes(filtro)) {
                            fila.style.display = '';
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                });
            }

            // 2. Navegación avanzada con teclado (Estilo Excel: Enter, Tab, Flechas)
            const filasTabla = document.querySelectorAll('.fila-estudiante');

            filasTabla.forEach((fila, rowIndex) => {
                const inputsEnFila = fila.querySelectorAll('.input-celda');

                inputsEnFila.forEach((input, colIndex) => {
                    input.addEventListener('keydown', function(e) {
                        let targetInput = null;
                        let rIndex = rowIndex;

                        if (['ArrowDown', 'Enter'].includes(e.key)) {
                            e.preventDefault();
                            let siguienteFila = filasTabla[rIndex + 1];
                            while (siguienteFila && siguienteFila.style.display ===
                                'none') {
                                rIndex++;
                                siguienteFila = filasTabla[rIndex + 1];
                            }
                            if (siguienteFila) {
                                targetInput = siguienteFila.querySelectorAll(
                                    '.input-celda')[colIndex];
                            }
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            let anteriorFila = filasTabla[rIndex - 1];
                            while (anteriorFila && anteriorFila.style.display === 'none') {
                                rIndex--;
                                anteriorFila = filasTabla[rIndex - 1];
                            }
                            if (anteriorFila) {
                                targetInput = anteriorFila.querySelectorAll('.input-celda')[
                                    colIndex];
                            }
                        } else if (e.key === 'ArrowRight') {
                            if (inputsEnFila[colIndex + 1]) {
                                e.preventDefault();
                                targetInput = inputsEnFila[colIndex + 1];
                            }
                        } else if (e.key === 'ArrowLeft') {
                            if (inputsEnFila[colIndex - 1]) {
                                e.preventDefault();
                                targetInput = inputsEnFila[colIndex - 1];
                            }
                        }

                        if (targetInput && !targetInput.disabled) {
                            targetInput.focus();
                            targetInput.select();
                        }
                    });
                });
            });

            // 3. Envío del formulario mediante AJAX
            const form = document.getElementById('formCalificacionesDirectas');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const btnGuardar = document.getElementById('btnGuardar');
                    if (btnGuardar) {
                        btnGuardar.disabled = true;
                        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...';
                    }

                    const formData = new FormData(this);

                    fetch("{{ route('docente.programacion.guardar-notas', $programacion->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                                if (btnGuardar) {
                                    btnGuardar.disabled = false;
                                    btnGuardar.innerHTML =
                                        '<i class="fas fa-save mr-1"></i> Guardar Calificaciones';
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Ocurrió un error inesperado al intentar guardar las notas.');
                            if (btnGuardar) {
                                btnGuardar.disabled = false;
                                btnGuardar.innerHTML =
                                    '<i class="fas fa-save mr-1"></i> Guardar Calificaciones';
                            }
                        });
                });
            }
        });
    </script>
@endpush
