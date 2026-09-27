@extends('adminlte::page')

@section('title', 'Planilla Ciega por Folios')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <a href="{{ route('docente.dashboard') }}" class="btn btn-secondary btn-sm mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Portal
                </a>
                <h1 class="m-0 fw-bold">Planilla Ciega por Folios</h1>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                @if ($programacion->bloqueado)
                    <span class="badge bg-danger p-2 fs-6">
                        <i class="bi bi-lock-fill me-1"></i> Calificación Bloqueada
                    </span>
                @else
                    <button type="button" id="btnGuardar" class="btn btn-success btn-lg shadow-sm">
                        <i class="bi bi-save me-1"></i> Guardar Calificaciones
                    </button>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- Usamos una fila centrada para que el contenido ocupe 8 de 12 columnas --}}
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">

                {{-- Tarjeta de Información Detallada Compacta --}}
                <div class="card card-outline card-primary mb-3 shadow-sm">
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">Asignatura</span>
                                <strong>{{ $programacion->ofertaAcademica->pensum->materia->nombre ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">Docente Titular</span>
                                @php
                                    $docenteHistorial = $programacion->ofertaAcademica->historialDocentes
                                        ->whereNull('fecha_fin')
                                        ->first();
                                    $personaDocente = $docenteHistorial->docente->persona ?? null;
                                    $nombreDocente = $personaDocente
                                        ? trim(
                                            $personaDocente->nombres .
                                                ' ' .
                                                $personaDocente->ap_paterno .
                                                ' ' .
                                                ($personaDocente->ap_materno ?? ''),
                                        )
                                        : 'No asignado';
                                @endphp
                                <strong class="text-dark">{{ $nombreDocente }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block small">Paralelo / Turno</span>
                                <strong>{{ $programacion->ofertaAcademica->paralelo->nombre ?? 'N/A' }}</strong>
                                <span
                                    class="text-muted">({{ $programacion->ofertaAcademica->turno->nombre ?? 'N/A' }})</span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block small">Periodo y Gestión</span>
                                <strong>{{ $programacion->ofertaAcademica->periodo->nombre ?? 'N/A' }}</strong> -
                                <strong>{{ $programacion->ofertaAcademica->periodo->gestion->nombre ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block small">Instancia / Evaluación</span>
                                <span class="badge bg-info text-dark text-uppercase">
                                    {{ $programacion->instancia ?? 'N/A' }}
                                </span>
                                <span
                                    class="text-muted small">({{ str_replace('_', ' ', $programacion->modalidad) }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contenedor de Alertas dinámicas --}}
                <div id="alertContainer"></div>

                {{-- Tarjeta Principal con el Listado de Folios Tipo Excel --}}
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title fs-6 fw-bold mb-0">
                            <i class="bi bi-journal-text me-1"></i> Registro de Notas (Usa <kbd>Enter</kbd> para bajar)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <form id="formNotas">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;" class="text-center py-2">#</th>
                                            <th style="width: 220px;" class="py-2">Código de Folio</th>
                                            <th style="width: 180px;" class="py-2">Estado</th>
                                            <th class="py-2 text-center">Nota (0 - 100)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($folios as $index =>$folio)
                                            <tr>
                                                <td class="text-center fw-bold text-muted py-1">{{ $index + 1 }}</td>
                                                <td class="py-1">
                                                    <span class="badge bg-dark font-monospace px-2 py-1">
                                                        {{ $folio->codigo_folio }}
                                                    </span>
                                                </td>
                                                <td class="py-1">
                                                    <span
                                                        class="badge bg-{{ $folio->nota !== null ? 'success' : 'warning text-dark' }}">
                                                        {{ $folio->estado_folio }}
                                                    </span>
                                                </td>
                                                <td class="py-1 text-center">
                                                    @if ($programacion->bloqueado)
                                                        <input type="number" value="{{ $folio->nota }}"
                                                            class="form-control form-control-sm text-center fw-bold mx-auto"
                                                            style="max-width: 130px;" disabled>
                                                    @else
                                                        <input type="number" name="notas[{{ $folio->id }}]"
                                                            value="{{ $folio->nota }}"
                                                            class="form-control form-control-sm input-nota text-center fw-bold mx-auto"
                                                            style="max-width: 130px;" min="0" max="100"
                                                            step="0.1" placeholder="0.0">
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted fst-italic">
                                                    No se han generado folios para esta evaluación.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnGuardar = document.getElementById('btnGuardar');
            const formNotas = document.getElementById('formNotas');
            const alertContainer = document.getElementById('alertContainer');

            // Navegación tipo Excel con la tecla ENTER
            const inputsNotas = document.querySelectorAll('.input-nota');

            inputsNotas.forEach((input, index) => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const siguienteInput = inputsNotas[index + 1];
                        if (siguienteInput) {
                            siguienteInput.focus();
                            siguienteInput.select();
                        } else if (btnGuardar) {
                            btnGuardar.focus();
                        }
                    }
                });
            });

            // Guardar calificaciones vía Fetch AJAX
            if (btnGuardar) {
                btnGuardar.addEventListener('click', function() {
                    btnGuardar.disabled = true;
                    btnGuardar.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';

                    const formData = new FormData(formNotas);

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
                            btnGuardar.disabled = false;
                            btnGuardar.innerHTML =
                                '<i class="bi bi-save me-1"></i> Guardar Calificaciones';

                            if (data.success) {
                                alertContainer.innerHTML = `
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle-fill me-2"></i>${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                `;
                                window.scrollTo({
                                    top: 0,
                                    behavior: 'smooth'
                                });
                            } else {
                                alertContainer.innerHTML = `
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>${data.message || 'Error al procesar la solicitud.'}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                `;
                            }
                        })
                        .catch(error => {
                            btnGuardar.disabled = false;
                            btnGuardar.innerHTML =
                                '<i class="bi bi-save me-1"></i> Guardar Calificaciones';
                            alertContainer.innerHTML = `
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Ocurrió un error inesperado.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `;
                        });
                });
            }
        });
    </script>
@endpush
