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
             MODO MASIVO: MÚLTIPLES OFERTAS SELECCIONADAS
             ========================================================= --}}
        <form action="{{ route('admin.programacion-examenes.store') }}" method="POST" id="form-cronograma-masivo">
            @csrf
            <div class="card card-success card-outline shadow mb-3">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title text-bold text-success m-0">
                        <i class="fas fa-tasks mr-1"></i> Programación Masiva de Lote ({{ count($listaOfertas) }} materias
                        seleccionadas)
                    </h3>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-sm mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 22%;">Materia / Curso</th>
                                    <th style="width: 12%;">Instancia</th>
                                    <th style="width: 15%;">Fecha Programada</th>
                                    <th style="width: 12%;">Modalidad</th>
                                    <th style="width: 22%;">Responsable / Docente</th>
                                    <th style="width: 17%;">Caso Extraordinario / Ref.</th>
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
                                        <td>
                                            <span
                                                class="font-weight-bold text-dark">{{ $oferta->pensum->materia->nombre ?? 'S/N' }}</span><br>
                                            <small class="text-muted">{{ $oferta->pensum->grado->nombre ?? '' }} -
                                                {{ $oferta->paralelo->nombre ?? '' }}
                                                ({{ $oferta->turno->nombre ?? '' }})
                                            </small>
                                            <input type="hidden" name="programaciones[{{ $index }}][oferta_id]"
                                                value="{{ $oferta->id }}">
                                            <input type="hidden" name="programaciones[{{ $index }}][materia_id]"
                                                value="{{ $oferta->pensum->materia_id ?? '' }}">
                                        </td>
                                        <td>
                                            <select name="programaciones[{{ $index }}][instancia]"
                                                class="form-control form-control-sm" required>
                                                <option value="P1">Primer Parcial (P1)</option>
                                                <option value="P2">Segundo Parcial (P2)</option>
                                                <option value="EF">Examen Final (EF)</option>
                                                <option value="2T">Segunda Instancia (2T)</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="date"
                                                name="programaciones[{{ $index }}][fecha_programada]"
                                                class="form-control form-control-sm" required min="{{ date('Y-m-d') }}">
                                        </td>
                                        <td>
                                            <select name="programaciones[{{ $index }}][modalidad]"
                                                class="form-control form-control-sm">
                                                <option value="directa" selected>Directa</option>
                                                <option value="a_ciegas">A Ciegas</option>
                                                <option value="dictada">Dictada</option>
                                            </select>
                                            <input type="hidden"
                                                name="programaciones[{{ $index }}][tipo_contenido]" value="Mixto">
                                            <input type="hidden" name="programaciones[{{ $index }}][tipo_proceso]"
                                                value="Ordinario">
                                        </td>
                                        <td>
                                            <select name="programaciones[{{ $index }}][responsable_id]"
                                                class="form-control form-control-sm select2-responsable" required>
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
                                        <td>
                                            <input type="text" name="programaciones[{{ $index }}][observaciones]"
                                                class="form-control form-control-sm" placeholder="Ej: Res. Adm. 123">
                                            <small class="text-muted" style="font-size: 10px;">Llenar solo si es
                                                Resolución</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-right">
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

            {{-- COLUMNA DEL FORMULARIO --}}
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
                                    {{-- Instancia de Evaluación --}}
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

                                    {{-- Fecha Programada --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha Programada</label>
                                            <input type="date" name="fecha_programada" class="form-control" required
                                                min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>

                                    {{-- Modalidad --}}
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Modalidad de Examen</label>
                                            <select name="modalidad" class="form-control" required>
                                                <option value="directa" selected>Directa (Ingreso libre de notas)</option>
                                                <option value="a_ciegas">A Ciegas (Con folios/codificado)</option>
                                                <option value="dictada">Dictada</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Tipo de Proceso --}}
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

                                    {{-- Responsable de Evaluación --}}
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

                                    {{-- Bloqueado Switch --}}
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

                                    {{-- Observaciones Legales / Justificación --}}
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
            // Inicialización de Select2 para todos los selectores de docentes
            $('.select2-responsable').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Seleccione o busque un docente --',
                allowClear: true
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
