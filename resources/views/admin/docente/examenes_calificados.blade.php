@extends('adminlte::page')

@section('title', 'Exámenes Calificados - Docente')

@section('content_header')
    <div class="card card-outline card-purple shadow-sm mb-2">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                    <i class="fas fa-clipboard-check text-purple mr-1"></i> Resumen de Exámenes Calificados
                </h4>
                <span class="text-muted" style="font-size: 0.85rem;">
                    Materia: <strong>{{ $programacion->ofertaAcademica->pensum->materia->nombre ?? '' }}</strong> |
                    Instancia: <span class="badge badge-purple"
                        style="background-color: #6f42c1; color: #fff;">{{ $programacion->instancia }}</span>
                </span>
            </div>
            <div>
                <a href="javascript:history.back()" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                <button onclick="window.print();" class="btn btn-dark btn-xs font-weight-bold shadow-sm">
                    <i class="fas fa-print mr-1"></i> Imprimir Reporte
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">

            @php
                $totalCalificados = $folios->whereNotNull('nota')->count();
                $aprobados = $folios->filter(fn($f) => $f->nota >= 50.5)->count();
                $aplazados = $folios->filter(fn($f) => $f->nota !== null && $f->nota < 50.5 && $f->nota >= 1)->count();
            @endphp

            {{-- Tarjetas de Estadísticas Rápidas --}}
            <div class="row text-center mb-3">
                <div class="col-md-4">
                    <div class="info-box shadow-sm mb-0 py-1">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted font-weight-bold">Total Calificados</span>
                            <span class="info-box-number text-purple">{{ $totalCalificados }} /
                                {{ $folios->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box shadow-sm mb-0 py-1">
                        <div class="info-box-content">
                            <span class="info-box-text text-success font-weight-bold">Aprobados (≥ 50.5)</span>
                            <span class="info-box-number text-success">{{ $aprobados }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box shadow-sm mb-0 py-1">
                        <div class="info-box-content">
                            <span class="info-box-text text-danger font-weight-bold">Aplazados (< 50.5)</span>
                                    <span class="info-box-number text-danger">{{ $aplazados }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-purple shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 65vh;">
                        <table class="table table-bordered table-striped table-sm m-0 align-middle"
                            style="font-size: 0.88rem;">
                            <thead class="bg-light text-center sticky-top">
                                <tr>
                                    <th width="50px">N°</th>
                                    <th width="110px">R.U.</th>
                                    <th class="text-left px-3">APELLIDOS Y NOMBRES DEL ESTUDIANTE</th>
                                    <th width="130px">CÓDIGO FOLIO</th>
                                    <th width="110px">NOTA FINAL</th>
                                    <th width="130px">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($folios as $index => $folio)
                                    <tr>
                                        <td class="text-center font-weight-bold bg-light">{{ $index + 1 }}</td>
                                        <td class="text-center font-weight-bold text-muted">
                                            {{ $folio->estudiante->registro_universitario ?? 'S/R' }}
                                        </td>
                                        <td class="px-3 font-weight-bold text-dark">
                                            @if ($folio->estudiante && $folio->estudiante->persona)
                                                {{ trim(($folio->estudiante->persona->ap_paterno ?? '') . ' ' . ($folio->estudiante->persona->ap_materno ?? '')) }},
                                                {{ $folio->estudiante->persona->nombres ?? '' }}
                                            @else
                                                <span class="text-muted font-italic">No asignado / Vacío</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-weight-bold text-uppercase">{{ $folio->codigo_folio }}
                                        </td>
                                        <td
                                            class="text-center font-weight-bold {{ $folio->nota < 50.5 && $folio->nota !== null ? 'text-danger' : 'text-dark' }}">
                                            {{ $folio->nota !== null ? $folio->nota : '-' }}
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge
                                                @if ($folio->estado_folio === 'Calificado') badge-success
                                                @elseif($folio->estado_folio === 'Foliado_Y_Separado') badge-info
                                                @else badge-secondary @endif">
                                                {{ $folio->estado_folio ?? 'Pendiente' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No existen registros de
                                            folios.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
