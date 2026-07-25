<div class="modal fade" id="ModalCreate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.carreras.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-2">
                    <h6 class="modal-title font-weight-bold">Nueva Carrera</h6>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body py-2">
                    <!-- Fila 1: Sigla y Resolución -->
                    <div class="form-row mb-2">
                        <div class="col-6"><label class="small mb-0">Sigla</label><input type="text" name="sigla"
                                class="form-control form-control-sm" required></div>
                        <div class="col-6"><label class="small mb-0">Resolución</label><input type="text"
                                name="resolucion" class="form-control form-control-sm"></div>
                    </div>

                    <!-- Nombre -->
                    <div class="form-group mb-2">
                        <label class="small mb-0">Nombre</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required>
                    </div>

                    <!-- Fila 2: Duración y Título -->
                    <div class="form-row mb-2">
                        <div class="col-6"><label class="small mb-0">Duración</label><input type="number"
                                name="duracion" class="form-control form-control-sm" required></div>
                        <div class="col-6"><label class="small mb-0">Título</label><input type="text" name="titulo"
                                class="form-control form-control-sm" required></div>
                    </div>

                    <!-- Fila 3: Nivel y Estado -->
                    <div class="form-row mb-2">
                        <div class="col-6">
                            <label class="small mb-0">Nivel</label>
                            <select name="nivel_id" class="form-control form-control-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach ($niveles as $n)
                                    <option value="{{ $n->id }}">{{ $n->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small mb-0">Estado</label>
                            <select name="estado_id" class="form-control form-control-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach ($estados as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Carrera Base y Checkbox -->
                    <div class="form-group mb-2">
                        <label class="small mb-0">Carrera Base (Opcional)</label>
                        <select name="carrera_base_id" class="form-control form-control-sm">
                            <option value="">Ninguna</option>
                            @foreach ($carrerasBase as $cb)
                                <option value="{{ $cb->id }}">{{ $cb->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" name="es_tronco_comun" id="create_comun" class="custom-control-input"
                            value="1">
                        <label class="custom-control-label small" for="create_comun">Es carrera de Tronco Común</label>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
