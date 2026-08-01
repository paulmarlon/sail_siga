@extends('adminlte::page')

@section('title', 'Crear Estudiante')

@section('content_header')
    <h1><b>Creación de un Nuevo Estudiante</b></h1>
    <hr>
@stop

@section('content')
    <form action="{{ route('admin.estudiantes.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- COLUMNA IZQUIERDA: DATOS DEL ESTUDIANTE -->
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i> Datos del Estudiante</h3>
                    </div>
                    <div class="card-body">
                        <!-- Campo oculto para el ID de la Persona (Estudiante) -->
                        <input type="hidden" id="persona_id" name="persona_id" value="{{ old('persona_id') }}" required>

                        <div class="row">
                            <div class="col-md-12 mb-3 text-center">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                    data-target="#modalBuscarEstudiante">
                                    <i class="fas fa-search"></i> Seleccionar Persona (Estudiante)
                                </button>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombres_est">Nombres (*)</label>
                                    <input type="text" class="form-control" id="nombres_est" name="nombres_est"
                                        value="{{ old('nombres_est') }}" placeholder="Seleccione una persona..." readonly
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apellidos_est">Apellidos (*)</label>
                                    <input type="text" class="form-control" id="apellidos_est" name="apellidos_est"
                                        value="{{ old('apellidos_est') }}" placeholder="Seleccione una persona..." readonly
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ci_est">Cédula de Identidad</label>
                                    <input type="text" class="form-control" id="ci_est" name="ci_est"
                                        value="{{ old('ci_est') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de Nacimiento (*)</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento_est"
                                        name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                                </div>
                            </div>

                            <!-- NUEVOS CAMPOS: RU y ESTADO -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registro_universitario">Registro Universitario (RU) (*)</label>
                                    <input type="text"
                                        class="form-control @error('registro_universitario') is-invalid @enderror"
                                        id="registro_universitario" name="registro_universitario"
                                        value="{{ old('registro_universitario') }}" placeholder="Ej. 202600123" required>
                                    @error('registro_universitario')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estado_id">Estado (*)</label>
                                    <select name="estado_id" id="estado_id"
                                        class="form-control @error('estado_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un estado...</option>
                                        {{-- Asegúrate de pasar la variable $estados desde tu controlador --}}
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}"
                                                {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                                                {{ $estado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('estado_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: DATOS DEL PADRE / APODERADO -->
            <div class="col-md-5">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-tie mr-2"></i> Padre o Apoderado (Opcional)</h3>
                    </div>
                    <div class="card-body">
                        <!-- Campo oculto para guardar el ID del PPFF -->
                        <input type="hidden" id="ppff_id" name="ppff_id" value="{{ old('ppff_id') }}">

                        <div class="row">
                            <div class="col-md-12 mb-3 text-center">
                                <button type="button" class="btn btn-outline-warning btn-sm" data-toggle="modal"
                                    data-target="#modalBuscarPpff">
                                    <i class="fas fa-search"></i> Buscar Padre o Apoderado
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm ml-2" id="btnQuitarPpff">
                                    <i class="fas fa-times"></i> Quitar
                                </button>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Nombre del Padre/Apoderado</label>
                                    <input type="text" class="form-control" id="ppff_nombre_completo"
                                        placeholder="Ningún padre seleccionado (Opcional)..." readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cédula de Identidad</label>
                                    <input type="text" class="form-control" id="ppff_ci" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" class="form-control" id="ppff_telefono" readonly>
                                </div>
                            </div>

                            <!-- NUEVOS CAMPOS: Parentesco y Tutor Principal -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="parentesco">Parentesco (*)</label>
                                    <select name="parentesco" id="parentesco" class="form-control">
                                        <option value="Padre/Madre">Padre / Madre</option>
                                        <option value="Tutor Legal" selected>Tutor Legal</option>
                                        <option value="Familiar">Familiar</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="es_tutor_principal">¿Es Tutor Principal?</label>
                                    <select name="es_tutor_principal" id="es_tutor_principal" class="form-control">
                                        <option value="1" selected>Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTÓN DE GUARDAR FINAL -->
        <div class="row">
            <div class="col-md-12 text-right mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Guardar
                    Estudiante</button>
            </div>
        </div>
    </form>

    <!-- ========================================== -->
    <!-- MODAL 1: SELECCIONAR ESTUDIANTE (PERSONA)  -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalBuscarEstudiante" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Seleccionar Persona como Estudiante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="tablaEstudiantesModal" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Nombre Completo (Nombres y Apellidos)</th>
                                <th>CI</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ppffs as $persona)
                                @php
                                    $nombresPersona = $persona->nombres;
                                    $apellidosPersona = trim(
                                        ($persona->ap_paterno ?? '') . ' ' . ($persona->ap_materno ?? ''),
                                    );
                                    $nombreCompletoEst = trim($nombresPersona . ' ' . $apellidosPersona);
                                @endphp
                                <tr id="fila-est-{{ $persona->id }}">
                                    <td>{{ $nombreCompletoEst }}</td>
                                    <td>{{ $persona->ci }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-primary btn-seleccionar-estudiante"
                                            data-id="{{ $persona->id }}" data-nombres="{{ $persona->nombres }}"
                                            data-apellidos="{{ trim(($persona->ap_paterno ?? '') . ' ' . ($persona->ap_materno ?? '')) }}"
                                            data-ci="{{ $persona->ci }}"
                                            data-fecha_nacimiento="{{ $persona->fecha_nacimiento }}">
                                            <i class="fas fa-check"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: SELECCIONAR PADRE DE FAMILIA      -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalBuscarPpff" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Seleccionar Padre de Familia o Apoderado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="tablaPpffModal" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Nombre Completo (Nombres y Apellidos)</th>
                                <th>CI</th>
                                <th>Teléfono</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ppffs as $ppff)
                                @php
                                    $nombresPpff = $ppff->nombres;
                                    $apellidosPpff = trim(($ppff->ap_paterno ?? '') . ' ' . ($ppff->ap_materno ?? ''));
                                    $nombreCompletoPpff = trim($nombresPpff . ' ' . $apellidosPpff);
                                @endphp
                                <tr id="fila-ppff-{{ $ppff->id }}">
                                    <td>{{ $nombreCompletoPpff }}</td>
                                    <td>{{ $ppff->ci }}</td>
                                    <td>{{ $ppff->celular ?? 'S/N' }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-success btn-xs btn-seleccionar-ppff"
                                            data-id="{{ $ppff->id }}" data-nombre="{{ $nombreCompletoPpff }}"
                                            data-ci="{{ $ppff->ci }}" data-telefono="{{ $ppff->celular ?? '' }}">
                                            <i class="fas fa-check"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tablaEstudiantesModal').DataTable();
            $('#tablaPpffModal').DataTable();

            // Función para actualizar la visibilidad en las tablas y evitar auto-asignación
            function actualizarFiltrosCruzados() {
                var estudianteId = $('#persona_id').val();
                var ppffId = $('#ppff_id').val();

                // Mostrar todas las filas primero
                $('tr[id^="fila-est-"]').show();
                $('tr[id^="fila-ppff-"]').show();

                // Si hay un estudiante seleccionado, ocultarlo de la lista de apoderados
                if (estudianteId) {
                    $('#fila-ppff-' + estudianteId).hide();
                }

                // Si hay un apoderado seleccionado, ocultarlo de la lista de estudiantes
                if (ppffId) {
                    $('#fila-est-' + ppffId).hide();
                }
            }

            // 1. Lógica para seleccionar el Estudiante
            $(document).on('click', '.btn-seleccionar-estudiante', function() {
                var id = $(this).data('id');
                var ppffActualId = $('#ppff_id').val();

                if (id == ppffActualId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Acción no permitida',
                        text: 'Una persona no puede ser apoderada de sí misma.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                $('#persona_id').val(id);
                $('#nombres_est').val($(this).data('nombres'));
                $('#apellidos_est').val($(this).data('apellidos'));
                $('#ci_est').val($(this).data('ci'));
                $('#fecha_nacimiento_est').val($(this).data('fecha_nacimiento'));

                $('#modalBuscarEstudiante').modal('hide');
                actualizarFiltrosCruzados();
            });

            // 2. Lógica para seleccionar el Padre / Apoderado
            $(document).on('click', '.btn-seleccionar-ppff', function() {
                var id = $(this).data('id');
                var estudianteActualId = $('#persona_id').val();

                if (id == estudianteActualId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Acción no permitida',
                        text: 'El estudiante seleccionado no puede ser su propio apoderado.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                $('#ppff_id').val(id);
                $('#ppff_nombre_completo').val($(this).data('nombre'));
                $('#ppff_ci').val($(this).data('ci'));
                $('#ppff_telefono').val($(this).data('telefono'));

                $('#modalBuscarPpff').modal('hide');
                actualizarFiltrosCruzados();
            });

            // 3. Botón para limpiar/quitar el Apoderado opcional
            $('#btnQuitarPpff').click(function() {
                $('#ppff_id').val('');
                $('#ppff_nombre_completo').val('');
                $('#ppff_ci').val('');
                $('#ppff_telefono').val('');
                actualizarFiltrosCruzados();

                Swal.fire({
                    icon: 'info',
                    title: 'Apoderado removido',
                    text: 'El estudiante se registrará sin apoderado asignado.',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
    </script>
@stop
