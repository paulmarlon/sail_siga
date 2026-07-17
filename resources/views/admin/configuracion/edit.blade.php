@section('adminlte_css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@stop
@extends('adminlte::page')
@section('content')
    <div class="container-fluid pt-3">
        <form action="{{ route('admin.configuracion.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row">
                {{-- IZQUIERDA: LOGO --}}
                <div class="col-md-3">
                    <div class="card card-outline shadow-sm border-0" style="border-top: 3px solid #007bff;">
                        <div class="card-header bg-primary text-white text-center">
                            <h3 class="card-title w-100 font-weight-bold text-uppercase" style="font-size: 1rem;">Logo
                                Institucional</h3>
                        </div>
                        <div class="card-body text-center">
                            <div class="my-2 mx-auto"
                                style="width: 150px; height: 150px; border: 1px dashed #ddd; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <img id="preview"
                                    src="{{ $configuracion->logo_path ? asset('storage/' . $configuracion->logo_path) : asset('img/default.png') }}"
                                    style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                            <input type="file" name="logo_path" id="logoInput" class="form-control-file mt-3"
                                accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-block btn-primary shadow-sm"><i class="fas fa-save mr-1"></i>
                        GUARDAR CAMBIOS</button>
                </div>

                {{-- DERECHA: DATOS FORMULARIO --}}
                <div class="col-md-9" id="accordion">
                    {{-- DATOS INSTITUCIONALES --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-university mr-2"></i> DATOS
                                INSTITUCIONALES</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3"><label>Nombre Institucional</label><input type="text"
                                        name="nombre_institucion" class="form-control form-control-sm"
                                        value="{{ $configuracion->nombre_institucion }}" required></div>
                                <div class="col-md-4 mb-3"><label>Sigla</label><input type="text"
                                        name="sigla_institucion" class="form-control form-control-sm"
                                        value="{{ $configuracion->sigla_institucion }}"></div>
                                <div class="col-md-4 mb-3"><label>NIT</label><input type="text" name="nit"
                                        class="form-control form-control-sm" value="{{ $configuracion->nit }}"></div>
                                <div class="col-md-4 mb-3"><label>Teléfono</label><input type="text" name="telefono"
                                        class="form-control form-control-sm" value="{{ $configuracion->telefono }}"></div>
                                <div class="col-md-4 mb-3"><label>Email Contacto</label><input type="email"
                                        name="email_contacto" class="form-control form-control-sm"
                                        value="{{ $configuracion->email_contacto }}"></div>
                                <div class="col-md-4 mb-3"><label>Sitio Web</label><input type="text" name="web"
                                        class="form-control form-control-sm" value="{{ $configuracion->web }}"></div>
                                <div class="col-md-4 mb-3">
                                    <label>Divisa</label>
                                    <select name="divisa" class="form-control form-control-sm">
                                        @foreach ($divisas as $code => $info)
                                            <option value="{{ $code }}"
                                                {{ ($configuracion->divisa ?? 'BOB') == $code ? 'selected' : '' }}>
                                                {{ $code }} - {{ $info['name'] }} ({{ $info['symbol'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3"><label>Gestión Activa</label>
                                    <select name="gestion_actual_id" class="form-control form-control-sm">
                                        @foreach ($gestiones as $g)
                                            <option value="{{ $g->id }}"
                                                {{ $configuracion->gestion_actual_id == $g->id ? 'selected' : '' }}>
                                                {{ $g->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACORDEÓN DOMICILIO ADAPTADO --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white p-0">
                            <button type="button" class="btn btn-block text-left text-white font-weight-bold py-2 px-3"
                                data-toggle="collapse" data-target="#collapseDomicilio">
                                <i class="fas fa-map-marker-alt mr-2"></i> UBICACIÓN Y DOMICILIO
                            </button>
                        </div>
                        <div id="collapseDomicilio" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3"><label>País</label><input type="text" name="pais"
                                            class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->pais ?? 'Bolivia' }}"></div>
                                    <div class="col-md-3 mb-3"><label>Departamento</label><input type="text"
                                            name="departamento" class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->departamento ?? '' }}"></div>
                                    <div class="col-md-3 mb-3"><label>Ciudad</label><input type="text" name="ciudad"
                                            class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->ciudad ?? '' }}"></div>
                                    <div class="col-md-3 mb-3"><label>Zona</label><input type="text" name="zona"
                                            class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->zona ?? '' }}"></div>

                                    <div class="col-md-4 mb-3"><label>Avenida/Calle</label><input type="text"
                                            name="avenida" class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->avenida ?? '' }}"></div>
                                    <div class="col-md-2 mb-3"><label>Número</label><input type="text" name="numero"
                                            class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->numero ?? '' }}"></div>

                                    {{-- NUEVOS CAMPOS DE GEOLOCALIZACIÓN --}}
                                    <div class="col-md-3 mb-3">
                                        <label>Latitud</label>
                                        <input type="number" step="any" name="latitud"
                                            class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->latitud ?? '' }}"
                                            placeholder="Ej: -16.495">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Longitud</label>
                                        <input type="number" step="any" name="longitud"
                                            class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->longitud ?? '' }}"
                                            placeholder="Ej: -68.133">
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <div id="map" style="height: 300px; width: 100%; border-radius: 5px;"></div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Tipo de Domicilio</label>
                                        <select name="tipo_domicilio" class="form-control form-control-sm">
                                            <option value="Residencia"
                                                {{ ($configuracion->domicilio->tipo_domicilio ?? '') == 'Residencia' ? 'selected' : '' }}>
                                                Residencia</option>
                                            <option value="Trabajo"
                                                {{ ($configuracion->domicilio->tipo_domicilio ?? '') == 'Trabajo' ? 'selected' : '' }}>
                                                Trabajo</option>
                                            <option value="Referencia"
                                                {{ ($configuracion->domicilio->tipo_domicilio ?? '') == 'Referencia' ? 'selected' : '' }}>
                                                Referencia</option>
                                        </select>
                                    </div>

                                    <div class="col-md-9 mb-3"><label>Mayor referencia</label><input type="text"
                                            name="referencia" class="form-control form-control-sm"
                                            value="{{ $configuracion->domicilio->referencia ?? '' }}">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function() {
            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif

            $('#logoInput').change(function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = (e) => $('#preview').attr('src', e.target.result);
                    reader.readAsDataURL(this.files[0]);
                }
            });
            // Lógica del MAPA
            var lat = $('input[name="latitud"]').val() || -16.4897;
            var lng = $('input[name="longitud"]').val() || -68.1193;

            var map = L.map('map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            var marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(map);

            // Actualizar inputs al mover el marcador
            marker.on('dragend', function(e) {
                var position = marker.getLatLng();
                $('input[name="latitud"]').val(position.lat.toFixed(6));
                $('input[name="longitud"]').val(position.lng.toFixed(6));
            });

            // Actualizar marcador al hacer clic en el mapa
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                $('input[name="latitud"]').val(e.latlng.lat.toFixed(6));
                $('input[name="longitud"]').val(e.latlng.lng.toFixed(6));
            });

            // IMPORTANTE: Corregir tamaño del mapa al abrir el acordeón
            $('#collapseDomicilio').on('shown.bs.collapse', function() {
                map.invalidateSize();
            });
        });
    </script>
@stop
