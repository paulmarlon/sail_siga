@extends('adminlte::page')
@section('title', 'Editar Persona')
@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 200px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }

        .card-body {
            padding: 0.75rem !important;
        }

        .mb-3 {
            margin-bottom: 0.5rem !important;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid pt-2">
        <form action="{{ route('admin.personas.update', $persona->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                {{-- FOTO --}}
                <div class="col-md-3">
                    <div class="card card-outline card-success shadow-sm mb-2">
                        <div class="card-body text-center">
                            <img id="preview"
                                src="{{ $persona->foto_path ? asset('storage/' . $persona->foto_path) : asset('vendor/adminlte/dist/img/avatar.png') }}"
                                style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                            <input type="file" name="foto_path" id="fotoInput"
                                class="form-control-file form-control-sm mt-2" accept="image/*">
                        </div>
                    </div>
                </div>

                {{-- DATOS PERSONALES --}}
                <div class="col-md-9">
                    <div class="card shadow-sm mb-2">
                        <div class="card-header bg-success py-1">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-user mr-2"></i> DATOS PERSONALES</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2"><label>CI</label><input type="text" name="ci"
                                        class="form-control form-control-sm uppercase" value="{{ old('ci', $persona->ci) }}"
                                        required>
                                </div>
                                <div class="col-md-4"><label>Nombres</label><input type="text" name="nombres"
                                        class="form-control form-control-sm uppercase"
                                        value="{{ old('nombres', $persona->nombres) }}" required></div>
                                <div class="col-md-6"><label>Apellidos</label>
                                    <div class="input-group input-group-sm uppercase">
                                        <input type="text" name="ap_paterno" class="form-control"
                                            value="{{ old('ap_paterno', $persona->ap_paterno) }}" placeholder="Paterno">
                                        <input type="text" name="ap_materno" class="form-control"
                                            value="{{ old('ap_materno', $persona->ap_materno) }}" placeholder="Materno">
                                    </div>
                                </div>
                                <div class="col-md-3"><label>Nacimiento</label><input type="date" name="fecha_nacimiento"
                                        class="form-control form-control-sm"
                                        value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento) }}" required></div>
                                <div class="col-md-2"><label>Sexo</label>
                                    <select name="sexo" class="form-control form-control-sm uppercase">
                                        <option value="M" {{ old('sexo', $persona->sexo) == 'M' ? 'selected' : '' }}>
                                            Masculino</option>
                                        <option value="F" {{ old('sexo', $persona->sexo) == 'F' ? 'selected' : '' }}>
                                            Femenino</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><label>Celular</label><input type="text" name="celular"
                                        class="form-control form-control-sm"
                                        value="{{ old('celular', $persona->celular) }}"></div>
                                <div class="col-md-4"><label>Email</label><input type="email" name="email_personal"
                                        class="form-control form-control-sm"
                                        value="{{ old('email_personal', $persona->email_personal) }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DOMICILIO Y MAPA --}}
            <div class="card mt-1">
                <div class="card-header bg-secondary py-1">
                    <button type="button" class="btn btn-sm text-white p-0" data-toggle="collapse"
                        data-target="#collapseDomicilio">
                        <i class="fas fa-map-marker-alt"></i> UBICACIÓN Y DOMICILIO (DESPLEGAR)
                    </button>
                </div>
                <div id="collapseDomicilio" class="collapse">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2"><label>País</label><input type="text" name="pais"
                                    class="form-control form-control-sm uppercase"
                                    value="{{ old('pais', $persona->domicilio->pais ?? 'Bolivia') }}"></div>
                            <div class="col-md-2"><label>Depto</label><input type="text" name="departamento"
                                    class="form-control form-control-sm uppercase"
                                    value="{{ old('departamento', $persona->domicilio->departamento ?? '') }}"></div>
                            <div class="col-md-2"><label>Ciudad</label><input type="text" name="ciudad"
                                    class="form-control form-control-sm uppercase"
                                    value="{{ old('ciudad', $persona->domicilio->ciudad ?? '') }}"></div>
                            <div class="col-md-2"><label>Zona</label><input type="text" name="zona"
                                    class="form-control form-control-sm uppercase"
                                    value="{{ old('zona', $persona->domicilio->zona ?? '') }}"></div>
                            <div class="col-md-3"><label>Calle/Av</label><input type="text" name="avenida"
                                    class="form-control form-control-sm uppercase"
                                    value="{{ old('avenida', $persona->domicilio->avenida ?? '') }}"></div>
                            <div class="col-md-1"><label>Nº</label><input type="text" name="numero"
                                    class="form-control form-control-sm"
                                    value="{{ old('numero', $persona->domicilio->numero ?? '') }}"></div>
                            <div class="col-md-2"><label>Latitud</label><input type="number" step="any"
                                    name="latitud" id="lat" class="form-control form-control-sm"
                                    value="{{ old('latitud', $persona->domicilio->latitud ?? -16.54069) }}" readonly>
                            </div>
                            <div class="col-md-2"><label>Longitud</label><input type="number" step="any"
                                    name="longitud" id="lng" class="form-control form-control-sm"
                                    value="{{ old('longitud', $persona->domicilio->longitud ?? -68.09611) }}" readonly>
                            </div>
                            <div class="col-md-6"><label>Referencia</label><input type="text" name="referencia"
                                    class="form-control form-control-sm uppercase"
                                    value="{{ old('referencia', $persona->domicilio->referencia ?? '') }}"></div>
                            <div class="col-md-2">
                                <label>TIPO</label>
                                <select name="tipo_domicilio" class="form-control form-control-sm">
                                    <option value="RESIDENCIA"
                                        {{ old('tipo_domicilio', $persona->domicilio->tipo_domicilio ?? '') == 'RESIDENCIA' ? 'selected' : '' }}>
                                        RESIDENCIA
                                    </option>
                                    <option value="TRABAJO"
                                        {{ old('tipo_domicilio', $persona->domicilio->tipo_domicilio ?? '') == 'TRABAJO' ? 'selected' : '' }}>
                                        TRABAJO
                                    </option>
                                    <option value="REFERENCIA"
                                        {{ old('tipo_domicilio', $persona->domicilio->tipo_domicilio ?? '') == 'REFERENCIA' ? 'selected' : '' }}>
                                        REFERENCIA
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-2 mb-4">
                <a href="{{ route('admin.personas.index') }}" class="btn btn-default">CANCELAR</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> ACTUALIZAR</button>
            </div>
        </form>
    </div>
@stop
@section('css')
    <style>
        .uppercase {
            text-transform: uppercase;
        }
    </style>
@stop
@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            var map = null;

            function initMap() {
                if (!map) {
                    map = L.map('map').setView([parseFloat($('#lat').val()), parseFloat($('#lng').val())], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    var marker = L.marker([parseFloat($('#lat').val()), parseFloat($('#lng').val())], {
                        draggable: true
                    }).addTo(map);
                    marker.on('dragend', (e) => {
                        $('#lat').val(e.target.getLatLng().lat.toFixed(6));
                        $('#lng').val(e.target.getLatLng().lng.toFixed(6));
                    });
                }
            }
            $('#collapseDomicilio').on('shown.bs.collapse', () => {
                initMap();
                map.invalidateSize();
            });
            @if (isset($persona->domicilio))
                $('#collapseDomicilio').collapse('show');
            @endif
        });
        $('#fotoInput').change(function() {
            if (this.files && this.files[0]) {
                let reader = new FileReader();

                reader.onload = (e) => {
                    $('#preview').attr('src', e.target.result);
                }

                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
@stop
