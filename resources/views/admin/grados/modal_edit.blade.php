<div class="modal fade" id="ModalUpdate{{ $grado->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.grados.update', $grado->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Editar Grado</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="{{ $grado->nombre }}" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Orden</label>
                            <input type="number" name="orden" value="{{ $grado->orden }}" class="form-control"
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ciclo</label>
                            <select name="ciclo" class="form-control">
                                <option value="1" {{ $grado->ciclo == 1 ? 'selected' : '' }}>Tronco Común</option>
                                <option value="2" {{ $grado->ciclo == 2 ? 'selected' : '' }}>Especialidad</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nivel</label>
                            <select name="nivel_id" class="form-control" required>
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel->id }}"
                                        {{ $grado->nivel_id == $nivel->id ? 'selected' : '' }}>{{ $nivel->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Estado</label>
                            <select name="estado_id" class="form-control" required>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->id }}"
                                        {{ $grado->estado_id == $estado->id ? 'selected' : '' }}>{{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>
