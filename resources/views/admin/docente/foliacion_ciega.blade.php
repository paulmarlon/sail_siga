@extends('adminlte::page')

@section('title', 'Estación de Foliado y Notas - Docente')

@section('content_header')
    {{-- Tarjeta de cabecera compacta a 3 columnas --}}
    <div class="card card-outline card-purple shadow-sm mb-2">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center">

                {{-- Columna 1: Información de la Materia y Sigla --}}
                <div class="col-md-4 border-right">
                    <div class="d-flex align-items-center">
                        <div class="bg-purple p-2 rounded mr-2 text-white shadow-sm" style="background-color: #6f42c1;">
                            <i class="fas fa-book fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">
                                {{ $programacion->ofertaAcademica->pensum->materia->nombre ?? 'Materia no asignada' }}
                            </h5>
                            <span class="text-muted" style="font-size: 0.8rem;">
                                Sigla:
                                <strong>{{ $programacion->ofertaAcademica->pensum->materia->sigla ?? 'N/D' }}</strong> |
                                Paralelo: <strong>{{ $programacion->ofertaAcademica->paralelo->nombre ?? 'N/D' }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Columna 2: Datos Académicos (Semestre, Turno, Instancia) --}}
                <div class="col-md-4 border-right px-3">
                    <div style="font-size: 0.82rem;" class="text-secondary">
                        <div><i class="fas fa-layer-group mr-1 text-purple"></i> Semestre/Grado: <strong
                                class="text-dark">{{ $programacion->ofertaAcademica->pensum->grado->nombre ?? 'N/D' }}</strong>
                        </div>
                        <div><i class="fas fa-clock mr-1 text-purple"></i> Turno: <strong
                                class="text-dark">{{ $programacion->ofertaAcademica->turno->nombre ?? 'N/D' }}</strong> |
                            Instancia: <span class="badge badge-purple"
                                style="background-color: #6f42c1; color: #fff;">{{ $programacion->instancia }}</span></div>
                    </div>
                </div>

                {{-- Columna 3: Gestión y Botón de Cambio --}}
                <div class="col-md-4 text-right">
                    <div class="mb-1" style="font-size: 0.82rem;">
                        <i class="fas fa-calendar-alt mr-1 text-purple"></i> Gestión: <strong
                            class="text-dark">{{ $programacion->ofertaAcademica->gestion->nombre ?? 'Actual' }}</strong>
                    </div>
                    <a href="{{ route('docente.seleccionar-materia') }}"
                        class="btn btn-secondary btn-xs font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Cambiar Materia
                    </a>
                </div>

            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 mb-2" role="alert"
                    style="font-size: 0.88rem;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    <button type="button" class="close py-1" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 mb-2" role="alert"
                    style="font-size: 0.88rem;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                    <button type="button" class="close py-1" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('docente.foliacion.actualizar', $programacion->id) }}" method="POST"
                id="form-excel-notas">
                @csrf
                <div class="card card-outline card-purple shadow-sm">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold text-dark" style="font-size: 0.9rem;">
                            <i class="fas fa-table text-purple mr-1"></i> MATRIZ DE CALIFICACIÓN ANÓNIMA
                        </h3>

                        <div class="d-flex align-items-center" style="font-size: 0.85rem;">
                            <!-- AQUI PEGAS EL BOTÓN -->
                            <a href="{{ route('docente.examenes.calificados', $programacion->id) }}"
                                class="btn btn-info btn-sm font-weight-bold shadow-sm mr-2">
                                <i class="fas fa-list-alt mr-1"></i> Ver Resumen Calificado
                            </a>

                            <span class="badge badge-success px-2 py-1 mr-2 shadow-sm">
                                Aprobados: <strong id="count-aprobados">0</strong>
                            </span>
                            <span class="badge badge-danger px-2 py-1 mr-3 shadow-sm">
                                Aplazados: <strong id="count-aplazados">0</strong>
                            </span>
                            <button type="submit" class="btn btn-purple btn-sm font-weight-bold shadow-sm px-3"
                                style="background-color: #6f42c1; color: #fff;">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 62vh;">
                            <table class="table table-bordered table-striped table-sm m-0 align-middle excel-table"
                                style="font-size: 0.88rem;">
                                <thead class="bg-light text-center sticky-top" style="z-index: 10;">
                                    <tr>
                                        <th width="60px" class="py-2">N°</th>
                                        <th class="py-2 text-left px-3">CÓDIGO DE FOLIO (Solo Lectura)</th>
                                        <th width="160px" class="py-2">ESTADO</th>
                                        <th width="160px" class="py-2">NOTA (Mín. 1 si asistió)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($folios as $index => $folio)
                                        <tr>
                                            <td class="text-center font-weight-bold align-middle bg-light">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="align-middle px-3">
                                                <input type="hidden" name="folios[{{ $index + 1 }}][estudiante_id]"
                                                    value="{{ $folio->estudiante_id }}">

                                                <input type="text" name="folios[{{ $index + 1 }}][codigo_folio]"
                                                    class="form-control form-control-sm font-weight-bold text-uppercase bg-light border-0"
                                                    value="{{ $folio->codigo_folio }}" readonly tabindex="-1">
                                            </td>
                                            <td class="text-center align-middle">
                                                <span
                                                    class="badge badge-status
                                                    @if ($folio->estado_folio === 'Calificado') badge-success
                                                    @elseif($folio->estado_folio === 'Foliado_Y_Separado') badge-info
                                                    @else badge-secondary @endif">
                                                    {{ $folio->estado_folio ?? 'Pendiente' }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle p-1">
                                                <input type="number" step="1" min="0" max="100"
                                                    name="folios[{{ $index + 1 }}][nota]"
                                                    class="form-control form-control-sm text-center font-weight-bold input-nota"
                                                    value="{{ $folio->nota }}" placeholder="0-100" autocomplete="off">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-exclamation-circle mr-1"></i> No existen folios generados
                                                previamente por el administrador.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-light text-right py-2">
                        <button type="submit" class="btn btn-purple btn-sm font-weight-bold shadow-sm px-3"
                            style="background-color: #6f42c1; color: #fff;">
                            <i class="fas fa-save mr-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@stop

@section('js')
    <style>
        /* Estilo para notas menores a 50.5 (texto rojo) */
        .text-reprobado {
            color: #dc3545 !important;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputs = Array.from(document.querySelectorAll(".input-nota"));
            const lblAprobados = document.getElementById("count-aprobados");
            const lblAplazados = document.getElementById("count-aplazados");

            function actualizarContadoresYEstilos() {
                let aprobados = 0;
                let aplazados = 0;

                inputs.forEach(input => {
                    let val = parseFloat(input.value);
                    if (!isNaN(input.value) && input.value.trim() !== "") {
                        if (val < 50.5) {
                            input.classList.add("text-reprobado");
                            input.style.fontWeight = "bold";
                            // Solo cuenta como aplazado si tiene nota válida de asistencia (ej. >= 1)
                            if (val >= 1) aplazados++;
                        } else {
                            input.classList.remove("text-reprobado");
                            if (val <= 100) aprobados++;
                        }
                    } else {
                        input.classList.remove("text-reprobado");
                    }
                });

                lblAprobados.textContent = aprobados;
                lblAplazados.textContent = aplazados;
            }

            // Ejecutar al cargar por si la BD ya trae notas registradas
            actualizarContadoresYEstilos();

            inputs.forEach((input, index) => {
                input.addEventListener("focus", function() {
                    this.select();
                    this.closest("tr").style.backgroundColor = "#e8eaf6";
                });

                input.addEventListener("blur", function() {
                    this.closest("tr").style.backgroundColor = "";
                });

                input.addEventListener("keydown", function(e) {
                    let targetInput = null;

                    if (e.key === "Enter" || e.key === "ArrowDown") {
                        e.preventDefault();
                        if (inputs[index + 1]) targetInput = inputs[index + 1];
                    } else if (e.key === "ArrowUp") {
                        e.preventDefault();
                        if (inputs[index - 1]) targetInput = inputs[index - 1];
                    }

                    if (targetInput) {
                        targetInput.focus();
                        targetInput.select();
                    }
                });

                input.addEventListener("input", function() {
                    let val = parseFloat(this.value);
                    const badge = this.closest("tr").querySelector(".badge-status");

                    // Actualizar colores y contadores globales en vivo
                    actualizarContadoresYEstilos();

                    if (!isNaN(val)) {
                        if (val > 100) this.value = 100;
                        if (val < 0) this.value = 0;

                        if (val >= 1 && val <= 100) {
                            badge.className = "badge badge-status badge-success";
                            badge.textContent = "Calificado";
                        } else if (val === 0) {
                            badge.className = "badge badge-status badge-info";
                            badge.textContent = "Foliado";
                        }
                    } else {
                        badge.className = "badge badge-status badge-secondary";
                        badge.textContent = "Pendiente";
                    }
                });
            });

            const form = document.getElementById("form-excel-notas");
            form.addEventListener("submit", function(e) {
                let errorEncontrado = false;
                inputs.forEach(input => {
                    let val = input.value.trim();
                    if (val !== "") {
                        let num = parseFloat(val);
                        if (num > 0 && num < 1) {
                            errorEncontrado = true;
                            input.style.border = "2px solid red";
                        } else {
                            input.style.border = "";
                        }
                    }
                });

                if (errorEncontrado) {
                    e.preventDefault();
                    alert(
                        "Atención: Si el estudiante rindió el examen, la nota mínima de calificación es 1."
                    );
                }
            });
        });
    </script>
@stop
