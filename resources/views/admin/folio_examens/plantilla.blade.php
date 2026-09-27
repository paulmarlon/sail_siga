@extends('adminlte::page')

@section('title', 'Foliación de Exámenes')

@section('css')
    <style>
        .excel-grid td {
            vertical-align: middle !important;
        }

        .excel-input {
            border: 1px solid #ced4da;
            text-align: center;
            background-color: #fff;
        }

        .excel-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background-color: #fffef0;
        }

        .input-duplicado {
            background-color: #f8d7da !important;
            border-color: #dc3545 !important;
            color: #721c24 !important;
        }

        .fila-duplicada {
            background-color: rgba(220, 53, 69, 0.08) !important;
        }

        .ocultar-bolsa {
            display: none !important;
        }

        .suggestion-item {
            padding: 6px 10px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            font-size: 0.8rem;
        }

        .suggestion-item:hover,
        .suggestion-item.active {
            background-color: #007bff;
            color: white;
        }

        .bolsa-flotante {
            position: sticky;
            top: 70px;
            z-index: 1020;
            max-height: calc(100vh - 90px);
            overflow-y: auto;
        }
    </style>
@stop

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-list-ol mr-2"></i> Foliación de Exámenes</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button type="button" onclick="history.back()" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- Tarjeta de Información General --}}
        {{-- Tarjeta de Información General --}}
        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-2">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Asignatura:</strong> {{ $programacion->ofertaAcademica->pensum->materia->nombre ?? 'N/A' }}
                    </div>
                    <div class="col-md-2">
                        <strong>Paralelo:</strong> {{ $programacion->ofertaAcademica->paralelo->nombre ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        {{-- Aquí añadimos la instancia (Primer Parcial, Final, etc.) --}}
                        <strong>Instancia:</strong> <span
                            class="badge badge-info">{{ $programacion->instancia ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-2">
                        <strong>Gestión:</strong> {{ $programacion->ofertaAcademica->periodo->gestion->nombre ?? 'N/A' }}
                    </div>
                    <div class="col-md-2 text-right">
                        @if ($programacion->bloqueado)
                            <span class="badge badge-danger p-2"><i class="fas fa-lock mr-1"></i> Bloqueado</span>
                        @else
                            <span class="badge badge-success p-2"><i class="fas fa-unlock mr-1"></i> Editable</span>
                        @endif
                    </div>
                </div>

                {{-- Fila del Docente --}}
                <div class="row mt-2 border-top pt-2">
                    <div class="col-md-12">
                        <strong>Docente:</strong>
                        @php
                            $historialDocente = $programacion->ofertaAcademica->ofertaDocenteHistorial->last();
                            $personaDocente = $historialDocente?->docente?->persona;
                            $nombreDocente = $personaDocente
                                ? trim(
                                    $personaDocente->nombres .
                                        ' ' .
                                        $personaDocente->ap_paterno .
                                        ' ' .
                                        $personaDocente->ap_materno,
                                )
                                : 'No asignado';
                        @endphp
                        {{ $nombreDocente }}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- COLUMNA IZQUIERDA: Grilla de Foliación Estilo Excel --}}
            <div class="col-lg-7">
                <div class="card card-primary card-outline">
                    <div class="card-header bg-light py-2">
                        <h3 class="card-title font-weight-bold text-sm">
                            <i class="fas fa-table mr-1"></i> Grilla de Casillas (Total: {{ $totalCasillas }})
                        </h3>
                        <div class="card-tools">
                            @if (!$programacion->bloqueado)
                                <button type="button" class="btn btn-success btn-sm" id="btnGuardarMasivo">
                                    <i class="fas fa-save mr-1"></i> Guardar Toda la Foliación
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered excel-grid mb-0">
                            <thead class="thead-dark sticky-top">
                                <tr>
                                    <th style="width: 10%" class="text-center">FOLIO</th>
                                    <th style="width: 35%" class="text-center">Cód. RU / Estudiante</th>
                                    <th style="width: 55%">Nombre Completo del Estudiante</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoGrilla">
                                @for ($i = 1; $i <= $totalCasillas; $i++)
                                    @php
                                        $folioExistente = $foliosAsignados[$i] ?? null;
                                        $estudianteAsignado = $folioExistente ? $folioExistente->estudiante : null;

                                        // Leemos el registro universitario correcto de la BD
                                        $ruVal = $estudianteAsignado
                                            ? $estudianteAsignado->registro_universitario ?? ''
                                            : '';

                                        // Construimos el nombre completo cruzando con la relación persona
                                        $nombreVal = '';
                                        if ($estudianteAsignado && $estudianteAsignado->persona) {
                                            $nombres = $estudianteAsignado->persona->nombres ?? '';
                                            $paterno = $estudianteAsignado->persona->ap_paterno ?? '';
                                            $materno = $estudianteAsignado->persona->ap_materno ?? '';
                                            $nombreVal = trim("$nombres $paterno $materno");
                                        }
                                    @endphp
                                    <tr data-pos="{{ $i }}">
                                        <td class="text-center font-weight-bold bg-light">
                                            {{ str_pad($i, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="position-relative">
                                            <input type="text" class="form-control form-control-sm excel-input input-ru"
                                                data-pos="{{ $i }}" value="{{ $ruVal }}"
                                                placeholder="RU..." {{ $programacion->bloqueado ? 'disabled' : '' }}>
                                            {{-- Contenedor de autocompletado flotante --}}
                                            <div id="sugg-{{ $i }}"
                                                class="dropdown-suggestions position-absolute w-100 shadow bg-white border rounded"
                                                style="display: none; z-index: 1050; max-height: 150px; overflow-y: auto;">
                                            </div>
                                        </td>
                                        <td>
                                            <span id="lbl-nombre-{{ $i }}"
                                                class="text-sm font-weight-bold text-dark">
                                                {{ $nombreVal }}
                                            </span>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Bolsa de Estudiantes del Paralelo --}}
            <div class="col-lg-5">
                <div class="card card-secondary card-outline bolsa-flotante">
                    <div class="card-header bg-light py-2">
                        <h3 class="card-title font-weight-bold text-sm">
                            <i class="fas fa-users mr-1"></i> Estudiantes Disponibles (<span id="contadorBolsa">0</span>)
                        </h3>
                    </div>
                    <div class="card-body p-2">
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" id="buscarEstudiante" class="form-control"
                                placeholder="Buscar por nombre o RU...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 52vh; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <tbody id="listaBolsa">
                                    @forelse($estudiantesParalelo as $estudiante)
                                        @php
                                            $searchString = strtolower(
                                                ($estudiante['ru'] ?? '') .
                                                    ' ' .
                                                    ($estudiante['nombres'] ?? '') .
                                                    ' ' .
                                                    ($estudiante['apellidos'] ?? ''),
                                            );
                                        @endphp
                                        <tr class="fila-estudiante" data-reg="{{ $estudiante['ru'] }}"
                                            data-search="{{ $searchString }}">
                                            <td style="width: 25%" class="align-middle">
                                                <strong class="text-sm">{{ $estudiante['ru'] }}</strong>
                                            </td>
                                            <td style="width: 60%" class="align-middle nombre-col text-sm">
                                                {{ $estudiante['nombres'] ?? '' }} {{ $estudiante['apellidos'] ?? '' }}
                                            </td>
                                            <td style="width: 15%" class="text-center align-middle">
                                                @if (!$programacion->bloqueado)
                                                    <button type="button" class="btn btn-xs btn-primary btn-asignar"
                                                        title="Asignar a casilla libre">
                                                        <i class="fas fa-arrow-left"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No hay estudiantes
                                                inscritos en este paralelo.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(function() {
            let estaBloqueado = @json($programacion->bloqueado);

            actualizarBolsaYContador();

            // Si está bloqueado, detenemos toda la interactividad de edición
            if (estaBloqueado) return;

            // 1. Navegación por teclado estilo Excel
            $(document).on('keydown', '.input-ru', function(e) {
                let currentInput = $(this);
                let currentPos = parseInt(currentInput.data('pos'));
                let suggBox = $(`#sugg-${currentPos}`);

                if (suggBox.is(':visible')) {
                    let items = suggBox.find('.suggestion-item');
                    let activeItem = suggBox.find('.suggestion-item.active');

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (activeItem.length === 0) {
                            items.first().addClass('active');
                        } else {
                            activeItem.removeClass('active');
                            let next = activeItem.next();
                            if (next.length) next.addClass('active');
                            else items.first().addClass('active');
                        }
                        return;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (activeItem.length > 0) {
                            activeItem.removeClass('active');
                            let prev = activeItem.prev();
                            if (prev.length) prev.addClass('active');
                            else items.last().addClass('active');
                        }
                        return;
                    } else if (e.key === 'Enter' && activeItem.length > 0) {
                        e.preventDefault();
                        activeItem.click();
                        return;
                    }
                }

                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    let nextInput = $(`#cuerpoGrilla tr[data-pos="${currentPos + 1}"] .input-ru`);
                    if (nextInput.length) {
                        nextInput.focus().select();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    let prevInput = $(`#cuerpoGrilla tr[data-pos="${currentPos - 1}"] .input-ru`);
                    if (prevInput.length) {
                        prevInput.focus().select();
                    }
                }
            });

            // 2. Autocompletado inteligente
            $(document).on('input', '.input-ru', function() {
                let input = $(this);
                let val = input.val().trim();
                let pos = input.data('pos');
                let suggBox = $(`#sugg-${pos}`);

                if (val === "") {
                    $(`#lbl-nombre-${pos}`).text('');
                    suggBox.hide().empty();
                    actualizarBolsaYContador();
                    return;
                }

                let matches = [];
                $('.fila-estudiante').each(function() {
                    let reg = $(this).data('reg').toString().trim();
                    let nombre = $(this).find('.nombre-col').text();

                    let ultimosCuatro = reg.slice(-4);
                    if (reg.includes(val) || ultimosCuatro.includes(val)) {
                        matches.push({
                            reg: reg,
                            nombre: nombre
                        });
                    }
                });

                if (matches.length > 0) {
                    let html = '';
                    matches.forEach((m, idx) => {
                        let activeClass = idx === 0 ? 'active' : '';
                        html += `<div class="suggestion-item ${activeClass}" data-reg="${m.reg}" data-nombre="${m.nombre}">
                                    <strong>${m.reg}</strong> - ${m.nombre}
                               </div>`;
                    });
                    suggBox.html(html).show();

                    let exactMatch = matches.find(m => m.reg === val || m.reg.slice(-4) === val);
                    if (exactMatch && matches.length === 1) {
                        input.val(exactMatch.reg);
                        $(`#lbl-nombre-${pos}`).text(exactMatch.nombre);
                        suggBox.hide();
                        actualizarBolsaYContador();

                        let nextInput = $(`#cuerpoGrilla tr[data-pos="${pos + 1}"] .input-ru`);
                        if (nextInput.length) nextInput.focus().select();
                    }
                } else {
                    suggBox.hide().empty();
                    $(`#lbl-nombre-${pos}`).text('No encontrado');
                }
                actualizarBolsaYContador();
            });

            // 3. Selección al hacer clic en sugerencia
            $(document).on('click', '.suggestion-item', function() {
                let item = $(this);
                let reg = item.data('reg');
                let nombre = item.data('nombre');

                let activeRow = item.closest('tr');
                let pos = activeRow.data('pos');

                let input = $(`#cuerpoGrilla tr[data-pos="${pos}"] .input-ru`);
                input.val(reg);
                $(`#lbl-nombre-${pos}`).text(nombre);

                $(`#sugg-${pos}`).hide().empty();
                actualizarBolsaYContador();

                let nextInput = $(`#cuerpoGrilla tr[data-pos="${pos + 1}"] .input-ru`);
                if (nextInput.length) nextInput.focus().select();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.excel-grid').length) {
                    $('.dropdown-suggestions').hide();
                }
            });

            // 4. Asignación desde la bolsa
            $(document).on('click', '.btn-asignar', function() {
                let filaEst = $(this).closest('.fila-estudiante');
                let ru = filaEst.data('reg');
                let nombre = filaEst.find('.nombre-col').text();

                let inputLibre = null;
                $('.input-ru').each(function() {
                    if ($(this).val().trim() === "") {
                        inputLibre = $(this);
                        return false;
                    }
                });

                if (inputLibre) {
                    let pos = inputLibre.data('pos');
                    inputLibre.val(ru);
                    $(`#lbl-nombre-${pos}`).text(nombre);
                    actualizarBolsaYContador();
                    inputLibre.focus().select();
                } else {
                    alert("No hay casillas libres en la grilla izquierda.");
                }
            });

            // 5. GUARDADO MASIVO (AJAX)
            $('#btnGuardarMasivo').on('click', function() {
                let btn = $(this);
                let foliosData = {};
                let hayDuplicados = $('.input-duplicado').length > 0;

                if (hayDuplicados) {
                    alert(
                        "¡Atención! Tienes códigos de RU duplicados en la grilla. Por favor corrígelos antes de guardar."
                    );
                    return;
                }

                $('.input-ru').each(function() {
                    let pos = $(this).data('pos');
                    let ru = $(this).val().trim();
                    if (ru !== "") {
                        foliosData[pos] = ru;
                    }
                });

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

                let urlMasiva = "{{ route('admin.folio-examens.generar-masivo', $programacion->id) }}";

                $.post(urlMasiva, {
                    _token: "{{ csrf_token() }}",
                    folios: foliosData
                }, function(res) {
                    if (res.success) {
                        alert("¡Foliación guardada masivamente con éxito!");
                        location.reload();
                    }
                }).fail(function(xhr) {
                    btn.prop('disabled', false).html(
                        '<i class="fas fa-save mr-1"></i> Guardar Toda la Foliación');
                    let mensaje = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                        .message : "Ocurrió un error al guardar la foliación.";
                    alert(mensaje);
                });
            });

            // Función de sincronización y conteo
            // Función de sincronización y conteo corregida
            function actualizarBolsaYContador() {
                let rusEscritos = [];
                let conteoRus = {};

                // 1. Recolectar los RU escritos en la grilla para detectar duplicados
                $('.input-ru').each(function() {
                    let val = $(this).val().trim();
                    if (val !== "") {
                        rusEscritos.push(val);
                        conteoRus[val] = (conteoRus[val] || 0) + 1;
                    }
                });

                // 2. Marcar visualmente los duplicados en la grilla
                $('.input-ru').each(function() {
                    let input = $(this);
                    let val = input.val().trim();
                    let fila = input.closest('tr');

                    if (val !== "" && conteoRus[val] > 1) {
                        input.addClass('input-duplicado');
                        fila.addClass('fila-duplicada');
                    } else {
                        input.removeClass('input-duplicado');
                        fila.removeClass('fila-duplicada');
                    }
                });

                // 3. Filtrar correctamente la bolsa de estudiantes (Disponibles + Buscador)
                let query = $('#buscarEstudiante').val().toLowerCase().trim();

                $('.fila-estudiante').each(function() {
                    let fila = $(this);
                    let reg = fila.data('reg').toString().trim();
                    let searchStr = fila.data('search').toString().toLowerCase();

                    let yaEscrito = rusEscritos.includes(reg);
                    let coincideBusqueda = (query === "" || searchStr.includes(query));

                    // Regla de oro: Se muestra SÓLO si NO está escrito en la grilla Y coincide con la búsqueda
                    if (!yaEscrito && coincideBusqueda) {
                        fila.removeClass('d-none ocultar-bolsa');
                    } else {
                        fila.addClass('d-none ocultar-bolsa');
                    }
                });

                // 4. Actualizar el contador de pendientes en la cabecera de la bolsa
                let pendientes = $('.fila-estudiante').not('.ocultar-bolsa').length;
                $('#contadorBolsa').text(pendientes);
            }

            $(document).on('input', '.input-ru', function() {
                actualizarBolsaYContador();
            });

            // Buscador dinámico de la bolsa
            $('#buscarEstudiante').on('input', function() {
                actualizarBolsaYContador();
            });
        });
    </script>
@stop
