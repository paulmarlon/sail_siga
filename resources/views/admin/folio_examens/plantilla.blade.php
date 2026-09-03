@extends('adminlte::page')

@section('title', 'Estación de Foliado Masivo')

@section('content_header')
    <div class="card card-outline card-purple shadow-sm mb-2">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="font-weight-bold text-dark mb-1">
                        <i class="fas fa-table text-purple mr-2"></i> Estación de Foliado: <span
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
                        <div class="mb-1">
                            <span><i class="fas fa-layer-group mr-1 text-warning"></i> Semestre/Grado:
                                <strong>{{ $programacion->ofertaAcademica->pensum->grado->nombre ?? 'S/N' }}</strong></span>
                            &nbsp;|&nbsp;
                            <span><i class="fas fa-clock mr-1 text-danger"></i> Turno:
                                <strong>{{ $programacion->ofertaAcademica->turno->nombre ?? 'S/N' }}</strong></span>
                            &nbsp;|&nbsp;
                            <span><i class="fas fa-columns mr-1 text-secondary"></i> Paralelo:
                                <strong>{{ $programacion->ofertaAcademica->paralelo->nombre ?? 'S/N' }}</strong></span>
                        </div>
                        <div>
                            <span><i class="fas fa-user-tie mr-1 text-purple"></i> Docente / Responsable:
                                <strong>
                                    @php
                                        $resp = $programacion->responsable->persona ?? null;
                                        $nombreResp = $resp
                                            ? trim(
                                                ($resp->ap_paterno ?? '') .
                                                    ' ' .
                                                    ($resp->ap_materno ?? '') .
                                                    ', ' .
                                                    ($resp->nombres ?? ''),
                                            )
                                            : 'No asignado';
                                    @endphp
                                    {{ $nombreResp }}
                                </strong>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    {{-- 🔒 SI NO ESTÁ BLOQUEADO, MOSTRAMOS EL BOTÓN DE GUARDAR --}}
                    @if (!$programacion->bloqueado)
                        <button type="button" id="btnGuardarMasivo"
                            class="btn btn-success font-weight-bold shadow-sm mr-1">
                            <i class="fas fa-save mr-1"></i> Guardar Toda la Foliación
                        </button>
                    @else
                        <span class="badge badge-danger p-2 font-weight-bold mr-1" style="font-size: 0.85rem;">
                            <i class="fas fa-lock mr-1"></i> ACTA SELLADA Y CERRADA
                        </span>
                    @endif

                    {{-- BOTÓN VOLVER INTELIGENTE (ADMIN O DOCENTE) --}}
                    <a href="{{ auth()->user()->hasRole('Docente') ? route('docente.seleccionar-materia') : route('admin.folio-examens.index') }}"
                        class="btn btn-outline-secondary shadow-sm font-weight-bold">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- ALERTA INFORMATIVA SI ESTÁ BLOQUEADO --}}
    @if ($programacion->bloqueado)
        <div class="alert alert-warning shadow-sm py-2 px-3 mb-3" style="font-size: 0.88rem;">
            <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Modo de Consulta (Bloqueado):</strong> Esta instancia
            de examen se encuentra sellada. Los campos están protegidos en modo de solo lectura y no se pueden realizar
            modificaciones.
        </div>
    @endif

    <div class="row pt-1">

        {{-- 1. CARD IZQUIERDA: GRILLA TIPO EXCEL COMPACTA --}}
        <div class="col-md-7">
            <div class="card card-outline card-primary shadow-sm mb-2">
                <div class="card-header border-0 py-1 px-2 bg-light">
                    <h3 class="card-title font-weight-bold" style="font-size: 0.85rem;">
                        <i class="fas fa-list-ol mr-1 text-primary"></i> GRILLA DE EXÁMENES (FOLIADO RÁPIDO)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped table-sm m-0 excel-grid" style="font-size: 0.78rem;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="40px" class="py-1">FOLIO</th>
                                <th width="130px" class="py-1">RU</th>
                                <th class="py-1">APELLIDOS Y NOMBRES</th>
                                <th width="35px" class="py-1"><i class="fas fa-chevron-right text-muted"
                                        style="font-size: 0.65rem;"></i></th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoGrilla">
                            @for ($i = 1; $i <= $totalCasillas; $i++)
                                @php
                                    $folioExistente = $programacion->foliosExamen->values()[$i - 1] ?? null;
                                    $ruGuardado =
                                        $folioExistente && $folioExistente->estudiante
                                            ? $folioExistente->estudiante->registro_universitario
                                            : '';
                                    $p =
                                        $folioExistente && $folioExistente->estudiante
                                            ? $folioExistente->estudiante->persona
                                            : null;
                                    $nombreGuardado = $p
                                        ? trim(
                                            ($p->ap_paterno ?? '') .
                                                ' ' .
                                                ($p->ap_materno ?? '') .
                                                ', ' .
                                                ($p->nombres ?? ''),
                                        )
                                        : '';
                                @endphp
                                <tr data-pos="{{ $i }}">
                                    <td class="text-center font-weight-bold bg-light align-middle py-1">{{ $i }}
                                    </td>
                                    <td class="p-1 align-middle position-relative">
                                        <input type="text"
                                            class="form-control form-control-xs excel-input input-ru font-weight-bold text-primary py-0 px-1"
                                            style="height: 22px; font-size: 0.78rem;" data-pos="{{ $i }}"
                                            value="{{ $ruGuardado }}" placeholder="Ej. 3412" autocomplete="off"
                                            maxlength="15" {{ $programacion->bloqueado ? 'readonly disabled' : '' }}>

                                        <!-- Contenedor flotante para opciones de autocompletado por 4 dígitos -->
                                        @if (!$programacion->bloqueado)
                                            <div class="dropdown-suggestions shadow-lg rounded"
                                                id="sugg-{{ $i }}"
                                                style="display:none; position:absolute; z-index:1050; background:white; border:1px solid #ddd; width: 230px; max-height: 130px; overflow-y:auto; font-size: 0.75rem;">
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle px-2 py-1">
                                        <span id="lbl-nombre-{{ $i }}"
                                            class="text-uppercase font-weight-bold text-dark"
                                            style="font-size: 0.78rem;">{{ $nombreGuardado }}</span>
                                    </td>
                                    <td class="text-center align-middle p-1 text-muted py-1">
                                        <i class="fas fa-chevron-right" style="font-size: 0.65rem;"></i>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 2. CARD DERECHA: BOLSA DE ESTUDIANTES COMPACTA --}}
        <div class="col-md-5 bolsa-flotante">
            <div class="card card-outline card-warning shadow-sm mb-2">
                <div class="card-header border-0 py-1 px-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h3 class="card-title font-weight-bold" style="font-size: 0.85rem;">
                            <i class="fas fa-users mr-1 text-warning"></i> ESTUDIANTES DISPONIBLES
                        </h3>
                        <span class="badge badge-warning px-2 py-1 font-weight-bold" id="contadorBolsa"
                            style="font-size: 0.75rem;">
                            {{ $estudiantesParalelo->count() }}
                        </span>
                    </div>

                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white py-0 px-2" style="font-size: 0.75rem;"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="buscarEstudiante" class="form-control form-control-sm py-0 px-1"
                            style="height: 24px; font-size: 0.78rem;" placeholder="Buscar por RU o Nombre...">
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped table-sm m-0" style="font-size: 0.78rem;">
                        <thead class="bg-warning text-center">
                            <tr>
                                <th width="35px" class="py-1"><i class="fas fa-arrow-left"
                                        style="font-size: 0.65rem;"></i></th>
                                <th width="85px" class="py-1">RU</th>
                                <th class="py-1">ESTUDIANTE</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoBolsa">
                            @foreach ($estudiantesParalelo as $pend)
                                @php
                                    $ru = trim($pend->estudiante->registro_universitario ?? '');
                                    $p = $pend->estudiante->persona ?? null;
                                    $nombreCompleto = $p
                                        ? trim(
                                            ($p->ap_paterno ?? '') .
                                                ' ' .
                                                ($p->ap_materno ?? '') .
                                                ', ' .
                                                ($p->nombres ?? ''),
                                        )
                                        : 'SIN DATOS';
                                    $searchable = strtolower($ru . ' ' . $nombreCompleto);

                                    $yaAsignado = $programacion->foliosExamen->contains(function ($f) use ($pend) {
                                        return $f->estudiante_id === $pend->estudiante_id;
                                    });
                                @endphp
                                <tr class="fila-estudiante {{ $yaAsignado ? 'd-none ocultar-bolsa' : '' }}"
                                    data-reg="{{ $ru }}" data-search="{{ $searchable }}">
                                    <td class="text-center align-middle p-1 py-1">
                                        {{-- 🔒 SI ESTÁ BLOQUEADO, NO MOSTRAMOS EL BOTÓN DE ASIGNAR --}}
                                        @if (!$programacion->bloqueado)
                                            <button type="button" class="btn btn-xs btn-primary btn-asignar py-0 px-1"
                                                style="font-size: 0.65rem;" title="Asignar al primer folio libre">
                                                <i class="fas fa-arrow-left"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-primary align-middle py-1">
                                        {{ $ru }}
                                    </td>
                                    <td class="nombre-col align-middle text-uppercase py-1">{{ $nombreCompleto }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@stop

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
            function actualizarBolsaYContador() {
                let rusEscritos = [];
                let conteoRus = {};

                $('.input-ru').each(function() {
                    let val = $(this).val().trim();
                    if (val !== "") {
                        rusEscritos.push(val);
                        conteoRus[val] = (conteoRus[val] || 0) + 1;
                    }
                });

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

                $('.fila-estudiante').each(function() {
                    let reg = $(this).data('reg').toString().trim();
                    if (rusEscritos.includes(reg)) {
                        $(this).addClass('d-none ocultar-bolsa');
                    } else {
                        $(this).removeClass('ocultar-bolsa');
                        let query = $('#buscarEstudiante').val().toLowerCase().trim();
                        if (query === "" || $(this).data('search').toString().includes(query)) {
                            $(this).removeClass('d-none');
                        }
                    }
                });

                let pendientes = $('.fila-estudiante').not('.ocultar-bolsa').length;
                $('#contadorBolsa').text(pendientes);
            }

            $(document).on('input', '.input-ru', function() {
                actualizarBolsaYContador();
            });
        });
    </script>
@stop
