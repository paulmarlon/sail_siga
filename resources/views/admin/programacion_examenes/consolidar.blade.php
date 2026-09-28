@extends('adminlte::page')

@section('title', 'Consolidación de Notas')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-clipboard-check text-primary"></i> Consolidación de Notas por Folios
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.programacion-examenes.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> <strong>Volver</strong>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Información General de la Programación -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="callout callout-info shadow-sm mb-0">
                    <p class="mb-0">
                        Materia: <strong>{{ $programacion->ofertaAcademica->pensum->materia->nombre ?? 'N/A' }}</strong>
                        ({{ $programacion->ofertaAcademica->pensum->materia->sigla ?? 'S/S' }}) |
                        Instancia: <span class="badge badge-primary">{{ $programacion->instancia }}</span> |
                        Modalidad: <span class="badge badge-info">{{ ucfirst($programacion->modalidad) }}</span>
                    </p>
                </div>
            </div>
        </div>

        @if ($programacion->bloqueado)
            <div class="alert alert-warning shadow-sm" role="alert">
                <i class="fas fa-lock"></i> <strong>Examen Bloqueado:</strong> Este examen ya ha sido consolidado y cerrado
                oficialmente. No se pueden realizar modificaciones.
            </div>
        @endif

        @php
            $tieneNotasRegistradas = $folios->contains(function ($folio) {
                return $folio->nota !== null;
            });
        @endphp

        @if (!$programacion->bloqueado && !$tieneNotasRegistradas)
            <div class="alert alert-danger shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <strong>Atención:</strong> Ningún folio cuenta con una nota
                registrada todavía. Los docentes deben ingresar las calificaciones antes de poder realizar la consolidación
                oficial.
            </div>
        @endif

        <!-- Tabla de Folios y Notas -->
        <div class="card card-outline card-primary shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">Listado de Folios y Calificaciones Registradas</h3>

                <div class="card-tools">
                    @if (!$programacion->bloqueado && $tieneNotasRegistradas)
                        <button type="button" id="btnConsolidar" class="btn btn-success btn-sm shadow-sm">
                            <i class="fas fa-check-circle"></i> Consolidar Notas Oficiales
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-nowrap align-middle" id="tablaFolios"
                        width="100%">
                        <thead class="thead-dark" style="font-size: 0.85rem;">
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>Código de Folio</th>
                                <th>Estudiante</th>
                                <th class="text-center">R.U.</th>
                                <th class="text-center">Nota (Folio)</th>
                                <th class="text-center">Estado del Folio</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($folios as $index => $folio)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><strong>{{ $folio->codigo_folio }}</strong></td>
                                    <td>
                                        {{ $folio->estudiante->persona->ap_paterno ?? '' }}
                                        {{ $folio->estudiante->persona->ap_materno ?? '' }}
                                        {{ $folio->estudiante->persona->nombres ?? 'Estudiante no encontrado' }}
                                    </td>
                                    <td class="text-center">{{ $folio->estudiante->registro_universitario ?? 'S/R' }}</td>
                                    <td class="text-center">
                                        @if ($folio->nota !== null)
                                            <span class="badge badge-success px-2 py-1"
                                                style="font-size: 0.9rem;">{{ number_format($folio->nota, 2) }}</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">Sin calificar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge
                                        @if ($folio->estado_folio == 'Consolidado') badge-success
                                        @elseif($folio->estado_folio == 'Calificado') badge-info
                                        @else badge-secondary @endif">
                                            {{ $folio->estado_folio }}
                                        </span>
                                    </td>
                                    <td>{{ $folio->observacion ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No hay folios generados para esta
                                        programación.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.getElementById('btnConsolidar')?.addEventListener('click', function() {
            if (!confirm(
                    '¿Está seguro de consolidar oficialmente estas notas? Esta acción trasladará los registros a la libreta de los estudiantes, calculará los promedios ponderados y bloqueará el examen.'
                    )) {
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Consolidando...';

            fetch("{{ route('admin.calificaciones.consolidar', $programacion->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({
                    status: response.status,
                    body: data
                })))
                .then(res => {
                    if (res.status === 200 && res.body.success) {
                        alert(res.body.message);
                        location.reload();
                    } else {
                        alert('Aviso: ' + (res.body.message || 'Ocurrió un error inesperado.'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> Consolidar Notas Oficiales';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de red al intentar procesar la consolidación.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Consolidar Notas Oficiales';
                });
        });
    </script>
@endpush
