@extends('adminlte::page')

@section('title', 'Edición Masiva de Exámenes')

@section('css')
    <style>
        .card-scroll {
            height: 75vh;
            display: flex;
            flex-direction: column;
        }

        .card-body-scroll {
            flex: 1;
            overflow-y: auto;
        }

        #tabla-edicion-masiva th,
        #tabla-edicion-masiva td {
            padding: 0.35rem 0.4rem !important;
            vertical-align: middle !important;
            font-size: 0.78rem;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-edit text-info mr-2"></i> Edición y Asignación Masiva de Exámenes</h1>
            <p class="text-muted mb-0">Modifique o configure en lote las instancias evaluativas de las ofertas académicas
                seleccionadas.</p>
        </div>
        <div>
            <a href="{{ route('admin.programacion-examenes.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="icon fas fa-ban"></i> Por favor corrija los errores marcados en el formulario antes de guardar.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- FORMULARIO GENERAL DE EDICIÓN / ACTUALIZACIÓN MASIVA -->
    <form action="{{ route('admin.programacion-examenes.update-masivo') }}" method="POST" id="form-edicion-masiva">
        @csrf
        @method('PUT')

        <div class="card card-info card-outline card-scroll shadow-sm mb-0">
            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title text-dark font-weight-bold mb-0" style="font-size: 0.85rem;">
                    <i class="fas fa-tasks mr-1 text-info"></i> Lote Seleccionado para Edición / Creación
                </h6>
                <span class="badge badge-info px-2 py-1" style="font-size: 0.75rem;">
                    Total Registros: {{ count($listaOfertas) }}
                </span>
            </div>

            <div class="card-body p-2 card-body-scroll table-responsive">
                <table id="tabla-edicion-masiva"
                    class="table table-bordered table-striped table-hover text-nowrap w-100 mb-0">
                    <thead class="thead-dark text-center" style="font-size: 0.75rem;">
                        <tr>
                            <th style="width: 22%;">Materia / Carrera / Turno</th>
                            <th style="width: 12%;">Instancia</th>
                            <th style="width: 12%;">Modalidad</th>
                            <th style="width: 11%;">Tipo Proceso</th>
                            <th style="width: 15%;">Fecha Programada</th>
                            <th style="width: 16%;">Responsable</th>
                            <th style="width: 12%;">Observaciones Legales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($listaOfertas) && count($listaOfertas) > 0)
                            @foreach ($listaOfertas as $index => $oferta)
                                @php
                                    // Verificamos si la oferta ya tiene un examen asociado (tomamos el primero o permitimos evaluar por filas)
                                    $examenExistente = $oferta->programacionesExamen->first();
                                @endphp
                                <tr>
                                    <!-- ID oculto del examen existente (indispensable para evitar duplicar registros) -->
                                    <input type="hidden" name="programaciones[{{ $index }}][programacion_id]"
                                        value="{{ optional($examenExistente)->id }}">

                                    <!-- ID oculto fundamental para relacionar el array en el request -->
                                    <input type="hidden" name="programaciones[{{ $index }}][oferta_id]"
                                        value="{{ $oferta->id }}">

                                    <!-- Columna 1: Datos de la Oferta Académica -->
                                    <td>
                                        <span
                                            class="font-weight-bold text-dark">{{ $oferta->pensum->materia->nombre ?? 'S/N' }}</span><br>
                                        <small class="text-muted">
                                            {{ $oferta->pensum->carrera->nombre ?? 'S/C' }} |
                                            <span
                                                class="badge badge-light border">{{ $oferta->turno->nombre ?? 'S/T' }}</span>
                                            <span
                                                class="badge badge-light border">{{ $oferta->paralelo->nombre ?? 'S/P' }}</span>
                                        </small>
                                    </td>

                                    <!-- Columna 2: Instancia -->
                                    <td class="text-center">
                                        <select name="programaciones[{{ $index }}][instancia]"
                                            class="form-control form-control-sm" style="font-size: 0.75rem;" required>
                                            <option value="P1"
                                                {{ optional($examenExistente)->instancia == 'P1' ? 'selected' : '' }}>
                                                Primer Parcial</option>
                                            <option value="P2"
                                                {{ optional($examenExistente)->instancia == 'P2' ? 'selected' : '' }}>
                                                Segundo Parcial</option>
                                            <option value="EF"
                                                {{ optional($examenExistente)->instancia == 'EF' ? 'selected' : '' }}>
                                                Examen Final</option>
                                            <option value="2T"
                                                {{ optional($examenExistente)->instancia == '2T' ? 'selected' : '' }}>
                                                Segunda Instancia</option>
                                        </select>
                                    </td>

                                    <!-- Columna 3: Modalidad -->
                                    <td class="text-center">
                                        <select name="programaciones[{{ $index }}][modalidad]"
                                            class="form-control form-control-sm" style="font-size: 0.75rem;">
                                            <option value="directa"
                                                {{ optional($examenExistente)->modalidad == 'directa' ? 'selected' : '' }}>
                                                Directa</option>
                                            <option value="a_ciegas"
                                                {{ optional($examenExistente)->modalidad == 'a_ciegas' ? 'selected' : '' }}>
                                                A ciegas</option>
                                            <option value="dictada"
                                                {{ optional($examenExistente)->modalidad == 'dictada' ? 'selected' : '' }}>
                                                Dictada</option>
                                        </select>
                                    </td>

                                    <!-- Columna 4: Tipo de Proceso -->
                                    <td class="text-center">
                                        <select name="programaciones[{{ $index }}][tipo_proceso]"
                                            class="form-control form-control-sm" style="font-size: 0.75rem;">
                                            <option value="Ordinario"
                                                {{ optional($examenExistente)->tipo_proceso == 'Ordinario' ? 'selected' : '' }}>
                                                Ordinario</option>
                                            <option value="Recuperatorio"
                                                {{ optional($examenExistente)->tipo_proceso == 'Recuperatorio' ? 'selected' : '' }}>
                                                Recuperatorio</option>
                                            <option value="Extraordinario"
                                                {{ optional($examenExistente)->tipo_proceso == 'Extraordinario' ? 'selected' : '' }}>
                                                Extraordinario</option>
                                        </select>
                                    </td>

                                    <!-- Columna 5: Fecha Programada -->
                                    <td>
                                        <input type="date" name="programaciones[{{ $index }}][fecha_programada]"
                                            class="form-control form-control-sm" style="font-size: 0.75rem;"
                                            value="{{ optional($examenExistente)->fecha_programada ? \Carbon\Carbon::parse($examenExistente->fecha_programada)->format('Y-m-d') : '' }}"
                                            required>
                                    </td>

                                    <!-- Columna 6: Responsable -->
                                    <td>
                                        <select name="programaciones[{{ $index }}][responsable_id]"
                                            class="form-control form-control-sm select2" style="font-size: 0.75rem;"
                                            required>
                                            <option value="">-- Seleccionar Responsable --</option>
                                            @foreach ($listaPersonal as $personal)
                                                @if ($personal->persona)
                                                    <option value="{{ $personal->id }}"
                                                        {{ optional($examenExistente)->responsable_id == $personal->id ? 'selected' : '' }}>
                                                        {{ $personal->persona->ap_paterno }}
                                                        {{ $personal->persona->ap_materno }}
                                                        {{ $personal->persona->nombres }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>

                                    <!-- Columna 7: Observaciones Legales -->
                                    <td>
                                        <input type="text" name="programaciones[{{ $index }}][observaciones]"
                                            class="form-control form-control-sm" style="font-size: 0.75rem;"
                                            value="{{ optional($examenExistente)->observaciones_legales ?? '' }}"
                                            placeholder="Opcional...">
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay ofertas académicas disponibles para la edición masiva.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white text-right py-2 px-3">
                <a href="{{ route('admin.programacion-examenes.index') }}"
                    class="btn btn-default btn-sm mr-2 font-weight-bold">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-info btn-sm font-weight-bold px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Guardar Cambios en Lote
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        $(function() {
            // Inicialización de Select2 con compatibilidad Bootstrap 4 para los selectores de personal
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        });
    </script>
@stop
