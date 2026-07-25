@extends('adminlte::page')

@section('title', 'Asignar Personal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><b>Registro de Personal:</b> <span class="text-primary">{{ ucfirst($tipo) }}</span></h1>
        <a href="{{ route('admin.personal.index', $tipo) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tag mr-1"></i> Vincular Persona a Personal</h3>
                </div>

                <form action="{{ route('admin.personal.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- ID de la persona seleccionada -->
                    <input type="hidden" name="persona_id" id="persona_id" value="{{ old('persona_id') }}">

                    <div class="card-body">

                        <!-- SECCIÓN 1: BUSCADOR / AUTOCOMPLETADO -->
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-search"></i> Paso 1: Buscar Persona</h5>
                            Escriba el <b>número de CI</b>, <b>nombres</b> o <b>apellidos</b> para buscar y autocompletar.
                        </div>

                        <div class="row">
                            <div class="col-md-8 position-relative">
                                <div class="form-group">
                                    <label for="buscador_persona">Buscar por Cédula o Nombres</label><b> (*)</b>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" id="buscador_persona" class="form-control"
                                            placeholder="Ej. 1234567 o Juan Pérez..." autocomplete="off">
                                    </div>
                                    <!-- Contenedor flotante para los resultados de la búsqueda -->
                                    <div id="lista_resultados" class="list-group position-absolute w-100 shadow"
                                        style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: DATOS BIOGRÁFICOS (Autocompletados / Solo lectura) -->
                        <div id="seccion_datos_persona" style="display: none;">
                            <hr class="my-4">
                            <h5 class="text-secondary mb-3"><i class="fas fa-id-card"></i> Datos Biográficos (Seleccionados)
                            </h5>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Cédula de Identidad (CI)</label>
                                        <input type="text" id="view_ci" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nombres</label>
                                        <input type="text" id="view_nombres" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Apellido Paterno</label>
                                        <input type="text" id="view_ap_paterno" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Apellido Materno</label>
                                        <input type="text" id="view_ap_materno" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: DATOS PROPIOS DE LA TABLA PERSONAL -->
                        <div id="seccion_datos_laborales" style="display: none;">
                            <hr class="my-4">
                            <h5 class="text-secondary mb-3"><i class="fas fa-briefcase"></i> Datos Laborales y de Acceso
                            </h5>

                            <div class="row">
                                <!-- Tipo de Personal (Selector controlado para evitar texto libre) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tipo">Tipo de Personal</label><b> (*)</b>
                                        <select name="tipo" id="tipo"
                                            class="form-control @error('tipo') is-invalid @enderror" required>
                                            <option value="">Seleccione tipo...</option>
                                            <option value="docente" {{ old('tipo', $tipo) == 'docente' ? 'selected' : '' }}>
                                                Docente</option>
                                            <option value="administrativo"
                                                {{ old('tipo', $tipo) == 'administrativo' ? 'selected' : '' }}>
                                                Administrativo</option>
                                            <option value="planta" {{ old('tipo', $tipo) == 'planta' ? 'selected' : '' }}>
                                                Personal de Planta</option>
                                        </select>
                                        @error('tipo')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Profesión -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="profesion">Profesión u Oficio</label><b> (*)</b>
                                        <input type="text" name="profesion" id="profesion"
                                            class="form-control @error('profesion') is-invalid @enderror"
                                            value="{{ old('profesion') }}" placeholder="Ej. Ing. de Sistemas" required>
                                        @error('profesion')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="estado_id">Estado Laboral</label><b> (*)</b>
                                        <select name="estado_id" id="estado_id"
                                            class="form-control @error('estado_id') is-invalid @enderror" required>
                                            <option value="">Seleccione estado...</option>
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id }}"
                                                    {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                                                    {{ $estado->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('estado_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Rol -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="rol">Rol de Usuario</label>
                                        <select name="rol" id="rol" class="form-control">
                                            <option value="">Sin acceso al sistema (Opcional)</option>
                                            @foreach ($roles as $rol)
                                                <option value="{{ $rol->name }}"
                                                    {{ old('rol') == $rol->name ? 'selected' : '' }}>
                                                    {{ $rol->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Email de Sistema -->
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="email">Correo Electrónico (Sistema)</label>
                                        <input type="email" name="email" id="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" placeholder="usuario@correo.com">
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Botones de Acción -->
                    <div class="card-footer text-right" id="footer_acciones" style="display: none;">
                        <a href="{{ route('admin.personal.index', $tipo) }}" class="btn btn-default mr-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Registro de Personal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        console.log("El script de autocompletado se cargó correctamente.");

        const inputBuscador = document.getElementById('buscador_persona');
        const listaResultados = document.getElementById('lista_resultados');

        if (!inputBuscador) {
            console.error("¡No se encontró el elemento buscador_persona en el DOM!");
        }

        inputBuscador.addEventListener('input', function() {
            let query = this.value.trim();
            console.log("Escribiendo:", query);

            if (query.length < 2) {
                listaResultados.style.display = 'none';
                return;
            }

            let url = `{{ route('admin.personas.autocomplete') }}?query=${encodeURIComponent(query)}`;
            console.log("Peticionando a:", url);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log("Datos recibidos:", data);
                    listaResultados.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(persona => {
                            let item = document.createElement('a');
                            item.href = 'javascript:void(0);';

                            // Verificamos si ya tiene un registro en la tabla personal
                            let yaEsPersonal = persona.personal !== null;

                            if (yaEsPersonal) {
                                // Estilo visual para los que ya tienen cargo (Rojo / Deshabilitado)
                                item.className =
                                    'list-group-item list-group-item-action bg-light text-danger';
                                item.innerHTML = `
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>CI:</strong> ${persona.ci} - ${persona.nombres} ${persona.ap_paterno}
                                        </div>
                                        <span class="badge badge-danger">Ya registrado como ${persona.personal.tipo}</span>
                                    </div>`;

                                // Evitamos que se pueda seleccionar haciendo clic para crear
                                item.addEventListener('click', function() {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Persona ya registrada',
                                        text: `Esta persona ya cuenta con un registro activo como "${persona.personal.tipo}". Debe editar su registro existente si desea modificar sus datos.`,
                                        confirmButtonText: 'Entendido'
                                    });
                                });

                            } else {
                                // Estilo normal para los que están disponibles
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML =
                                    `
                                    <strong>CI:</strong> ${persona.ci} - ${persona.nombres} ${persona.ap_paterno}
                                    <span class="float-right text-success"><i class="fas fa-check-circle"></i> Disponible</span>`;

                                item.addEventListener('click', function() {
                                    document.getElementById('persona_id').value = persona.id;
                                    document.getElementById('view_ci').value = persona.ci;
                                    document.getElementById('view_nombres').value = persona
                                        .nombres;
                                    document.getElementById('view_ap_paterno').value = persona
                                        .ap_paterno;
                                    document.getElementById('view_ap_materno').value = persona
                                        .ap_materno ?? '';

                                    inputBuscador.value =
                                        `${persona.nombres} ${persona.ap_paterno} (${persona.ci})`;
                                    listaResultados.style.display = 'none';
                                    document.getElementById('seccion_datos_persona').style
                                        .display = 'block';
                                    document.getElementById('seccion_datos_laborales').style
                                        .display = 'block';
                                    document.getElementById('footer_acciones').style.display =
                                        'block';
                                });
                            }

                            listaResultados.appendChild(item);
                        });
                        listaResultados.style.display = 'block';
                    } else {
                        listaResultados.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error en fetch:', error));
        });
    </script>
@stop
