@extends('adminlte::page')

@section('title', 'Detalles del Estudiante')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-user-graduate text-primary"></i> Detalles del Estudiante</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.estudiantes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la Lista
                </a>
                <a href="{{ route('admin.estudiantes.edit', $estudiante->id) }}" class="btn btn-success">
                    <i class="fas fa-edit"></i> Editar Estudiante
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- COLUMNA IZQUIERDA: INFORMACIÓN GENERAL Y ACADÉMICA -->
        <div class="col-md-7">
            <!-- Tarjeta de Datos Personales -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card mr-2"></i> Información Personal</h3>
                </div>
                <div class="card-body box-profile">
                    <div class="text-center mb-3">
                        @if ($estudiante->persona->foto_path)
                            <img class="profile-user-img img-fluid img-circle"
                                src="{{ asset('storage/' . $estudiante->persona->foto_path) }}" alt="Foto de perfil">
                        @else
                            <img class="profile-user-img img-fluid img-circle"
                                src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" alt="Foto por defecto">
                        @endif
                    </div>

                    <h3 class="profile-username text-center">
                        {{ $estudiante->persona->nombres }}
                        {{ $estudiante->persona->ap_paterno }}
                        {{ $estudiante->persona->ap_materno }}
                    </h3>

                    <p class="text-muted text-center">
                        <span class="badge badge-info" style="font-size: 14px;">
                            RU: {{ $estudiante->registro_universitario ?? 'Sin Asignar' }}
                        </span>
                    </p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Cédula de Identidad (CI)</b> <a
                                class="float-right">{{ $estudiante->persona->ci ?? 'S/C' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Fecha de Nacimiento</b> <a
                                class="float-right">{{ $estudiante->persona->fecha_nacimiento ?? 'No registrada' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Sexo</b> <a
                                class="float-right">{{ $estudiante->persona->sexo == 'M' ? 'Masculino' : ($estudiante->persona->sexo == 'F' ? 'Femenino' : 'No especificado') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Celular / Teléfono</b> <a
                                class="float-right">{{ $estudiante->persona->celular ?? 'No registrado' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Correo Personal</b> <a
                                class="float-right">{{ $estudiante->persona->email_personal ?? 'No registrado' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Estado Académico</b>
                            <span class="float-right badge"
                                style="background-color: {{ $estudiante->estado->color_hex ?? '#6c757d' }}; color: white; font-size: 13px;">
                                {{ $estudiante->estado->nombre ?? 'N/A' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tarjeta de Domicilio (Si lo tiene vinculado) -->
            @if ($estudiante->persona->domicilio)
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i> Información de Domicilio</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-unbordered mb-0">
                            <li class="list-group-item">
                                <b>País / Departamento</b> <a
                                    class="float-right">{{ $estudiante->persona->domicilio->pais }} /
                                    {{ $estudiante->persona->domicilio->departamento }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Ciudad / Zona</b> <a class="float-right">{{ $estudiante->persona->domicilio->ciudad }} -
                                    {{ $estudiante->persona->domicilio->zona ?? 'S/N' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Dirección (Av./Calle/Nro)</b> <a
                                    class="float-right">{{ $estudiante->persona->domicilio->avenida ?? '' }} Nro.
                                    {{ $estudiante->persona->domicilio->numero ?? 'S/N' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Referencia</b> <span
                                    class="text-muted float-right">{{ $estudiante->persona->domicilio->referencia ?? 'Ninguna' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- COLUMNA DERECHA: PADRES O APODERADOS -->
        <div class="col-md-5">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tie mr-2"></i> Padre o Apoderado (PPFF)</h3>
                </div>
                <div class="card-body">
                    @if ($estudiante->ppffs->count() > 0)
                        @foreach ($estudiante->ppffs as $ppff)
                            <div class="callout callout-warning">
                                <h5><strong>{{ $ppff->pivot->parentesco }}</strong>
                                    @if ($ppff->pivot->es_tutor_principal)
                                        <span class="badge badge-success float-right">Tutor Principal</span>
                                    @endif
                                </h5>
                                <hr class="my-2">
                                <p class="mb-1"><strong>Nombre:</strong> {{ $ppff->nombres }} {{ $ppff->ap_paterno }}
                                    {{ $ppff->ap_materno }}</p>
                                <p class="mb-1"><strong>C.I.:</strong> {{ $ppff->ci ?? 'S/C' }}</p>
                                <p class="mb-1"><strong>Celular:</strong> {{ $ppff->celular ?? 'No registrado' }}</p>
                                <p class="mb-0"><strong>Correo:</strong> {{ $ppff->email_personal ?? 'No registrado' }}
                                </p>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-default-secondary text-center mb-0">
                            <i class="fas fa-info-circle"></i> Este estudiante no tiene ningún padre o apoderado registrado.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
