@extends('adminlte::page')

@section('title', 'Control de Asistencias')

@section('content_header')
    <h1>Control de Asistencia por Oferta Académica</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- 1. Formulario de Filtro Estilizado -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body py-3">
            <form action="{{ route('admin.asistencias.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-6 form-group mb-0">
                    <label class="small text-muted font-weight-bold">OFERTA ACADÉMICA:</label>
                    <select name="oferta_id" class="form-control form-control-sm select2" required>
                        <option value="">-- Seleccione una materia --</option>
                        @foreach ($ofertas as $of)
                            @php
                                $sigla = $of->pensum->materia->sigla ?? 'S/S';
                                $materia = $of->pensum->materia->nombre ?? 'Desconocida';
                                $grado = $of->pensum->grado->nombre ?? '';
                                $paralelo = $of->paralelo->nombre ?? 'N/A';
                                $turno = $of->turno->nombre ?? 'N/A';
                            @endphp
                            <option value="{{ $of->id }}" {{ $ofertaId == $of->id ? 'selected' : '' }}>
                                [{{ $sigla }}] {{ $materia }} — {{ $grado }} | Paralelo:
                                {{ $paralelo }} ({{ $turno }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group mb-0">
                    <label class="small text-muted font-weight-bold">FECHA:</label>
                    <input type="date" name="fecha" class="form-control form-control-sm" value="{{ $fecha }}"
                        required>
                </div>
                <div class="col-md-2 form-group mb-0">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-search mr-1"></i> Cargar Lista
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. DataGrid Compacto de Asistencia -->
    @if ($ofertaId)
        @php
            $idPresente =
                $estados->first(
                    fn($e) => str_contains(strtolower($e->nombre), 'presente') ||
                        str_contains(strtolower($e->slug), 'presente'),
                )?->id ??
                ($estados[0]->id ?? 1);

            $idAusente =
                $estados->first(
                    fn($e) => str_contains(strtolower($e->nombre), 'ausente') ||
                        str_contains(strtolower($e->nombre), 'falta') ||
                        str_contains(strtolower($e->slug), 'ausente') ||
                        str_contains(strtolower($e->slug), 'falta'),
                )?->id ??
                ($estados[1]->id ?? 2);

            // Verificamos si ya existe asistencia registrada para al menos el primer estudiante en esta fecha
            $primerMatriculado = $matriculados->first();
            $yaRegistrado = $primerMatriculado && $primerMatriculado->asistencias->isNotEmpty();
        @endphp

        <form action="{{ route('admin.asistencias.store-masiva') }}" method="POST" id="form-asistencia">
            @csrf
            <input type="hidden" name="oferta_id" value="{{ $ofertaId }}">
            <input type="hidden" name="fecha" value="{{ $fecha }}">
            <input type="hidden" id="id-presente" value="{{ $idPresente }}">
            <input type="hidden" id="id-ausente" value="{{ $idAusente }}">

            <div class="card card-outline {{ $yaRegistrado ? 'card-info' : 'card-success' }} shadow-sm">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-sm m-0">
                        <i class="fas fa-users mr-1"></i> Estudiantes Matriculados: <span
                            class="badge {{ $yaRegistrado ? 'badge-info' : 'badge-success' }}">{{ $matriculados->count() }}</span>
                        @if ($yaRegistrado)
                            <span class="ml-2 badge badge-warning text-dark"><i class="fas fa-lock"></i> Asistencia ya
                                registrada (Modo Lectura)</span>
                        @endif
                    </h3>
                    <div>
                        @if ($yaRegistrado)
                            <button type="button" id="btn-habilitar-edicion" class="btn btn-xs btn-outline-warning mr-2">
                                <i class="fas fa-edit"></i> Habilitar Edición
                            </button>
                        @endif
                        <button type="button" id="btn-marcar-todos" class="btn btn-xs btn-outline-info"
                            {{ $yaRegistrado ? 'disabled' : '' }}>
                            <i class="fas fa-check-double"></i> Marcar Todos Presentes
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($matriculados->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-striped text-sm mb-0"
                                style="vertical-align: middle;">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="text-center py-2" style="width: 40px;">#</th>
                                        <th class="py-2">Estudiante</th>
                                        <th class="text-center py-2" style="width: 100px;">Presente</th>
                                        <th class="py-2" style="width: 35%;">Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($matriculados as $index => $mat)
                                        @php
                                            $p = $mat->estudiante->persona ?? null;
                                            $nombre = $p ? "{$p->ap_paterno} {$p->ap_materno}, {$p->nombres}" : 'S/N';
                                            $ci = $p->ci ?? 'S/C';
                                            $ru = $mat->estudiante->registro_universitario ?? 'S/RU';

                                            $asistenciaAnterior = $mat->asistencias->first();
                                            $esPresente = $asistenciaAnterior
                                                ? $asistenciaAnterior->estado_id == $idPresente
                                                : true;
                                        @endphp
                                        <tr>
                                            <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>

                                            <td class="py-2">
                                                <div class="d-flex flex-column">
                                                    <span class="font-weight-bold text-dark">{{ $nombre }}</span>
                                                    <div class="text-muted" style="font-size: 80%;">
                                                        <span class="badge badge-light border px-1 text-secondary">CI:
                                                            {{ $ci }}</span>
                                                        <span class="badge badge-light border px-1 text-secondary ml-1">RU:
                                                            {{ $ru }}</span>
                                                    </div>
                                                </div>
                                                <input type="hidden"
                                                    name="asistencias[{{ $index }}][matriculacion_id]"
                                                    value="{{ $mat->id }}">
                                            </td>

                                            <td class="text-center py-2 align-middle">
                                                <div
                                                    class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="checkbox" class="custom-control-input check-asistencia"
                                                        id="chk-{{ $index }}" data-index="{{ $index }}"
                                                        {{ $esPresente ? 'checked' : '' }}
                                                        {{ $yaRegistrado ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="chk-{{ $index }}"
                                                        style="cursor: pointer;"></label>
                                                </div>
                                                <input type="hidden" name="asistencias[{{ $index }}][estado_id]"
                                                    id="estado-id-{{ $index }}"
                                                    value="{{ $esPresente ? $idPresente : $idAusente }}">
                                            </td>

                                            <td class="py-2 align-middle">
                                                <input type="text" name="asistencias[{{ $index }}][observacion]"
                                                    class="form-control form-control-xs"
                                                    value="{{ $asistenciaAnterior->observacion ?? '' }}"
                                                    placeholder="Observación opcional..."
                                                    {{ $yaRegistrado ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">No hay estudiantes matriculados en esta oferta.</div>
                    @endif
                </div>
                @if ($matriculados->count() > 0)
                    <div class="card-footer py-2 text-right bg-light" id="card-footer-guardar"
                        style="{{ $yaRegistrado ? 'display: none;' : '' }}">
                        <button type="submit" class="btn btn-success btn-sm px-4">
                            <i class="fas fa-save mr-1"></i> Guardar Asistencia
                        </button>
                    </div>
                @endif
            </div>
        </form>
    @endif
@stop

@section('css')
    <style>
        .table-sm td,
        .table-sm th {
            padding: 0.35rem 0.5rem !important;
        }

        .form-control-xs {
            height: calc(1.5em + 0.3rem + 2px);
            padding: 0.15rem 0.4rem;
            font-size: 0.8rem;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idPresente = document.getElementById('id-presente')?.value;
            const idAusente = document.getElementById('id-ausente')?.value;

            document.querySelectorAll('.check-asistencia').forEach(chk => {
                chk.addEventListener('change', function() {
                    const idx = this.getAttribute('data-index');
                    document.getElementById(`estado-id-${idx}`).value = this.checked ? idPresente :
                        idAusente;
                });
            });

            const btnMarcarTodos = document.getElementById('btn-marcar-todos');
            if (btnMarcarTodos) {
                btnMarcarTodos.addEventListener('click', function() {
                    document.querySelectorAll('.check-asistencia').forEach(chk => {
                        chk.checked = true;
                        chk.dispatchEvent(new Event('change'));
                    });
                });
            }

            // Lógica para el botón de Habilitar Edición
            const btnHabilitarEdicion = document.getElementById('btn-habilitar-edicion');
            if (btnHabilitarEdicion) {
                btnHabilitarEdicion.addEventListener('click', function() {
                    // Habilitar todos los checkboxes y campos de observación
                    document.querySelectorAll('.check-asistencia').forEach(chk => chk.removeAttribute(
                        'disabled'));
                    document.querySelectorAll('input[name*="[observacion]"]').forEach(inp => inp
                        .removeAttribute('disabled'));

                    // Mostrar el botón de guardar
                    const cardFooter = document.getElementById('card-footer-guardar');
                    if (cardFooter) {
                        cardFooter.style.display = 'block';
                    }

                    // Habilitar botón de marcar todos
                    if (btnMarcarTodos) {
                        btnMarcarTodos.removeAttribute('disabled');
                    }

                    // Ocultar este botón de habilitar edición tras hacer clic
                    this.style.display = 'none';
                });
            }
        });
    </script>
@stop
