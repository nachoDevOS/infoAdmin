@extends('layouts.app')

@section('title', 'Grupos de PCs')

@push('styles')
<style>
.grupo-badge { font-size:.72rem; padding:.25rem .6rem; border-radius:20px; font-weight:600; }
.pc-card {
    border:1px solid #E2E8F0; border-radius:6px; padding:.6rem .9rem;
    display:flex; align-items:center; justify-content:space-between;
    background:#fff; margin-bottom:6px;
}
.pc-card .pc-info { display:flex; align-items:center; gap:8px; }
.pc-dot { width:8px; height:8px; border-radius:50%; background:#26B99A; flex-shrink:0; }
.pc-dot.offline { background:#CBD5E1; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h4 class="page-title"><i class="fas fa-layer-group me-2"></i>Grupos de PCs</h4>
        <div class="page-subtitle">Organiza las PCs en departamentos para envíos segmentados.</div>
    </div>
    <button class="btn btn-mp btn-sm" data-bs-toggle="modal" data-bs-target="#modal-grupo">
        <i class="fas fa-plus me-1"></i>Nuevo grupo
    </button>
</div>

<div class="row g-4">
    {{-- Lista de grupos --}}
    <div class="col-lg-5">
        <div class="card card-mp">
            <div class="card-header bg-white px-4 py-3">
                <span class="section-title-mp"><i class="fas fa-layer-group"></i>Grupos definidos</span>
            </div>
            <div class="card-body p-0">
                @forelse($grupos as $g)
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <div>
                        <div class="fw-semibold" style="font-size:.9rem;">{{ $g->nombre }}</div>
                        @if($g->descripcion)
                        <div class="text-muted" style="font-size:.78rem;">{{ $g->descripcion }}</div>
                        @endif
                        <span class="grupo-badge mt-1 d-inline-block {{ $g->activo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                            {{ $g->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="editarGrupo({{ $g->id }}, '{{ addslashes($g->nombre) }}', '{{ addslashes($g->descripcion ?? '') }}', {{ $g->activo ? 'true' : 'false' }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="eliminarGrupo({{ $g->id }}, '{{ addslashes($g->nombre) }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="font-size:.85rem;">
                    Sin grupos creados aún.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Asignación de PCs --}}
    <div class="col-lg-7">
        <div class="card card-mp">
            <div class="card-header bg-white px-4 py-3">
                <span class="section-title-mp"><i class="fas fa-desktop"></i>Asignar PCs a grupos</span>
            </div>
            <div class="card-body px-4 py-3">
                @forelse($pcs as $pc)
                <div class="pc-card">
                    <div class="pc-info">
                        <div class="pc-dot"></div>
                        <div>
                            <div class="fw-semibold" style="font-size:.85rem;">{{ $pc->nombre }}</div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $pc->ip }}</div>
                        </div>
                    </div>
                    <select class="form-select form-select-sm" style="width:180px;"
                        onchange="asignarGrupo('{{ $pc->nombre }}', this.value)">
                        <option value="">— Sin grupo —</option>
                        @foreach($grupos->where('activo', true) as $g)
                        <option value="{{ $g->nombre }}" {{ $pc->grupo === $g->nombre ? 'selected' : '' }}>
                            {{ $g->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @empty
                <div class="text-center text-muted py-3" style="font-size:.85rem;">
                    No hay PCs registradas actualmente.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Modal crear/editar grupo --}}
<div class="modal fade" id="modal-grupo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo">Nuevo grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="grupo-id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="grupo-nombre" class="form-control" maxlength="100" placeholder="Ej: Administración">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <input type="text" id="grupo-desc" class="form-control" maxlength="255" placeholder="Opcional">
                </div>
                <div class="form-check" id="check-activo-wrap">
                    <input class="form-check-input" type="checkbox" id="grupo-activo" checked>
                    <label class="form-check-label" for="grupo-activo">Activo</label>
                </div>
                <div id="modal-error" class="text-danger mt-2 d-none" style="font-size:.82rem;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-mp btn-sm" id="btn-guardar-grupo" onclick="guardarGrupo()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function editarGrupo(id, nombre, desc, activo) {
    document.getElementById('grupo-id').value    = id;
    document.getElementById('grupo-nombre').value = nombre;
    document.getElementById('grupo-desc').value   = desc;
    document.getElementById('grupo-activo').checked = activo;
    document.getElementById('modal-titulo').textContent = 'Editar grupo';
    document.getElementById('check-activo-wrap').classList.remove('d-none');
    new bootstrap.Modal(document.getElementById('modal-grupo')).show();
}

document.getElementById('modal-grupo').addEventListener('hidden.bs.modal', () => {
    document.getElementById('grupo-id').value     = '';
    document.getElementById('grupo-nombre').value  = '';
    document.getElementById('grupo-desc').value    = '';
    document.getElementById('grupo-activo').checked = true;
    document.getElementById('modal-titulo').textContent = 'Nuevo grupo';
    document.getElementById('modal-error').classList.add('d-none');
});

async function guardarGrupo() {
    const id     = document.getElementById('grupo-id').value;
    const nombre = document.getElementById('grupo-nombre').value.trim();
    const desc   = document.getElementById('grupo-desc').value.trim();
    const activo = document.getElementById('grupo-activo').checked;
    const errDiv = document.getElementById('modal-error');

    if (!nombre) { errDiv.textContent = 'El nombre es obligatorio.'; errDiv.classList.remove('d-none'); return; }
    errDiv.classList.add('d-none');

    const url    = id ? `/grupos/${id}` : '/grupos';
    const method = id ? 'PUT' : 'POST';

    const r = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ nombre, descripcion: desc, activo }),
    });
    const d = await r.json();

    if (d.ok) {
        location.reload();
    } else {
        const msgs = d.errors ? Object.values(d.errors).flat().join(' ') : 'Error al guardar.';
        errDiv.textContent = msgs;
        errDiv.classList.remove('d-none');
    }
}

async function eliminarGrupo(id, nombre) {
    if (!confirm(`¿Eliminar el grupo "${nombre}"? Las PCs asignadas quedarán sin grupo.`)) return;
    const r = await fetch(`/grupos/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf },
    });
    const d = await r.json();
    if (d.ok) location.reload();
    else alert('Error al eliminar');
}

async function asignarGrupo(pcNombre, grupo) {
    await fetch('/grupos/asignar-pc', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ pc_nombre: pcNombre, grupo }),
    });
}
</script>
@endpush
