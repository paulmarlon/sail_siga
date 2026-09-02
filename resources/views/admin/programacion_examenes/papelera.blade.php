@extends('adminlte::page')

@section('title', 'Papelera de Programación de Exámenes')

@section('css')
    <style>
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            border-radius: 0.25rem;
            margin-bottom: 0;
        }

        .pagination li {
            margin: 0 2px;
        }

        .pagination .page-link {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
            line-height: 1.25;
            color: #007bff;
            background-color: #fff;
            border: 1px solid #dee2e6;
        }

        .pagination .active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .pagination .disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-trash-alt text-danger mr-2"></i> Papelera de Exámenes Programados</h1>
            <p class="text-muted mb-0">Listado de registros eliminados por instancia que pueden ser restaurados.</p>
        </div>
        <div>
            <a href="{{ route('admin.programacion-examenes.index') }}"
                class="btn btn-secondary btn-sm shadow-sm font-weight-bold">
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

    <!-- FORMULARIO PARA RESTAURACIÓN MASIVA -->
    <form action="{{ route('admin.programacion-examenes.restaurar-masivo') }}" method="POST" id="form-papelera-masiva">
        @csrf

        <div class="card card-danger card-outline shadow-sm">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="seleccionar-todos-papelera">
                    <label class="custom-control-label small font-weight-bold text-dark cursor-pointer"
                        for="seleccionar-todos-papelera">Seleccionar Todos los Visibles</label>
                </div>
                <div>
                    <button type="submit" id="btn-restaurar-lote" class="btn btn-success btn-xs font-weight-bold shadow-sm"
                        disabled>
                        <i class="fas fa-trash-restore mr-1"></i> Restaurar Seleccionados (<span
                            id="contador-papelera">0</span>)
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <table class="table table-striped table-hover text-nowrap mb-0">
                    <thead class="thead-dark" style="font-size: 0.8rem;">
                        <tr>
                            <th class="text-center" style="width: 35px;"><i class="fas fa-check-square"></i></th>
                            <th class="text-center" style="width: 50px;">ID</th>
                            <th>Materia / Sigla</th>
                            <th>Instancia</th>
                            <th>Fecha Programada</th>
                            <th>Responsable</th>
                            <th>Fecha de Eliminación</th>
                            <th class="text-center" style="width: 100px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.8rem;">
                        @forelse ($eliminados as $item)
                            @php
                                $personaResp = $item->responsable->persona ?? null;
                                $nombreResponsable = $personaResp
                                    ? trim(
                                        $personaResp->nombres .
                                            ' ' .
                                            $personaResp->ap_paterno .
                                            ' ' .
                                            $personaResp->ap_materno,
                                    )
                                    : 'No asignado';
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="ids[]" value="{{ $item->id }}"
                                            id="papelera_chk_{{ $item->id }}"
                                            class="custom-control-input papelera-checkbox">
                                        <label class="custom-control-label" for="papelera_chk_{{ $item->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center font-weight-bold">{{ $item->id }}</td>
                                <td>
                                    {{ $item->ofertaAcademica->pensum->materia->nombre ?? 'S/N' }}
                                    <small
                                        class="text-muted">({{ $item->ofertaAcademica->pensum->materia->sigla ?? 'S/S' }})</small>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $item->instancia }}</span>
                                </td>
                                <td>{{ $item->fecha_programada }}</td>
                                <td>{{ $nombreResponsable }}</td>
                                <td class="text-muted">{{ $item->deleted_at }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.programacion-examenes.restaurar', $item->id) }}"
                                        class="btn btn-success btn-xs px-2 font-weight-bold"
                                        onclick="event.preventDefault(); document.getElementById('restaurar-form-{{ $item->id }}').submit();"
                                        title="Restaurar registro">
                                        <i class="fas fa-trash-restore mr-1"></i> Restaurar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                                    <p class="mb-0">La papelera se encuentra vacía.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix py-2">
                <div class="float-right">
                    {{ $eliminados->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </form>

    <!-- Formularios individuales ocultos para restaurar uno a uno -->
    @foreach ($eliminados as $item)
        <form id="restaurar-form-{{ $item->id }}"
            action="{{ route('admin.programacion-examenes.restaurar', $item->id) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endforeach
@stop

@section('js')
    <script>
        $(function() {
            function actualizarContadorPapelera() {
                var totalChecked = $('.papelera-checkbox:checked').length;
                $('#contador-papelera').text(totalChecked);

                if (totalChecked > 0) {
                    $('#btn-restaurar-lote').prop('disabled', false);
                } else {
                    $('#btn-restaurar-lote').prop('disabled', true);
                }
            }

            $(document).on('change', '.papelera-checkbox', function() {
                actualizarContadorPapelera();
            });

            $('#seleccionar-todos-papelera').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.papelera-checkbox').prop('checked', isChecked);
                actualizarContadorPapelera();
            });
        });
    </script>
@stop
