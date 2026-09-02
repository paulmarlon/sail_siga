@extends('adminlte::page')

@section('content_header')
    <h1><b>Programación de Evaluación</b></h1>
    <hr>
@stop

@section('content')
    @php
        // Verificamos si estamos en modo masivo (más de una oferta o array) o individual
        $esMasivo = count($listaOfertas) > 1;
        $ofertaUnica = !$esMasivo ? $listaOfertas->first() : null;

        // Recuperamos la instancia que eligió el usuario en el modal del index (Por defecto P1)
        $instanciaFiltro = request('instancia_filtro') ?? 'P1';

        // Fecha actual para valores por defecto
        $hoy = date('Y-m-d');

        // Datos para modo individual
        $docenteAsignado = $ofertaUnica ? $ofertaUnica->docenteActual : null;
        $materiaAsignada = $ofertaUnica && $ofertaUnica->pensum ? $ofertaUnica->pensum->materia : null;
        $examenesExistentesUnico = isset($examenesExistentes) ? $examenesExistentes : collect();
        $yaProgramadas = $examenesExistentesUnico->pluck('instancia')->toArray();

        $secuencia = ['P1', 'P2', 'EF', '2T'];
        $siguiente = collect($secuencia)->first(function ($item) use ($yaProgramadas) {
            return !in_array($item, $yaProgramadas);
        });
    @endphp

    @if ($esMasivo)
        {{-- =========================================================
             MODO MASIVO: MÚLTIPLES OFERTAS SELECCIONADAS (COMPACTO)
             ========================================================= --}}
        <form action="{{ route('admin.programacion-examenes.store') }}" method="POST" id="form-cronograma-masivo">
            @csrf
            <div class="card card-success card-outline shadow mb-3">
                <div class="card-header bg-white py-2">
                    <h3 class="card-title text-bold text-success m-0" style="font-size: 1.05rem;">
                        <i class="fas fa-tasks mr-1"></i> Programación Masiva de Lote ({{ count($listaOfertas) }} materias
                        seleccionadas)
                    </h3>
                </div>
                <div class="card-body p-1">
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-bordered table-striped text-xs mb-0">
                            <thead class="thead-dark text-center" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 22%; padding: 6px;">Materia / Curso</th>
                                    <th style="width: 12%; padding: 6px;">Instancia</th>
                                    <th style="width: 14%; padding: 6px;">Fecha Programada</th>
                                    <th style="width: 12%; padding: 6px;">Modalidad</th>
                                    <th style="width: 23%; padding: 6px;">Responsable / Docente</th>
                                    <th style="width: 17%; padding: 6px;">Caso Extraordinario / Ref.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listaOfertas as $index => $oferta)
                                    @php
                                        // Docente por defecto sugerido de la oferta
                                        $docSug =
                                            $oferta->docenteActual && $oferta->docenteActual->docente
                                                ? $oferta->docenteActual->docente_id
                                                : null;
                                    @endphp
                                    <tr>
                                        <td class="align-middle py-1 px-2">
                                            <span class="font-weight-bold text-dark" style="font-size: 0.8rem;">
                                                {{ $oferta->pensum->materia->nombre ?? 'S/N' }}
                                            </span><br>
                                            <span class="text-muted" style="font-size: 0.7rem;">
                                                {{ $oferta->pensum->grado->nombre ?? '' }} -
                                                {{ $oferta->paralelo->nombre ?? '' }}
                                                ({{ $oferta->turno->nombre ?? '' }})
                                            </span>
                                            <input type="hidden" name="programaciones[{{ $index }}][oferta_id]"
                                                value="{{ $oferta->id }}">
                                            <input type="hidden" name="programaciones[{{ $index }}][materia_id]"
                                                value="{{ $oferta->pensum->materia_id ?? '' }}">
                                        </td>
                                        <td class="align-middle py-1 px-2">
                                            <select name="programaciones[{{ $index }}][instancia]"
                                                class="form-control form-control-xs text-xs"
                                                style="height: calc(1.5em + 0.5rem + 2px); padding: 0.15rem 0.5rem;"
                                                required>
                                                <option value="P1" {{ $instanciaFiltro == 'P1' ? 'selected' : '' }}>
                                                    Primer Parcial (P1)</option>
                                                <option value="P2" {{ $instanciaFiltro == 'P2' ? 'selected' : '' }}>
                                                    Segundo Parcial (P2)</option>
                                                <option value="EF" {{ $instanciaFiltro == 'EF' ? 'selected' : '' }}>
                                                    Examen Final (EF)</option>
                                                <option value="2T" {{ $instanciaFiltro == '2T' ? 'selected' : '' }}>
                                                    Segunda Instancia (2T)</option>
                                            </select>
                                        </td>
                                        <td class="align-middle py-1 px-2">
                                            <input type="date"
                                                name="programaciones[{{ $index }}][fecha_programada]"
                                                class="form-control form-control-xs text-xs"
                                                style="height: calc(1.5em + 0.5rem + 2px); padding: 0.15rem 0.5rem;"
                                                required min="{{ $hoy }}" value="{{ $hoy }}">
                                        </td>
                                        <td class="align-middle py-1 px-2">
                                            <select name="programaciones[{{ $index }}][modalidad]"
                                                class="form-control form-control-xs text-xs select-modalidad font-weight-bold bg-success text-white"
                                                style="height: calc(1.5em + 0.5rem + 2px); padding: 0.15rem 0.5rem;"
                                                required>
                                                <option value="directa" class="bg-white text-dark" selected>Directa</option>
                                                <option value="a_ciegas" class="bg-white text-dark">A Ciegas</option>
                                                <option value="dictada" class="bg-white text-dark">Dictada</option>
                                            </select>
                                            <input type="hidden"
                                                name="programaciones[{{ $index }}][tipo_contenido]" value="Mixto">
                                            <input type="hidden" name="programaciones[{{ $index }}][tipo_proceso]"
                                                value="Ordinario">
                                        </td>
                                        <td class="align-middle py-1 px-2">
                                            <select name="programaciones[{{ $index }}][responsable_id]"
                                                class="form-control form-control-xs text-xs select2-responsable" required>
                                                <option value="">-- Seleccionar Docente --</option>
                                                @foreach ($listaPersonal as $p)
                                                    @if ($p->persona)
                                                        <option value="{{ $p->id }}"
                                                            {{ $docSug == $p->id ? 'selected' : '' }}>
                                                            {{ $p->persona->ap_paterno }} {{ $p->persona->ap_materno }}
                                                            {{ $p->persona->nombres }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="align-middle py-1 px-2">
                                            <input type="text" name="programaciones[{{ $index }}][observaciones]"
                                                class="form-control form-control-xs text-xs"
                                                style="height: calc(1.5em + 0.5rem + 2px); padding: 0.15rem 0.5rem;"
                                                placeholder="Ej: Res. Adm. 123">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-right py-2">
                    <a href="{{ route('admin.programacion-examenes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold px-4">
                        <i class="fas fa-save"></i> Guardar Programación Masiva
                    </button>
                </div>
            </div>
        </form>
    @else
        {{-- =========================================================
             MODO INDIVIDUAL (SIN CAMBIOS)
             ========================================================= --}}
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow">
                    <div class="card-header">
                        <h3 class="card-title text-bold">Datos de la Materia</h3>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @php
                                $fotoPath =
                                    $docenteAsignado && $docenteAsignado->docente && $docenteAsignado->docente->persona
                                        ? $docenteAsignado->docente->persona->foto_path
                                        : null;
                            @endphp
                            <img class="profile-user-img img-fluid img-circle shadow-sm"
                                src="{{ $fotoPath ? asset('storage/' . $fotoPath) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                                alt="Docente" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <h3 class="profile-username text-center text-primary text-bold">
                            {{ $materiaAsignada->nombre ?? 'Seleccione una Oferta' }}
                        </h3>
                        <p class="text-muted text-center">
                            @if ($docenteAsignado && $docenteAsignado->docente && $docenteAsignado->docente->persona)
                                {{ $docenteAsignado->docente->persona->ap_paterno ?? '' }}
                                {{ $docenteAsignado->docente->persona->ap_materno ?? '' }}
                                {{ $docenteAsignado->docente->persona->nombres ?? '' }}
                            @else
                                Sin docente asignado
                            @endif
                        </p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Curso / Paralelo</b> <a class="float-right text-primary text-bold">
                                    {{ $ofertaUnica && $ofertaUnica->pensum && $ofertaUnica->pensum->grado ? $ofertaUnica->pensum->grado->nombre : '' }}
                                    - {{ $ofertaUnica && $ofertaUnica->paralelo ? $ofertaUnica->paralelo->nombre : 'N/A' }}
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b>Instancias Listas</b>
                                <div class="float-right">
                                    @foreach ($yaProgramadas as $yp)
                                        <span class="badge badge-success">{{ $yp }}</span>
                                    @endforeach
                                    @if (empty($yaProgramadas))
                                        <span class="text-muted">Ninguna</span>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- CASO ESPECIAL / MODO RESOLUCIÓN --}}
                <div class="card card-warning card-outline collapsed-card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-gavel"></i> ¿Caso Especial?</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body text-sm">
                        Utilice esta opción solo si existe una <strong>Resolución Administrativa</strong>.
                        <button type="button" class="btn btn-xs btn-block btn-outline-warning mt-2"
                            onclick="habilitarModoResolucion()">Habilitar Modo Resolución</button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                @if ($ofertaUnica && $siguiente)
                    <form action="{{ route('admin.programacion-examenes.store') }}" method="POST" id="form-cronograma">
                        @csrf
                        <input type="hidden" name="oferta_id" value="{{ $ofertaUnica->id }}">
                        <input type="hidden" name="materia_id" value="{{ $materiaAsignada->id ?? '' }}">

                        <div class="card card-primary card-outline shadow">
                            <div class="card-header">
                                <h3 class="card-title text-bold" id="titulo-instancia">
                                    Programar: {{ $siguiente }}
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Instancia de Evaluación</label>
                                            @php
                                                $nombresInstancias = [
                                                    'P1' => 'Primer Parcial',
                                                    'P2' => 'Segundo Parcial',
                                                    'EF' => 'Examen Final',
                                                    '2T' => 'Segunda Instancia (2T)',
                                                ];
                                            @endphp
                                            <select name="instancia" id="select-instancia" class="form-control" required>
                                                @if ($siguiente)
                                                    <option value="{{ $siguiente }}" selected id="opt-siguiente">
                                                        {{ $nombresInstancias[$siguiente] ?? $siguiente }}
                                                    </option>
                                                @endif
                                                @foreach ($nombresInstancias as $val => $text)
                                                    @if ($val !== $siguiente)
                                                        <option value="{{ $val }}" class="d-none res-opt">
                                                            {{ $text }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha Programada</label>
                                            <input type="date" name="fecha_programada" class="form-control" required
                                                min="{{ $hoy }}" value="{{ $hoy }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Modalidad de Examen</label>
                                            <select name="modalidad" id="modalidad-individual"
                                                class="form-control font-weight-bold bg-success text-white" required>
                                                <option value="directa" class="bg-white text-dark" selected>Directa
                                                    (Ingreso libre de notas)</option>
                                                <option value="a_ciegas" class="bg-white text-dark">A Ciegas (Con
                                                    folios/codificado)</option>
                                                <option value="dictada" class="bg-white text-dark">Dictada</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo de Proceso</label>
                                            <select name="tipo_proceso" id="tipo_proceso" class="form-control" required>
                                                <option value="Ordinario" selected>Ordinario</option>
                                                <option value="Recuperatorio">Recuperatorio</option>
                                                <option value="Extraordinario">Extraordinario</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Responsable / Docente Asignado</label>
                                            <select name="responsable_id" class="form-control select2-responsable"
                                                required>
                                                <option value="">-- Seleccione o busque un docente --</option>
                                                @foreach ($listaPersonal as $p)
                                                    @if ($p->persona)
                                                        <option value="{{ $p->id }}"
                                                            {{ $docenteAsignado && $docenteAsignado->docente_id == $p->id ? 'selected' : '' }}>
                                                            {{ $p->persona->ap_paterno }} {{ $p->persona->ap_materno }}
                                                            {{ $p->persona->nombres }}
                                                            @if ($p->profesion)
                                                                ({{ $p->profesion }})
                                                            @endif
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12 d-flex align-items-center mb-3">
                                        <div class="custom-control custom-switch mt-2">
                                            <input type="checkbox" class="custom-control-input" id="bloqueadoSwitch"
                                                name="bloqueado" value="1">
                                            <label class="custom-control-label font-weight-normal text-secondary"
                                                for="bloqueadoSwitch">
                                                Bloquear modificaciones de notas
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12" id="div-observaciones">
                                        <div class="form-group">
                                            <label class="text-danger">Observaciones Legales / Justificación</label>
                                            <textarea name="observaciones_legales" class="form-control" rows="3"
                                                placeholder="Indique fundamentos legales si es un proceso extraordinario o por resolución..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="{{ route('admin.programacion-examenes.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary float-right">
                                    <i class="fas fa-save"></i> Confirmar Programación
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif(!$ofertaUnica)
                    <div class="alert alert-info shadow">
                        <h5><i class="icon fas fa-info"></i> Seleccione una Oferta Académica</h5>
                        Por favor, elija una oferta académica para comenzar con la programación de exámenes.
                    </div>
                @else
                    <div class="alert alert-success shadow">
                        <h5><i class="icon fas fa-check"></i> ¡Ciclo Ordinario Completo!</h5>
                        Todas las instancias ya han sido programadas para esta materia.
                    </div>
                    <a href="{{ route('admin.programacion-examenes.index') }}" class="btn btn-primary">Volver al
                        Panel</a>
                @endif
            </div>
        </div>
    @endif
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicialización de Select2 para todos los selectores de docentes (con adaptación compacta)
            $('.select2-responsable').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Seleccione o busque un docente --',
                allowClear: true
            });

            // Función genérica para cambiar colores según la modalidad seleccionada
            function actualizarColorModalidad(selectElement) {
                var val = $(selectElement).val();
                $(selectElement).removeClass('bg-success bg-indigo bg-warning text-white text-dark');

                if (val === 'directa') {
                    $(selectElement).addClass('bg-success text-white');
                } else if (val === 'a_ciegas') {
                    $(selectElement).addClass('bg-indigo text-white');
                } else if (val === 'dictada') {
                    $(selectElement).addClass('bg-warning text-dark');
                }
            }

            // Aplicar cambio de color dinámico en eventos de cambio
            $(document).on('change', '.select-modalidad, #modalidad-individual', function() {
                actualizarColorModalidad(this);
            });
        });

        function habilitarModoResolucion() {
            Swal.fire({
                title: '¿Activar Modo Resolución?',
                text: "Permitirá programar instancias fuera del orden académico y cambiar automáticamente el tipo de proceso.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, activar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#tipo_proceso').val('Extraordinario').trigger('change');
                    $('.res-opt').removeClass('d-none');
                    $('#titulo-instancia').html(
                        '<i class="fas fa-gavel text-warning"></i> Programación por Resolución / Extraordinaria'
                    );

                    // Hacer foco en el textarea de observaciones legales
                    $('textarea[name="observaciones_legales"]').focus();
                }
            })
        }
    </script>
@stop
