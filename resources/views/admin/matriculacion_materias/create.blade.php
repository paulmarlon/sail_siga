@extends('adminlte::page')

@section('title', 'Matriculación por Bloques')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-signature text-primary mr-2"></i> Matriculación Masiva (Estudiantes y Materias)</h1>
        <a href="{{ route('admin.matriculacion-materias.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.matriculacion-materias.store') }}" method="POST" id="form-matriculacion-masiva">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="icon fas fa-ban"></i> <strong>¡Atención!</strong> Corrige los siguientes errores:
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- CONTENEDOR PRINCIPAL DE 4 COLUMNAS -->
        <div class="row">

            <!-- ================= COLUMNA 1: CATÁLOGO DE ESTUDIANTES ================= -->
            <div class="col-md-3 px-1">
                <div class="card card-primary card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-users mr-1 text-primary"></i> 1. Estudiantes
                        </h6>
                        <button type="button" class="btn btn-xs btn-success" data-toggle="modal"
                            data-target="#modalPegarRUs" title="Pegar RUs masivos desde Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                    <div class="card-body d-flex flex-column p-2 flex-grow-1 overflow-hidden">
                        <div class="mb-2">
                            <input type="text" id="filtrarCatalogo" class="form-control form-control-sm py-1"
                                placeholder="Filtrar por CI, Nombre o RU...">
                        </div>
                        <div id="catalogo-origen" class="flex-grow-1 overflow-auto pr-1"
                            style="max-height: calc(100vh - 260px);">
                            @foreach ($estudiantes as $est)
                                <div class="estudiante-item custom-control custom-checkbox py-1 px-2 mb-1 border rounded bg-light"
                                    style="font-size: 0.75rem;" data-ru="{{ trim($est->registro_universitario) }}"
                                    data-ci="{{ trim($est->persona->ci) }}">
                                    <input type="checkbox" name="estudiante_ids[]" value="{{ $est->id }}"
                                        id="est_chk_{{ $est->id }}" class="custom-control-input estudiante-checkbox">
                                    <label class="custom-control-label font-weight-normal text-dark w-100 cursor-pointer"
                                        for="est_chk_{{ $est->id }}">
                                        <span class="font-weight-bold d-block">
                                            {{ $est->persona->ap_paterno }} {{ $est->persona->ap_materno }}
                                            {{ $est->persona->nombres }}
                                        </span>
                                        <span class="text-muted">CI: {{ $est->persona->ci }} | RU:
                                            {{ $est->registro_universitario }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 2: RESUMEN DE ESTUDIANTES SELECCIONADOS ================= -->
            <div class="col-md-3 px-1">
                <div class="card card-info card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-user-check mr-1 text-info"></i> 2. Seleccionados
                        </h6>
                        <button type="button" id="btn-desmarcar-todos" class="btn btn-xs btn-outline-secondary"
                            style="font-size: 0.65rem;">Limpiar</button>
                    </div>
                    <div class="card-body p-2 d-flex flex-column flex-grow-1 overflow-hidden">
                        <div id="sin-estudiantes-seleccionados" class="text-muted text-center py-4">
                            <i class="fas fa-user-slash fa-2x mb-2 text-secondary"></i>
                            <p class="small mb-0">Marca estudiantes de la izquierda o pégalos vía Excel.</p>
                        </div>
                        <div id="lista-seleccionados-container" class="flex-grow-1 overflow-auto pr-1 d-none"
                            style="max-height: calc(100vh - 220px);">
                            <!-- Se llena dinámicamente con JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 3: OFERTA ACADÉMICA Y FILTROS AVANZADOS ================= -->
            <div class="col-md-3 px-1">
                <div class="card card-warning card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-filter mr-1 text-warning"></i> 3. Oferta Académica
                        </h6>
                    </div>
                    <div class="card-body p-2 d-flex flex-column flex-grow-1 overflow-hidden">
                        <!-- Filtros Avanzados -->
                        <div class="form-row mb-2">
                            <div class="col-12 mb-1">
                                <select id="filtro-carrera" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Filtrar por Carrera --</option>
                                    @foreach ($carreras as $car)
                                        <option value="{{ strtolower($car->nombre) }}">{{ $car->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-1">
                                <select id="filtro-grado" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    <option value="">-- Grado / Semestre --</option>
                                    @foreach ($grados as $gra)
                                        <option value="{{ strtolower($gra->nombre) }}">{{ $gra->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-1">
                                <select id="filtro-periodo" class="form-control form-control-sm"
                                    style="font-size: 0.75rem;">
                                    <option value="">-- Periodo --</option>
                                    @foreach ($periodos as $per)
                                        <option value="{{ strtolower(trim($per->nombre)) }}">
                                            {{ $per->nombre_completo ?? $per->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 mb-1">
                                <select id="filtro-turno" class="form-control form-control-sm" style="font-size: 0.72rem;">
                                    <option value="">-- Turno --</option>
                                    @foreach ($turnos as $tur)
                                        <option value="{{ strtolower($tur->nombre) }}">{{ $tur->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4 mb-1">
                                <select id="filtro-paralelo" class="form-control form-control-sm"
                                    style="font-size: 0.72rem;">
                                    <option value="">-- Paralelo --</option>
                                    @foreach ($paralelos as $par)
                                        <option value="{{ strtolower($par->nombre) }}">{{ $par->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="button" id="btn-limpiar-filtros"
                                    class="btn btn-xs btn-outline-secondary btn-block"
                                    style="font-size: 0.70rem;">Limpiar</button>
                            </div>
                        </div>

                        <!-- Lista de materias (Oferta Académica optimizada) -->
                        <div id="lista-ofertas-container"
                            class="flex-grow-1 overflow-auto pr-1 border rounded p-1 bg-white"
                            style="max-height: calc(100vh - 280px);">
                            @foreach ($ofertas as $oferta)
                                @php
                                    $nombreCarrera = $oferta->pensum->carrera->nombre ?? '';
                                    $nombreGrado = $oferta->pensum->grado->nombre ?? '';
                                    $nombreMateria = $oferta->pensum->materia->nombre ?? 'N/A';
                                    $siglaMateria = $oferta->pensum->materia->sigla ?? 'S/S';
                                    $nombrePeriodo = $oferta->periodo->nombre ?? '';
                                    $nombreTurno = $oferta->turno->nombre ?? '';
                                    $nombreParalelo = $oferta->paralelo->nombre ?? '';
                                @endphp
                                <div class="oferta-item custom-control custom-checkbox mb-1 px-1 py-0 border-bottom"
                                    data-carrera="{{ strtolower($nombreCarrera) }}"
                                    data-grado="{{ strtolower($nombreGrado) }}"
                                    data-periodo="{{ strtolower($nombrePeriodo) }}"
                                    data-turno="{{ strtolower($nombreTurno) }}"
                                    data-paralelo="{{ strtolower($nombreParalelo) }}"
                                    style="font-size: 0.72rem; line-height: 1.1;">
                                    <input type="checkbox" name="oferta_ids[]" value="{{ $oferta->id }}"
                                        id="oferta_chk_{{ $oferta->id }}" class="custom-control-input oferta-checkbox">
                                    <label
                                        class="custom-control-label font-weight-normal text-dark w-100 cursor-pointer py-0 my-0"
                                        for="oferta_chk_{{ $oferta->id }}">
                                        <span class="badge badge-info px-1 py-0"
                                            style="font-size: 0.65rem;">{{ $siglaMateria }}</span>
                                        <strong class="text-dark">{{ $nombreMateria }}</strong>
                                        <span class="text-muted d-block" style="font-size: 0.65rem;">
                                            {{ $nombreCarrera }} | {{ $nombreGrado }} | Per: {{ $nombrePeriodo }} | T:
                                            {{ $nombreTurno }} | P: {{ $nombreParalelo }}
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= COLUMNA 4: CONFIRMACIÓN Y MATERIAS SELECCIONADAS ================= -->
            <div class="col-md-3 px-1">
                <div class="card card-success card-outline h-100 mb-0 shadow-sm d-flex flex-column">
                    <div class="card-header bg-white py-2 px-2">
                        <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                            <i class="fas fa-check-circle mr-1 text-success"></i> 4. Confirmar Lote
                        </h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <!-- Parte Superior: Resumen de Selecciones -->
                        <div>
                            <div class="bg-light p-2 rounded border mb-2">
                                <span class="small font-weight-bold d-block text-secondary">Resumen del Lote:</span>
                                <span class="small text-muted d-block" id="resumen-estudiantes-txt">Estudiantes: <b
                                        class="text-primary">0</b></span>
                                <span class="small text-muted d-block mb-1" id="resumen-materias-txt">Materias: <b
                                        class="text-success">0</b></span>

                                <!-- Contenedor de visualización en tiempo real de materias seleccionadas -->
                                <div id="lista-materias-seleccionadas-container" class="border-top pt-1 mt-1 d-none"
                                    style="max-height: calc(100vh - 350px); overflow-y: auto;">
                                    <!-- Se llena dinámicamente con JS -->
                                </div>
                                <div id="sin-materias-seleccionadas"
                                    class="text-muted small border-top pt-1 mt-1 font-italic">
                                    Ninguna materia seleccionada.
                                </div>
                            </div>
                        </div>

                        <!-- Parte Inferior: Estado Académico y Botón de Procesar -->
                        <div>
                            <div class="form-group mb-2">
                                <label for="estado_id" class="font-weight-bold small text-secondary">Estado
                                    Académico:</label>
                                <select name="estado_id" id="estado_id"
                                    class="form-control form-control-sm @error('estado_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar Estado --</option>
                                    @foreach ($estados as $est)
                                        <option value="{{ $est->id }}"
                                            {{ old('estado_id') == $est->id ? 'selected' : '' }}>
                                            {{ $est->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm">
                                <i class="fas fa-save mr-1"></i> Procesar Lote Masivo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- ================= MODAL PARA PEGAR LISTA DE RUs / CIs (EXCEL MÚLTIPLE) ================= -->
    <div class="modal fade" id="modalPegarRUs" tabindex="-1" role="dialog" aria-labelledby="modalPegarRUsLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success py-2">
                    <h5 class="modal-title font-weight-bold text-white" id="modalPegarRUsLabel"
                        style="font-size: 0.95rem;">
                        <i class="fas fa-file-excel mr-1"></i> Importación Masiva de Estudiantes (Excel)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Pega una columna completa de RUs o CIs desde Excel. Todos los que coincidan se marcarán
                        automáticamente:
                    </p>
                    <div class="form-group mb-0">
                        <textarea id="textarea-rus" class="form-control" rows="5" placeholder="RU12345&#10;RU67890&#10;..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-procesar-rus" class="btn btn-sm btn-success font-weight-bold">
                        <i class="fas fa-check mr-1"></i> Marcar Coincidentes
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Filtrar catálogo de estudiantes en tiempo real
            const inputFiltrar = document.getElementById('filtrarCatalogo');
            inputFiltrar.addEventListener('keyup', function() {
                const filtro = this.value.toLowerCase();
                document.querySelectorAll('.estudiante-item').forEach(item => {
                    const texto = item.innerText.toLowerCase();
                    item.style.display = texto.includes(filtro) ? '' : 'none';
                });
            });

            // 2. Control de selección de estudiantes y actualización de Columna 2
            const checkboxesEstudiantes = document.querySelectorAll('.estudiante-checkbox');
            const contenedorSeleccionados = document.getElementById('lista-seleccionados-container');
            const placeholderSinEstudiantes = document.getElementById('sin-estudiantes-seleccionados');

            function actualizarResumenEstudiantes() {
                let seleccionados = [];
                checkboxesEstudiantes.forEach(chk => {
                    if (chk.checked) {
                        const itemPadre = chk.closest('.estudiante-item');
                        const textoLabel = itemPadre.querySelector('span.font-weight-bold').innerText;
                        seleccionados.push({
                            id: chk.value,
                            nombre: textoLabel
                        });
                    }
                });

                document.getElementById('resumen-estudiantes-txt').innerHTML =
                    `Estudiantes: <b class="text-primary">${seleccionados.length}</b>`;

                if (seleccionados.length === 0) {
                    placeholderSinEstudiantes.classList.remove('d-none');
                    contenedorSeleccionados.classList.add('d-none');
                    contenedorSeleccionados.innerHTML = '';
                } else {
                    placeholderSinEstudiantes.classList.add('d-none');
                    contenedorSeleccionados.classList.remove('d-none');
                    let html = '';
                    seleccionados.forEach(est => {
                        html += `<div class="py-1 px-2 mb-1 bg-white border rounded small text-dark d-flex justify-content-between align-items-center">
                                    <span>${est.nombre}</span>
                                    <i class="fas fa-check text-success"></i>
                               </div>`;
                    });
                    contenedorSeleccionados.innerHTML = html;
                }
            }

            checkboxesEstudiantes.forEach(chk => {
                chk.addEventListener('change', actualizarResumenEstudiantes);
            });

            document.getElementById('btn-desmarcar-todos').addEventListener('click', function() {
                checkboxesEstudiantes.forEach(chk => chk.checked = false);
                actualizarResumenEstudiantes();
            });

            // 3. Procesar lista múltiple de RUs / CIs desde Excel
            document.getElementById('btn-procesar-rus').addEventListener('click', function() {
                const texto = document.getElementById('textarea-rus').value.trim();
                if (!texto) return;
                const lineas = texto.split('\n').map(l => l.trim().toLowerCase()).filter(l => l.length > 0);
                if (lineas.length === 0) return;

                let contadorMarcados = 0;
                checkboxesEstudiantes.forEach(chk => {
                    const item = chk.closest('.estudiante-item');
                    const ru = item.getAttribute('data-ru').toLowerCase();
                    const ci = item.getAttribute('data-ci').toLowerCase();

                    if (lineas.includes(ru) || lineas.includes(ci)) {
                        chk.checked = true;
                        contadorMarcados++;
                    }
                });

                actualizarResumenEstudiantes();
                $('#modalPegarRUs').modal('hide');
                Swal.fire('Proceso completado', `Se marcaron ${contadorMarcados} estudiantes coincidentes.`,
                    'success');
            });

            // 4. Filtros avanzados y limpieza de la Columna 3 (Oferta Académica)
            const filtroCarrera = document.getElementById('filtro-carrera');
            const filtroGrado = document.getElementById('filtro-grado');
            const filtroPeriodo = document.getElementById('filtro-periodo');
            const filtroTurno = document.getElementById('filtro-turno');
            const filtroParalelo = document.getElementById('filtro-paralelo');
            const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
            const checkboxesOfertas = document.querySelectorAll('.oferta-checkbox');

            function aplicarFiltrosOferta() {
                const car = filtroCarrera.value.toLowerCase().trim();
                const gra = filtroGrado.value.toLowerCase().trim();
                const per = filtroPeriodo.value.toLowerCase().trim();
                const tur = filtroTurno.value.toLowerCase().trim();
                const par = filtroParalelo.value.toLowerCase().trim();

                document.querySelectorAll('.oferta-item').forEach(item => {
                    const itemCar = item.getAttribute('data-carrera').toLowerCase().trim();
                    const itemGra = item.getAttribute('data-grado').toLowerCase().trim();
                    const itemPer = item.getAttribute('data-periodo').toLowerCase().trim();
                    const itemTur = item.getAttribute('data-turno').toLowerCase().trim();
                    const itemPar = item.getAttribute('data-paralelo').toLowerCase().trim();

                    let match = true;

                    if (car && !itemCar.includes(car)) match = false;
                    if (gra && !itemGra.includes(gra)) match = false;
                    if (per && !itemPer.includes(per)) match = false;
                    if (tur && !itemTur.includes(tur)) match = false;
                    if (par && !itemPar.includes(par)) match = false;

                    item.style.display = match ? '' : 'none';
                });
            }

            filtroCarrera.addEventListener('change', aplicarFiltrosOferta);
            filtroGrado.addEventListener('change', aplicarFiltrosOferta);
            filtroPeriodo.addEventListener('change', aplicarFiltrosOferta);
            filtroTurno.addEventListener('change', aplicarFiltrosOferta);
            filtroParalelo.addEventListener('change', aplicarFiltrosOferta);

            btnLimpiarFiltros.addEventListener('click', function() {
                filtroCarrera.value = '';
                filtroGrado.value = '';
                filtroPeriodo.value = '';
                filtroTurno.value = '';
                filtroParalelo.value = '';
                aplicarFiltrosOferta();

                // Deseleccionar todas las materias al hacer click en limpiar
                checkboxesOfertas.forEach(chk => chk.checked = false);
                actualizarResumenMaterias();
            });

            // 5. Actualizar contador y lista visual de materias seleccionadas en Columna 4
            const contenedorMateriasSel = document.getElementById('lista-materias-seleccionadas-container');
            const placeholderSinMaterias = document.getElementById('sin-materias-seleccionadas');

            function actualizarResumenMaterias() {
                let seleccionadas = [];
                checkboxesOfertas.forEach(chk => {
                    if (chk.checked) {
                        const itemPadre = chk.closest('.oferta-item');
                        const textoEtiqueta = itemPadre.querySelector('strong').innerText;
                        const badgeSigla = itemPadre.querySelector('.badge').innerText;
                        seleccionadas.push({
                            sigla: badgeSigla,
                            nombre: textoEtiqueta
                        });
                    }
                });

                document.getElementById('resumen-materias-txt').innerHTML =
                    `Materias: <b class="text-success">${seleccionadas.length}</b>`;

                if (seleccionadas.length === 0) {
                    placeholderSinMaterias.classList.remove('d-none');
                    contenedorMateriasSel.classList.add('d-none');
                    contenedorMateriasSel.innerHTML = '';
                } else {
                    placeholderSinMaterias.classList.add('d-none');
                    contenedorMateriasSel.classList.remove('d-none');
                    let html = '';
                    seleccionadas.forEach(mat => {
                        html += `<div class="py-1 px-1 mb-1 bg-white border rounded small text-dark">
                                    <span class="badge badge-info">${mat.sigla}</span> <span>${mat.nombre}</span>
                               </div>`;
                    });
                    contenedorMateriasSel.innerHTML = html;
                }
            }

            checkboxesOfertas.forEach(chk => {
                chk.addEventListener('change', actualizarResumenMaterias);
            });

            // Inicializar contadores al cargar por si hay valores previos
            actualizarResumenEstudiantes();
            actualizarResumenMaterias();
        });
    </script>
@stop
