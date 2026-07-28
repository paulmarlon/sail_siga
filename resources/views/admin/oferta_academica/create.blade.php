@extends('adminlte::page')

@section('title', 'Nueva Oferta Académica | SIG@')

@section('css')
    <!-- Select2 CSS para la primera columna -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />

    <style>
        .oferta-main-wrapper {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 140px);
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .oferta-layout {
            display: flex;
            flex-direction: row;
            flex-grow: 1;
            overflow: hidden;
            width: 100%;
            position: relative;
        }

        /* Contenedor de las Tres Columnas */
        .columna-seleccion {
            width: 340px;
            min-width: 340px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        .columna-arrastre {
            flex: 1;
            background: #f4f6f9;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            padding: 10px;
            overflow-y: auto;
        }

        .columna-opciones {
            width: 350px;
            min-width: 350px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .columna-opciones.collapsed {
            width: 0;
            min-width: 0;
            overflow: hidden;
            border-left: none;
            padding: 0;
            opacity: 0;
        }

        /* Lista receptora central */
        .lista-destino-arrastre {
            flex-grow: 1;
            min-height: 300px;
            background: #fff;
            border: 2px dashed #007bff;
            border-radius: 5px;
            padding: 10px;
            overflow-y: auto;
        }

        /* Tarjetas de materias */
        .materia-item-catalogo {
            background: white;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 6px;
            font-size: 0.8rem;
            border-left: 4px solid #17a2b8;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .materia-item-seleccionada {
            background: white;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 6px;
            font-size: 0.8rem;
            border-left: 4px solid #28a745;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .select2-container--bootstrap4 .select2-selection {
            min-height: calc(1.5em + 0.75rem + 2px);
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center py-1">
        <h1 class="h4 mb-0"><i class="fas fa-project-diagram mr-2 text-primary"></i> Registrar <b>Oferta Académica
                (Multicolumna)</b></h1>
        <div class="btn-group">
            <button id="btn-toggle-opciones" class="btn btn-sm btn-info" title="Mostrar/Ocultar Panel de Opciones">
                <i class="fas fa-columns"></i> <span id="toggle-text">Opciones</span>
            </button>
            <a href="{{ route('admin.oferta-academica.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="oferta-main-wrapper">
        <form action="{{ route('admin.oferta-academica.store') }}" method="POST" id="form-oferta"
            class="d-flex flex-column flex-grow-1">
            @csrf
            <div class="oferta-layout">

                <!-- ================= COLUMNA 1: CATÁLOGO DE MATERIAS ================= -->
                <div class="columna-seleccion">
                    <div class="card card-primary card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-1 px-2">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;"><i
                                    class="fas fa-search mr-1 text-primary"></i> 1. Catálogo de Materias</h6>
                        </div>
                        <div class="card-body d-flex flex-column p-2 flex-grow-1 overflow-hidden">
                            <!-- Input de filtro -->
                            <div class="mb-1">
                                <input type="text" id="filtrarCatalogo" class="form-control form-control-sm py-1"
                                    placeholder="Filtrar catálogo..." style="height: calc(1.5em + 0.5rem + 2px);">
                            </div>
                            <!-- Contenedor del catálogo -->
                            <div id="catalogo-origen" class="flex-grow-1 overflow-auto pr-1"
                                style="max-height: calc(100vh - 210px);">
                                @foreach ($pensums as $pensum)
                                    <div class="materia-item-catalogo py-1 px-2 mb-1" data-id="{{ $pensum->id }}"
                                        data-texto="{{ $pensum->carrera->sigla ?? 'N/A' }}-{{ $pensum->grado->orden ?? 'N/A' }} | {{ $pensum->materia->nombre ?? 'N/A' }}">
                                        <span class="font-weight-bold text-info text-truncate mr-2"
                                            style="font-size: 0.68rem; line-height: 1.1;">
                                            {{ $pensum->carrera->sigla ?? 'N/A' }}-{{ $pensum->grado->orden ?? 'N/A' }} |
                                            <span class="text-dark font-weight-600"
                                                style="font-size: 0.72rem;">{{ $pensum->materia->nombre ?? 'N/A' }}</span>
                                        </span>
                                        <button type="button"
                                            class="btn btn-xs btn-outline-success btn-agregar-materia p-0 px-1"
                                            title="Agregar materia">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= COLUMNA 2: ZONA ACTIVA DE SELECCIÓN ================= -->
                <div class="columna-arrastre">
                    <div class="card card-success card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-1 px-2">
                            <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;"><i
                                    class="fas fa-hand-paper mr-1 text-success"></i> 2. Materias a Grabar (Zona Activa)</h6>
                        </div>
                        <div class="card-body d-flex flex-column p-2 flex-grow-1">
                            <p class="text-muted small mb-1" style="font-size: 0.72rem; line-height: 1.2;"><i
                                    class="fas fa-info-circle"></i> Haz clic en el botón (+) del catálogo para añadir
                                materias aquí.</p>

                            <!-- Contenedor destino -->
                            <div id="zona-grabacion" class="lista-destino-arrastre p-1 overflow-auto"
                                style="max-height: calc(100vh - 210px);">
                                <!-- Las materias añadidas se inyectarán aquí -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= COLUMNA 3: CAMPOS DE CONFIGURACIÓN Y BOTÓN GUARDAR ================= -->
                <div class="columna-opciones" id="panel-opciones">
                    <div class="card card-info card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                        <div class="card-header bg-white py-2">
                            <h6 class="card-title text-dark font-weight-bold mb-0"><i
                                    class="fas fa-sliders-h mr-1 text-info"></i> 3. Parámetros de Oferta</h6>
                        </div>
                        <div class="card-body d-flex flex-column p-3 flex-grow-1 overflow-auto">
                            <div class="flex-grow-1">
                                <div class="form-group mb-2">
                                    <label for="periodo_id" class="small font-weight-bold mb-1">Periodo Académico:</label>
                                    <select name="periodo_id" id="periodo_id" class="form-control form-control-sm" required>
                                        <option value="">Seleccione periodo</option>
                                        @foreach ($periodos as $periodo)
                                            <option value="{{ $periodo->id }}">{{ $periodo->nombre }} |
                                                {{ $periodo->gestion->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="turno_id" class="small font-weight-bold mb-1">Turno:</label>
                                    <select name="turno_id" id="turno_id" class="form-control form-control-sm" required>
                                        <option value="">Seleccione turno</option>
                                        @foreach ($turnos as $turno)
                                            <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="paralelo_id" class="small font-weight-bold mb-1">Paralelo:</label>
                                    <select name="paralelo_id" id="paralelo_id" class="form-control form-control-sm"
                                        required>
                                        <option value="">Seleccione paralelo</option>
                                        @foreach ($paralelos as $paralelo)
                                            <option value="{{ $paralelo->id }}">{{ $paralelo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-row mb-2">
                                    <div class="form-group col-6 mb-0">
                                        <label for="cupo_maximo" class="small font-weight-bold mb-1">Cupo Máx:</label>
                                        <input type="number" name="cupo_maximo" id="cupo_maximo"
                                            class="form-control form-control-sm" value="80" min="1" required>
                                    </div>
                                    <div class="form-group col-6 mb-0">
                                        <label for="estado_id" class="small font-weight-bold mb-1">Estado:</label>
                                        <select name="estado_id" id="estado_id" class="form-control form-control-sm"
                                            required>
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de guardar al fondo de la tercera columna -->
                            <div class="border-top pt-3 mt-auto">
                                <button type="submit"
                                    class="btn btn-sm btn-success btn-block font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Grabar Ofertas Académicas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // 1. Filtrado rápido del Catálogo en la Columna 1 asegurando lectura exacta del data-texto
            $('#filtrarCatalogo').on('keyup', function() {
                let val = $(this).val().toLowerCase().trim();
                $('#catalogo-origen .materia-item-catalogo').filter(function() {
                    let textoItem = $(this).attr('data-texto').toLowerCase();
                    $(this).toggle(textoItem.indexOf(val) > -1);
                });
            });

            // 2. Evento clic en el botón (+) para mover la materia a la Zona Activa (Columna 2)
            $(document).on('click', '.btn-agregar-materia', function() {
                const itemPadre = $(this).closest('.materia-item-catalogo');
                const id = itemPadre.attr('data-id');
                const textoCompleto = itemPadre.attr('data-texto');

                // Validar si ya existe en la zona de grabación para evitar duplicados
                if ($(`#zona-grabacion input[value="${id}"]`).length > 0) {
                    toastr.warning('Esta materia ya está en la lista para grabar.');
                    return;
                }

                // Elemento compacto inyectado en la zona de grabación
                const htmlItem = `
                    <div class="materia-item-seleccionada py-1 px-2 mb-1" data-id="${id}">
                        <input type="hidden" name="pensum_id[]" value="${id}">
                        <span class="font-weight-bold text-success text-truncate mr-2" style="font-size: 0.72rem; line-height: 1.1;">
                            ${textoCompleto}
                        </span>
                        <button type="button" class="btn btn-xs text-danger btn-remover-materia p-0" title="Quitar">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                `;

                $('#zona-grabacion').append(htmlItem);
                toastr.success('Materia agregada correctamente');
            });

            // 3. Remover materia de la zona activa
            $(document).on('click', '.btn-remover-materia', function() {
                $(this).closest('.materia-item-seleccionada').fadeOut(200, function() {
                    $(this).remove();
                });
            });

            // 4. Toggle Panel Opciones (Columna 3)
            $('#btn-toggle-opciones').on('click', function() {
                $('#panel-opciones').toggleClass('collapsed');
                const isCollapsed = $('#panel-opciones').hasClass('collapsed');
                $('#toggle-text').text(isCollapsed ? 'Mostrar Opciones' : 'Opciones');
                $(this).find('i').toggleClass('fa-columns fa-indent');
            });

            // 5. Validación al enviar el formulario para asegurar que hay al menos una materia
            $('#form-oferta').on('submit', function(e) {
                if ($('#zona-grabacion input[name="pensum_id[]"]').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Sin materias seleccionadas',
                        text: 'Debes seleccionar al menos una materia haciendo clic en el botón (+) para poder grabar.',
                        icon: 'warning',
                        confirmButtonColor: '#003366'
                    });
                }
            });
        });
    </script>
@stop
