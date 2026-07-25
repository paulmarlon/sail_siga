{{-- resources/views/admin/materias/modal_edit.blade.php --}}
<div class="modal fade" id="ModalUpdate{{ $materia->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.materias.update', $materia->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Editar Materia: {{ $materia->sigla }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Sigla (*)</label>
                            <input type="text" name="sigla" class="form-control form-control-sm"
                                value="{{ old('sigla', $materia->sigla) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Horas Académicas (*)</label>
                            <input type="number" name="horas_academicas" class="form-control form-control-sm"
                                value="{{ old('horas_academicas', $materia->horas_academicas) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nombre (*)</label>
                        <input type="text" name="nombre" class="form-control form-control-sm"
                            value="{{ old('nombre', $materia->nombre) }}" required>
                    </div>

                    {{-- NUEVO: Campo de Descripción --}}
                    <div class="form-group">
                        <label>Descripción (*)</label>
                        <input type="text" name="descripcion" class="form-control form-control-sm"
                            value="{{ old('descripcion', $materia->descripcion) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tipo (*)</label>
                            <select name="tipo_materia" class="form-control form-control-sm" required>
                                <option value="Teorica"
                                    {{ old('tipo_materia', $materia->tipo_materia) == 'Teorica' ? 'selected' : '' }}>
                                    Teórica</option>
                                <option value="Practica"
                                    {{ old('tipo_materia', $materia->tipo_materia) == 'Practica' ? 'selected' : '' }}>
                                    Práctica</option>
                                <option value="Teorica-Practica"
                                    {{ old('tipo_materia', $materia->tipo_materia) == 'Teorica-Practica' ? 'selected' : '' }}>
                                    Teórica-Práctica</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Estado (*)</label>
                            <select name="estado_id" class="form-control form-control-sm" required>
                                @foreach ($estados as $e)
                                    <option value="{{ $e->id }}"
                                        {{ old('estado_id', $materia->estado_id) == $e->id ? 'selected' : '' }}>
                                        {{ $e->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="es_comun" class="form-check-input"
                            id="checkComun{{ $materia->id }}"
                            {{ old('es_comun', $materia->es_comun) ? 'checked' : '' }}>
                        <label class="form-check-label" for="checkComun{{ $materia->id }}">Es materia común</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success btn-sm">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
