@extends('adminlte::page')
@section('title', 'Registrar Persona')
{{-- 1. HABILITAMOS EL PLUGIN DE SWEETALERT2 --}}
@section('plugins.Sweetalert2', true)

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 200px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        .card-body {
            padding: 0.75rem !important;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid pt-2">
        <form action="{{ route('admin.personas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                {{-- FOTO --}}
                <div class="col-md-3">
                    <div class="card card-outline card-primary shadow-sm mb-2">
                        <div class="card-body text-center">
                            <img id="preview" src="{{ asset('vendor/adminlte/dist/img/avatar.png') }}"
                                style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                            <input type="file" name="foto_path" id="fotoInput"
                                class="form-control-file form-control-sm mt-2" accept="image/*">
                        </div>
                    </div>
                </div>

                {{-- DATOS PERSONALES --}}
                <div class="col-md-9">
                    <div class="card shadow-sm mb-2">
                        <div class="card-header bg-primary py-1 text-white">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-user mr-2"></i> DATOS PERSONALES</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>CI</label>
                                    <input type="text" name="ci"
                                        class="form-control form-control-sm @error('ci') is-invalid @enderror"
                                        value="{{ old('ci') }}" required>
                                </div>
                                <div class="col-md-4"><label>Nombres</label><input type="text" name="nombres"
                                        class="form-control form-control-sm" value="{{ old('nombres') }}" required></div>
                                <div class="col-md-6"><label>Apellidos</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="ap_paterno" class="form-control" placeholder="Paterno"
                                            value="{{ old('ap_paterno') }}">
                                        <input type="text" name="ap_materno" class="form-control" placeholder="Materno"
                                            value="{{ old('ap_materno') }}">
                                    </div>
                                </div>
                                <div class="col-md-3"><label>Nacimiento</label><input type="date" name="fecha_nacimiento"
                                        class="form-control form-control-sm" value="{{ old('fecha_nacimiento') }}" required>
                                </div>
                                <div class="col-md-2"><label>Sexo</label>
                                    <select name="sexo" class="form-control form-control-sm">
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><label>Celular</label><input type="text" name="celular"
                                        class="form-control form-control-sm" value="{{ old('celular') }}"
                                        placeholder="70000000"></div>
                                <div class="col-md-4"><label>Email</label><input type="email" name="email_personal"
                                        class="form-control form-control-sm" value="{{ old('email_personal') }}"></div>
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
                                    class="form-control form-control-sm" value="{{ old('pais', 'Bolivia') }}"></div>
                            <div class="col-md-2"><label>Depto</label><input type="text" name="departamento"
                                    class="form-control form-control-sm" value="{{ old('departamento') }}"></div>
                            <div class="col-md-2"><label>Ciudad</label><input type="text" name="ciudad"
                                    class="form-control form-control-sm" value="{{ old('ciudad') }}"></div>
                            <div class="col-md-2"><label>Zona</label><input type="text" name="zona"
                                    class="form-control form-control-sm" value="{{ old('zona') }}"></div>
                            <div class="col-md-3"><label>Calle/Av</label><input type="text" name="avenida"
                                    class="form-control form-control-sm" value="{{ old('avenida') }}"></div>
                            <div class="col-md-1"><label>Nº</label><input type="text" name="numero"
                                    class="form-control form-control-sm" value="{{ old('numero') }}"></div>
                            <div class="col-md-2">
                                <label>Latitud</label>
                                <input type="number" step="any" name="latitud" id="lat"
                                    class="form-control form-control-sm" value="{{ old('latitud', -16.54069) }}"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Longitud</label>
                                <input type="number" step="any" name="longitud" id="lng"
                                    class="form-control form-control-sm" value="{{ old('longitud', -68.09611) }}"
                                    readonly>
                            </div>
                            <div class="col-md-6"><label>Referencia</label><input type="text" name="referencia"
                                    class="form-control form-control-sm" value="{{ old('referencia') }}"></div>
                            <div class="col-md-2">
                                <label>tipo</label>
                                <select name="tipo_domicilio" class="form-control form-control-sm">
                                    <option value="RESIDENCIA"
                                        {{ old('tipo_domicilio') == 'RESIDENCIA' ? 'selected' : '' }}>RESIDENCIA</option>
                                    <option value="TRABAJO" {{ old('tipo_domicilio') == 'TRABAJO' ? 'selected' : '' }}>
                                        TRABAJO</option>
                                    <option value="REFERENCIA"
                                        {{ old('tipo_domicilio') == 'REFERENCIA' ? 'selected' : '' }}>REFERENCIA</option>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> GUARDAR PERSONA</button>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            // Preview
            $('#fotoInput').change(function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = (e) => $('#preview').attr('src', e.target.result);
                    reader.readAsDataURL(this.files[0]);
                }
            });

            var map = null;

            function initMap() {
                if (!map) {
                    var lat = parseFloat($('#lat').val());
                    var lng = parseFloat($('#lng').val());
                    map = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    var marker = L.marker([lat, lng], {
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
        });

        {{-- 2. ALERTA AUTOMÁTICA DE SWEETALERT SI HAY UN ERROR (EJ. CI DUPLICADO) --}}
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: '¡Atención!',
                text: 'El número de CI ya está registrado o hay campos requeridos pendientes.',
                confirmButtonColor: '#007bff',
                confirmButtonText: 'Entendido'
            });
        @endif
    </script>
@stop
