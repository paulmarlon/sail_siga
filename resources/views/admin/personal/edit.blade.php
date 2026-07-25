@extends('adminlte::page')

@section('title', 'Editar Personal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><b>Editar Personal:</b> <span class="text-primary">{{ ucfirst($personal->tipo) }}</span></h1>
        <a href="{{ route('admin.personal.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
    <hr>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-edit mr-1"></i> Modificar Datos de Personal</h3>
                </div>

                <form action="{{ route('admin.personal.update', $personal->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- ID de la persona vinculada (Ya no cambia, solo lectura o guardado oculto) -->
                    <input type="hidden" name="persona_id" value="{{ $personal->persona_id }}">

                    <div class="card-body">

                        <!-- SECCIÓN 2: DATOS BIOGRÁFICOS (Solo lectura, ya que pertenecen a la persona base) -->
                        <div id="seccion_datos_persona">
                            <h5 class="text-secondary mb-3"><i class="fas fa-id-card"></i> Datos Biográficos (Persona
                                Vinculada)</h5>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Cédula de Identidad (CI)</label>
                                        <input type="text" class="form-control"
                                            value="{{ $personal->persona->ci ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nombres</label>
                                        <input type="text" class="form-control"
                                            value="{{ $personal->persona->nombres ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Apellido Paterno</label>
                                        <input type="text" class="form-control"
                                            value="{{ $personal->persona->ap_paterno ?? '' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Apellido Materno</label>
                                        <input type="text" class="form-control"
                                            value="{{ $personal->persona->ap_materno ?? '' }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: DATOS PROPIOS DE LA TABLA PERSONAL -->
                        <div id="seccion_datos_laborales">
                            <hr class="my-4">
                            <h5 class="text-secondary mb-3"><i class="fas fa-briefcase"></i> Datos Laborales y de Acceso
                            </h5>

                            <div class="row">
                                <!-- Tipo de Personal -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tipo">Tipo de Personal</label><b> (*)</b>
                                        <select name="tipo" id="tipo"
                                            class="form-control @error('tipo') is-invalid @enderror" required>
                                            <option value="">Seleccione tipo...</option>
                                            <option value="docente"
                                                {{ old('tipo', $personal->tipo) == 'docente' ? 'selected' : '' }}>
                                                Docente</option>
                                            <option value="administrativo"
                                                {{ old('tipo', $personal->tipo) == 'administrativo' ? 'selected' : '' }}>
                                                Administrativo</option>
                                            <option value="planta"
                                                {{ old('tipo', $personal->tipo) == 'planta' ? 'selected' : '' }}>
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
                                            value="{{ old('profesion', $personal->profesion) }}"
                                            placeholder="Ej. Ing. de Sistemas" required>
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
                                                    {{ old('estado_id', $personal->estado_id) == $estado->id ? 'selected' : '' }}>
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
                                                    {{ old('rol', optional($personal->usuario->roles->first())->name) == $rol->name ? 'selected' : '' }}>
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
                                            value="{{ old('email', optional($personal->usuario)->email) }}"
                                            placeholder="usuario@correo.com">
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Botones de Acción -->
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.personal.index') }}" class="btn btn-default mr-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Actualizar Registro de Personal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
