@extends('adminlte::page')

@section('content_header')
    <h1><b>Programación de Evaluación</b></h1>
    <hr class="mb-2">
@stop

@section('content')
    @php
        // Verificamos si estamos en modo masivo (más de una oferta o array) o individual
        $esMasivo = count($listaOfertas) > 1;
        $ofertaUnica = !$esMasivo ? $listaOfertas->first() : null;

        // Datos para modo individual
        $docenteAsignado = $ofertaUnica ? $ofertaUnica->docenteActual : null;
        $materiaAsignada = $ofertaUnica && $ofertaUnica->pensum ? $ofertaUnica->pensum->materia : null;
        $examenesExistentesUnico = isset($examenesExistentes) ? $examenesExistentes : collect();
        $yaProgramadas = $examenesExistentesUnico->pluck('instancia')->toArray();

        $secuencia = ['P1', 'P2', 'EF', '2T'];
        $siguiente = collect($secuencia)->first(function ($item) use ($yaProgramadas) {
            return !in_array($item, $yaProgramadas);
        });

        // Valores sugeridos que llegan desde el request (modal del index)
        $instanciaDefecto = $instanciaSugerida ?? 'P1';
        $fechaDefecto = isset($fechaSugerida) ? str_replace('T', ' ', $fechaSugerida) : date('Y-m-d');
        $fechaInputDefecto = date('Y-m-d', strtotime($fechaDefecto));
        $observacionesDefecto = $observacionesSugeridas ?? '';
    @endphp

    @if ($esMasivo)
        {{-- =========================================================
             MODO MASIVO: MÚLTIPLES OFERTAS SELECCIONADAS
             ========================================================= --}}
        <form action="{{ route('admin.programacion-examenes.store') }}" method="POST" id="form-cronograma-masivo">
            @csrf
            <div class="card card-success card-outline shadow mb-3">
                <div class="card-header bg-white py-2">
                    <h3 class="card-title text-bold text-success m-0" style="font-size: 0.95rem;">
                        <i class="fas fa-tasks mr-1"></i> Programación Masiva de Lote ({{ count($listaOfertas) }} materias
                        seleccionadas)
                    </h3>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-sm mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 22%;" class="py-1">Materia / Curso</th>
                                    <th style="width: 12%;" class="py-1">Instancia</th>
                                    <th style="width: 15%;" class="py-1">Fecha Programada</th>
                                    <th style="width: 13%;" class="py-1">Modalidad</th>
                                    <th style="width: 21%;" class="py-1">Responsable / Docente</th>
                                    <th style="width: 17%;" class="py-1">Caso Extraordinario / Ref.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listaOfertas as $index => $oferta)
                                    @php
                                        $docSug =
                                            $oferta->docenteActual && $oferta->docenteActual->docente
                                                ? $oferta->docenteActual->docente_id
                                                : null;
                                    @endphp
                                    <tr>
                                        <td class="align-middle py-1">
                                            <span
                                                class="font-weight-bold text-dark">{{ $oferta->pensum->materia->nombre ?? 'S/N' }}</span><br>
                                            <small class="text-muted"
                                                style="font-size: 11px;">{{ $oferta->pensum->grado->nombre ?? '' }} -
                                                {{ $oferta->paralelo->nombre ?? '' }} ({{ $oferta->turno->nombre ?? '' }})
                                            </small>
                                            <input type="hidden" name="programaciones[{{ $index }}][oferta_id]"
                                                value="{{ $oferta->id }}">
                                            <input type="hidden" name="programaciones[{{ $index }}][materia_id]"
                                                value="{{ $oferta->pensum->materia_id ?? '' }}">
                                        </td>
                                        <td class="align-middle py-1">
                                            <select name="programaciones[{{ $index }}][instancia]"
                                                class="form-control form-control-sm" required>
                                                <option value="P1" {{ $instanciaDefecto == 'P1' ? 'selected' : '' }}>
                                                    Primer Parcial (P1)</option>
                                                <option value="P2" {{ $instanciaDefecto == 'P2' ? 'selected' : '' }}>
                                                    Segundo Parcial (P2)</option>
                                                <option value="EF" {{ $instanciaDefecto == 'EF' ? 'selected' : '' }}>
                                                    Examen Final (EF)</option>
                                                <option value="2T" {{ $instanciaDefecto == '2T' ? 'selected' : '' }}>
                                                    Segunda Instancia (2T)</option>
                                            </select>
                                        </td>
                                        <td class="align-middle py-1">
                                            <input type="date"
                                                name="programaciones[{{ $index }}][fecha_programada]"
                                                class="form-control form-control-sm" required min="{{ date('Y-m-d') }}"
                                                value="{{ $fechaInputDefecto }}">
                                        </td>
                                        <td class="align-middle py-1">
                                            <select name="programaciones[{{ $index }}][modalidad]"
                                                class="form-control form-control-sm select-modalidad font-weight-bold text-white bg-success"
                                                onchange="actualizarColorSelect(this)" required>
                                                <option value="directa" class="bg-white text-dark" selected>Directa</option>
                                                <option value="a_ciegas" class="bg-white text-dark">A Ciegas</option>
                                                <option value="dictada" class="bg-white text-dark">Dictada</option>
                                            </select>
                                            <input type="hidden"
                                                name="programaciones[{{ $index }}][tipo_contenido]" value="Mixto">
                                            <input type="hidden" name="programaciones[{{ $index }}][tipo_proceso]"
                                                value="Ordinario">
                                        </td>
                                        <td class="align-middle py-1">
                                            <select name="programaciones[{{ $index }}][responsable_id]"
                                                class="form-control form-control-sm select2-responsable" required>
                                                <option value="">-- Seleccionar --</option>
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
                                        <td class="align-middle py-1">
                                            <input type="text" name="programaciones[{{ $index }}][observaciones]"
                                                class="form-control form-control-sm" placeholder="Ej: Res. Adm. 123"
                                                value="{{ $observacionesDefecto }}">
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
             MODO INDIVIDUAL
             ========================================================= --}}
        <div class="row">
            {{-- COLUMNA DE INFORMACIÓN CONTEXTUAL --}}
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow mb-2">
                    <div class="card-header py-2">
                        <h3 class="card-title text-bold m-0" style="font-size: 0.95rem;">Datos de la Materia</h3>
                    </div>
                    <div class="card-body box-profile py-3">
                        <div class="text-center">
                            @php
                                $fotoPath =
                                    $docenteAsignado && $docenteAsignado->docente && $docenteAsignado->docente->persona
                                        ? $docenteAsignado->docente->persona->foto_path
                                        : null;
                            @endphp
                            <img class="profile-user-img img-fluid img-circle shadow-sm"
                                src="{{ $fotoPath ? asset('storage/' . $fotoPath) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                                alt="Docente" style="width: 85px; height: 85px; object-fit: cover;">
                        </div>
                        <h3 class="profile-username text-center text-primary text-bold mt-2" style="font-size: 1.1rem;">
                            {{ $materiaAsignada->nombre ?? 'Seleccione una Oferta' }}
                        </h3>
                        <p class="text-muted text-center mb-2" style="font-size: 0.85rem;">
                            @if ($docenteAsignado && $docenteAsignado->docente && $docenteAsignado->docente->persona)
                                {{ $docenteAsignado->docente->persona->ap_paterno ?? '' }}
                                {{ $docenteAsignado->docente->persona->ap_materno ?? '' }}
                                {{ $docenteAsignado->docente->persona->nombres ?? '' }}
                            @else
                                Sin docente asignado
                            @endif
                        </p>

                        <ul class="list-group list-group-unbordered mb-0 text-sm">
                            <li class="list-group-item py-1">
                                <b>Curso / Paralelo</b> <a class="float-right text-primary text-bold">
                                    {{ $ofertaUnica && $ofertaUnica->pensum && $ofertaUnica->pensum->grado ? $ofertaUnica->pensum->grado->nombre : '' }}
                                    - {{ $ofertaUnica && $ofertaUnica->paralelo ? $ofertaUnica->paralelo->nombre : 'N/A' }}
                                </a>
                            </li>
                            <li class="list-group-item py-1">
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
                    <div class="card-header py-2">
                        <h3 class="card-title m-0" style="font-size: 0.85rem;"><i class="fas fa-gavel"></i> ¿Caso
                            Especial?</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool btn-xs" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body text-sm py-2">
                        Utilice esta opción si existe una <strong>Resolución Administrativa</strong>.
                        <button type="button" class="btn btn-xs btn-block btn-outline-warning mt-2 font-weight-bold"
                            onclick="habilitarModoResolucion()">Habilitar Modo Resolución</button>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DEL FORMULARIO --}}
            <div class="col-md-8">
                @if ($ofertaUnica && $siguiente)
                    <form action="{{ route('admin.programacion-examenes.store') }}" method="POST" id="form-cronograma">
                        @csrf
                        <input type="hidden" name="oferta_id" value="{{ $ofertaUnica->id }}">
                        <input type="hidden" name="materia_id" value="{{ $materiaAsignada->id ?? '' }}">

                        <div class="card card-primary card-outline shadow mb-2">
                            <div class="card-header py-2">
                                <h3 class="card-title text-bold m-0" id="titulo-instancia" style="font-size: 0.95rem;">
                                    Programar: {{ $instanciaDefecto ?? $siguiente }}
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    {{-- Instancia de Evaluación --}}
                                    <div class="col-md-6 mb-2">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold mb-1">Instancia de Evaluación</label>
                                            <select name="instancia" id="select-instancia"
                                                class="form-control form-control-sm" required>
                                                <option value="P1" {{ $instanciaDefecto == 'P1' ? 'selected' : '' }}>
                                                    Primer Parcial</option>
                                                <option value="P2" {{ $instanciaDefecto == 'P2' ? 'selected' : '' }}>
                                                    Segundo Parcial</option>
                                                <option value="EF" {{ $instanciaDefecto == 'EF' ? 'selected' : '' }}>
                                                    Examen Final</option>
                                                <option value="2T" {{ $instanciaDefecto == '2T' ? 'selected' : '' }}>
                                                    Segunda Instancia (2T)</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Fecha Programada --}}
                                    <div class="col-md-6 mb-2">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold mb-1">Fecha Programada</label>
                                            <input type="date" name="fecha_programada"
                                                class="form-control form-control-sm" required min="{{ date('Y-m-d') }}"
                                                value="{{ $fechaInputDefecto }}">
                                        </div>
                                    </div>

                                    {{-- Modalidad --}}
                                    <div class="col-md-6 mb-2">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold mb-1">Modalidad de Examen</label>
                                            <select name="modalidad"
                                                class="form-control form-control-sm select-modalidad font-weight-bold text-white bg-success"
                                                onchange="actualizarColorSelect(this)" required>
                                                <option value="directa" class="bg-white text-dark" selected>Directa
                                                    (Ingreso libre de notas)</option>
                                                <option value="a_ciegas" class="bg-white text-dark">A Ciegas (Con
                                                    folios/codificado)</option>
                                                <option value="dictada" class="bg-white text-dark">Dictada</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Tipo de Proceso --}}
                                    <div class="col-md-6 mb-2">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold mb-1">Tipo de Proceso</label>
                                            <select name="tipo_proceso" id="tipo_proceso"
                                                class="form-control form-control-sm" required>
                                                <option value="Ordinario" selected>Ordinario</option>
                                                <option value="Recuperatorio">Recuperatorio</option>
                                                <option value="Extraordinario">Extraordinario</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Responsable de Evaluación --}}
                                    <div class="col-12 mb-2">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold mb-1">Responsable / Docente
                                                Asignado</label>
                                            <select name="responsable_id"
                                                class="form-control form-control-sm select2-responsable" required>
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

                                    {{-- Bloqueado Switch --}}
                                    <div class="col-md-12 mb-1">
                                        <div class="custom-control custom-switch mt-1">
                                            <input type="checkbox" class="custom-control-input" id="bloqueadoSwitch"
                                                name="bloqueado" value="1">
                                            <label class="custom-control-label small text-secondary"
                                                for="bloqueadoSwitch">
                                                Bloquear modificaciones de notas
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Observaciones Legales / Justificación --}}
                                    <div class="col-12 mb-1">
                                        <div class="form-group mb-0">
                                            <label class="small font-weight-bold text-danger mb-1">Observaciones Legales /
                                                Justificación</label>
                                            <textarea name="observaciones_legales" class="form-control form-control-sm" rows="2"
                                                placeholder="Indique fundamentos legales...">{{ $observacionesDefecto }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white py-2 text-right">
                                <a href="{{ route('admin.programacion-examenes.index') }}"
                                    class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="fas fa-save"></i> Confirmar Programación
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif(!$ofertaUnica)
                    <div class="alert alert-info shadow p-3">
                        <h5 class="mb-1" style="font-size: 1rem;"><i class="icon fas fa-info"></i> Seleccione una
                            Oferta Académica</h5>
                        Por favor, elija una oferta académica para comenzar con la programación de exámenes.
                    </div>
                @else
                    <div class="alert alert-success shadow p-3">
                        <h5 class="mb-1" style="font-size: 1rem;"><i class="icon fas fa-check"></i> ¡Ciclo Ordinario
                            Completo!</h5>
                        Todas las instancias ya han sido programadas para esta materia.
                    </div>
                    <a href="{{ route('admin.programacion-examenes.index') }}" class="btn btn-primary btn-sm">Volver al
                        Panel</a>
                @endif
            </div>
        </div>
    @endif
@stop

@section('js')
    <script>
        // Función para cambiar el color del select según la modalidad seleccionada
        function actualizarColorSelect(selectElement) {
            const val = $(selectElement).val();
            $(selectElement).removeClass('bg-success bg-secondary bg-warning bg-purple text-white text-dark');

            // Aplicar clases de fondo personalizadas de AdminLTE/Bootstrap
            if (val === 'directa') {
                $(selectElement).addClass('bg-success text-white');
            } else if (val === 'a_ciegas') {
                $(selectElement).css('background-color', '#6f42c1').addClass('text-white'); // Color morado/purple
            } else if (val === 'dictada') {
                $(selectElement).addClass('bg-warning text-dark');
            }
        }

        $(document).ready(function() {
            // Inicialización de Select2
            $('.select2-responsable').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Seleccione o busque --',
                allowClear: true
            });

            // Inicializar colores de los selects de modalidad ya renderizados
            $('.select-modalidad').each(function() {
                actualizarColorSelect(this);
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
                    $('textarea[name="observaciones_legales"]').focus();
                }
            })
        }
    </script>
@stop
