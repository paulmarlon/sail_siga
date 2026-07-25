@extends('adminlte::page')

@section('title', 'Malla Curricular | SIG@')

@section('css')
    <style>
        .malla-main-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
            /* Volvemos a fijar el alto de la pantalla del AdminLTE */
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .malla-layout {
            display: flex;
            flex-direction: row;
            flex-grow: 1;
            overflow: hidden;
            width: 100%;
        }

        .malla-section-container {
            flex: 1;
            display: flex;
            overflow-x: auto;
            background: #f4f6f9;
            padding: 10px;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .catalogo-col {
            width: 320px;
            min-width: 320px;
            border-left: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .catalogo-col.collapsed {
            width: 0;
            min-width: 0;
            overflow: hidden;
            border-left: none;
        }

        .columna-grado {
            min-width: 210px;
            max-width: 210px;
            background: #ebedef;
            border-radius: 5px;
            border: 1px solid #d1d4d7;
            display: flex;
            flex-direction: column;
            height: 100%;
            /* La columna ocupa toda la altura disponible sin desbordarse */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .grado-header {
            padding: 5px;
            background: #003366;
            color: white;
            font-size: 0.75rem;
            text-align: center;
            border-radius: 4px 4px 0 0;
        }

        .lista-materias {
            padding: 6px;
            flex-grow: 1;
            overflow-y: auto;
            /* Activa el scroll SOLO si superan el espacio estimado de las 13 materias */
            min-height: 100px;
            max-height: calc(100vh - 230px);
            /* Límite perfecto para que entren las 13 a la vista */
            scrollbar-width: thin;
        }

        .materia-card {
            background: white;
            border-radius: 3px;
            padding: 4px 8px;
            margin-bottom: 4px;
            /* Espaciado compacto para acomodar las 13 materias */
            font-size: 0.7rem;
            border-left: 4px solid #007bff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            cursor: grab;
        }

        .materia-sigla {
            font-weight: bold;
            color: #6c757d;
            display: block;
            font-size: 0.6rem;
        }

        .materia-nombre {
            font-weight: 600;
            color: #333;
            display: block;
            line-height: 1.1;
        }

        #catalogo-list {
            overflow-y: auto;
            padding: 10px;
            flex-grow: 1;
        }

        .ghost-class {
            opacity: 0.5;
            background: #c8d6e5 !important;
            border: 2px dashed #003366 !important;
        }

        .sortable-chosen {
            background: #e1f5fe !important;
            border: 2px solid #0288d1 !important;
        }

        .lista-materias[data-puedo-editar="false"] {
            background-color: #f8f9fa;
            border: 1px dashed #ced4da;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center py-1">
        <h1 class="h4 mb-0"><i class="fas fa-project-diagram mr-2"></i> Pensum: <span
                class="text-primary">{{ $carrera->nombre }}</span></h1>
        <div class="btn-group">
            <button id="btn-toggle-catalogo" class="btn btn-sm btn-info">
                <i class="fas fa-list"></i> <span id="toggle-text">Catálogo</span>
            </button>
            <a href="{{ route('admin.carreras.index') }}" class="btn btn-sm btn-secondary">VOLVER</a>
        </div>
    </div>
@stop

@section('content')
    <div class="malla-main-wrapper">
        <div class="malla-layout">

            <!-- ÁREA DE LA MALLA -->
            <div class="malla-section-container" id="malla-grid">
                @foreach ($grados as $grado)
                    @php $esBloqueado = in_array($grado->id, $gradosBloqueadosIds ?? []); @endphp
                    <div class="columna-grado">
                        <div class="grado-header {{ $esBloqueado ? 'bg-secondary' : '' }}">
                            {{ strtoupper($grado->nombre) }}
                            @if ($esBloqueado)
                                <br><small style="font-size: 0.55rem;"><i class="fas fa-lock"></i> BASE</small>
                            @endif
                        </div>
                        <div class="lista-materias sortable-list" data-grado-id="{{ $grado->id }}"
                            data-puedo-editar="{{ $esBloqueado ? 'false' : 'true' }}">

                            @foreach ($pensums->get($grado->id, []) as $item)
                                @php
                                    $esHeredado = $item->carrera_id != $carrera->id;
                                    $bloqueado = $esHeredado || $esBloqueado;
                                @endphp

                                <div class="materia-card {{ $bloqueado ? 'bg-light border-secondary' : '' }}"
                                    data-id="{{ $item->id }}"
                                    style="{{ $bloqueado ? 'cursor: not-allowed; opacity: 0.8;' : '' }}">

                                    <div class="d-flex justify-content-between align-items-start">
                                        <small class="materia-sigla font-weight-bold">{{ $item->materia->sigla }}</small>
                                        @if (!$bloqueado)
                                            <button type="button" class="btn btn-xs text-danger btn-eliminar p-0"
                                                data-id="{{ $item->id }}" style="margin-left: 5px;">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="materia-nombre d-block text-truncate">{{ $item->materia->nombre }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ÁREA DEL CATÁLOGO -->
            <div class="catalogo-col" id="catalogo-section">
                <div class="card mb-2">
                    <div class="card-body p-2">
                        <form action="{{ route('admin.pensums.index') }}" method="GET" class="form-inline">
                            <select name="carrera_id" class="form-control form-control-sm select2" style="width: 350px"
                                onchange="this.form.submit()">
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->id }}" {{ $carrera->id == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                <div class="p-2 bg-light border-bottom">
                    <input type="text" id="buscarMateria" class="form-control form-control-sm"
                        placeholder="Buscar materia...">
                </div>
                <div id="catalogo-list">
                    @foreach ($materias_disponibles as $materia)
                        <div class="materia-card" data-materia-id="{{ $materia->id }}"
                            style="border-left-color: #17a2b8; cursor: move;">
                            <div class="d-flex justify-content-between align-items-start">
                                <small class="materia-sigla font-weight-bold text-info">{{ $materia->sigla }}</small>
                            </div>
                            <span class="materia-nombre d-block text-truncate">{{ $materia->nombre }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle Catálogo
            $('#btn-toggle-catalogo').on('click', function() {
                $('#catalogo-section').toggleClass('collapsed');
                const isCollapsed = $('#catalogo-section').hasClass('collapsed');
                $('#toggle-text').text(isCollapsed ? 'Mostrar Catálogo' : 'Catálogo');
                $(this).find('i').toggleClass('fa-list fa-plus-circle');
            });

            // Configurar el Catálogo (Limpiamos cualquier data-id heredado al clonar)
            new Sortable(document.getElementById('catalogo-list'), {
                group: {
                    name: 'malla',
                    pull: 'clone', // 'clone' evita que el catálogo pierda el elemento original al arrastrarlo
                    put: false
                },
                sort: false,
                animation: 150,
                ghostClass: 'ghost-class',
                onClone: function(evt) {
                    // Nos aseguramos de que el elemento clonado nazca completamente limpio de IDs previos
                    $(evt.clone).removeAttr('data-id');
                }
            });

            // Configurar Listas de la Malla
            $('.sortable-list').each(function() {
                const canEdit = $(this).data('puedo-editar') === true;
                new Sortable(this, {
                    group: 'malla',
                    animation: 150,
                    ghostClass: 'ghost-class',
                    filter: '.bg-light',
                    disabled: !canEdit,

                    onAdd: function(evt) {
                        gestionarCambio(evt);
                    },
                    onUpdate: function(evt) {
                        gestionarCambio(evt);
                    }
                });
            });

            function gestionarCambio(evt) {
                const itemEl = evt.item;
                const nuevoGradoId = evt.to.dataset.gradoId;

                // Leemos el ID actual si lo tuviera
                const pensumId = itemEl.getAttribute('data-id');

                // Si viene del catálogo, tendrá el atributo data-materia-id
                let materiaId = itemEl.getAttribute('data-materia-id');
                if (!materiaId) {
                    materiaId = $(itemEl).data('materia-id');
                }

                // SI el elemento tiene un data-materia-id explícito, significa que SE ACABA DE CLONAR DEL CATÁLOGO
                if (materiaId && itemEl.hasAttribute('data-materia-id')) {
                    $(itemEl).css('border-left-color', '#007bff').css('cursor', 'grab');
                    crearAsignacion(materiaId, nuevoGradoId, itemEl);
                } else if (pensumId && pensumId !== "") {
                    // Si ya existía en otra columna de la malla y solo cambió de grado
                    actualizarGrado(pensumId, nuevoGradoId);
                } else {
                    console.error("No se pudo detectar el origen de la materia:", itemEl);
                    toastr.error('Error: No se reconoció la materia arrastrada');
                    $(itemEl).remove();
                }
            }

            function crearAsignacion(materiaId, gradoId, element) {
                const $el = $(element);

                $.post("{{ route('admin.pensums.store') }}", {
                    _token: "{{ csrf_token() }}",
                    carrera_id: "{{ $carrera->id }}",
                    materia_id: materiaId,
                    grado_id: gradoId
                }).done(res => {
                    $el.attr('data-id', res.id);
                    $el.removeAttr('data-materia-id');

                    let headerDiv = $el.find('.d-flex');
                    if (headerDiv.length === 0) {
                        headerDiv = $el.children().first();
                    }

                    $el.find('.btn-eliminar').remove();
                    headerDiv.append(`
                        <button type="button" class="btn btn-xs text-danger btn-eliminar p-0" data-id="${res.id}" style="margin-left: 5px;">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    `);

                    $el.css({
                        'border-left': '4px solid #007bff',
                        'cursor': 'grab'
                    });
                    $el.find('.materia-sigla').removeClass('text-info');

                    toastr.success('Asignación exitosa');
                }).fail(err => {
                    // Si el servidor falla, removemos el elemento fantasma visualmente
                    $el.remove();

                    console.error("DETALLE DEL ERROR:", err.responseText);
                    const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message :
                        'Error en el servidor';
                    toastr.error(msg);
                });
            }

            function actualizarGrado(pensumId, nuevoGradoId) {
                $.post("{{ route('admin.pensums.update-grado') }}", {
                    _token: "{{ csrf_token() }}",
                    id: pensumId,
                    grado_id: nuevoGradoId,
                    carrera_contexto_id: "{{ $carrera->id }}"
                }).done(res => {
                    toastr.info(res.message || 'Posición actualizada');
                }).fail(err => {
                    const msg = err.responseJSON ? err.responseJSON.message : 'Error al mover';
                    toastr.error(msg);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                });
            }

            $('#buscarMateria').on('keyup', function() {
                let val = $(this).val().toLowerCase();
                $('#catalogo-list .materia-card').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                });
            });

            $(document).on('click', '.btn-eliminar', function() {
                const btn = $(this);
                const id = btn.data('id');

                if (confirm('¿Deseas quitar esta materia de la malla?')) {
                    $.ajax({
                        url: `/admin/pensums/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}",
                            carrera_contexto_id: "{{ $carrera->id }}"
                        },
                        success: function(res) {
                            btn.closest('.materia-card').fadeOut(function() {
                                $(this).remove();
                            });
                            toastr.warning('Materia removida de la malla');
                        },
                        error: function(err) {
                            const msg = err.responseJSON ? err.responseJSON.message :
                                'Error desconocido';
                            toastr.error(msg);
                        }
                    });
                }
            });
        });
    </script>
@stop
