@extends('adminlte::page')

@section('title', 'Papelera de Programación de Exámenes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-trash-alt text-danger mr-2"></i> Papelera de Exámenes Programados</h1>
            <p class="text-muted mb-0">Listado de registros eliminados que pueden ser restaurados.</p>
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

    <div class="card card-danger card-outline shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover text-nowrap mb-0">
                <thead class="thead-dark" style="font-size: 0.8rem;">
                    <tr>
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
                                <form action="{{ route('admin.programacion-examenes.restaurar', $item->id) }}"
                                    method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs px-2 font-weight-bold"
                                        title="Restaurar registro">
                                        <i class="fas fa-trash-restore mr-1"></i> Restaurar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">La papelera se encuentra vacía.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $eliminados->links() }}
        </div>
    </div>
@stop
