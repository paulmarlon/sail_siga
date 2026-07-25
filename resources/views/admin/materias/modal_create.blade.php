{{-- resources/views/admin/materias/modal_create.blade.php --}}
<div class="modal fade" id="ModalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.materias.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nueva Materia</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Sigla (*)</label>
                            <input type="text" name="sigla" class="form-control form-control-sm"
                                value="{{ old('sigla') }}" required>
                        </div>
                        <div class="col-md-8 form-group">
                            <label>Nombre (*)</label>
                            <input type="text" name="nombre" class="form-control form-control-sm"
                                value="{{ old('nombre') }}" required>
                        </div>
                    </div>
                    {{-- NUEVO: Campo de Descripción --}}
                    <div class="form-group">
                        <label>Descripción (*)</label>
                        <input type="text" name="descripcion" class="form-control form-control-sm"
                            value="{{ old('descripcion') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Horas Académicas (*)</label>
                            <input type="number" name="horas_academicas" class="form-control form-control-sm"
                                value="{{ old('horas_academicas') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Tipo Materia (*)</label>
                            <select name="tipo_materia" class="form-control form-control-sm" required>
                                <option value="Teorica">Teórica</option>
                                <option value="Practica">Práctica</option>
                                <option value="Teorica-Practica">Teórica-Práctica</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Estado (*)</label>
                        <select name="estado_id" class="form-control form-control-sm" required>
                            @foreach ($estados as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="es_comun" class="form-check-input" id="es_comun_create">
                        <label class="form-check-label" for="es_comun_create">Es materia común</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary btn-sm">Guardar Materia</button>
                </div>
            </form>
        </div>
    </div>
</div>
