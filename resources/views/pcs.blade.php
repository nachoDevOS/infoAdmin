@extends('layouts.app')

@section('title', 'PCs Conectadas')

@push('styles')
<style>
    .pc-card {
        background: #fff;
        border: 1px solid #E5E9EF;
        border-radius: 6px;
        padding: .9rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow .15s;
    }
    .pc-card:hover { box-shadow: 0 2px 8px rgba(42,63,84,.1); }
    .pc-dot {
        width: 11px; height: 11px;
        border-radius: 50%;
        background: #22C55E;
        box-shadow: 0 0 6px #22C55E;
        flex-shrink: 0;
    }
    .pc-nombre { font-weight: 600; font-size: .95rem; color: #1A1A2E; }
    .pc-ip     { font-size: .8rem; color: #94A3B8; }
    .pc-tiempo { font-size: .78rem; color: #94A3B8; margin-left: auto; white-space: nowrap; }
    #buscador  { max-width: 320px; }
    .conteo-badge {
        background: #1D6A3A; color: #fff;
        border-radius: 20px; padding: .2rem .8rem;
        font-size: .85rem; font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h4 class="page-title"><i class="fas fa-desktop me-2"></i>PCs Conectadas</h4>
        <p class="page-subtitle">PCs con heartbeat en los últimos 3 minutos</p>
    </div>
</div>

<div class="card-mp mb-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="conteo-badge"><span id="conteo">0</span> en línea</span>
        <input id="buscador" type="text" class="form-control form-control-mp"
               placeholder="🔍  Buscar por nombre o IP...">
        <button class="btn-mp-outline ms-auto" onclick="cargarPcs()">
            <i class="fas fa-sync-alt"></i> Actualizar
        </button>
    </div>
</div>

<div id="lista-pcs" class="d-grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(280px,1fr));">
    <div class="pc-card"><span style="color:#94A3B8;font-size:.9rem;">Cargando...</span></div>
</div>
@endsection

@push('scripts')
<script>
let _todas = [];

function cargarPcs() {
    fetch('{{ route("panel.pcs-lista") }}')
        .then(r => r.json())
        .then(pcs => {
            _todas = pcs;
            renderizar(pcs);
        })
        .catch(() => {});
}

function renderizar(pcs) {
    document.getElementById('conteo').textContent = pcs.length;
    const lista = document.getElementById('lista-pcs');

    if (pcs.length === 0) {
        lista.innerHTML = '<div class="pc-card"><span style="color:#94A3B8;">Sin PCs conectadas en este momento.</span></div>';
        return;
    }

    lista.innerHTML = pcs.map(pc => {
        const hace = tiempoAtras(pc.updated_at);
        return `
        <div class="pc-card">
            <span class="pc-dot"></span>
            <div>
                <div class="pc-nombre">${pc.nombre}</div>
                <div class="pc-ip"><i class="fas fa-network-wired" style="font-size:.7rem;"></i> ${pc.ip ?? '—'}</div>
            </div>
            <span class="pc-tiempo">${hace}</span>
        </div>`;
    }).join('');
}

function tiempoAtras(fecha) {
    const seg = Math.floor((Date.now() - new Date(fecha).getTime()) / 1000);
    if (seg < 60)  return `hace ${seg}s`;
    if (seg < 3600) return `hace ${Math.floor(seg/60)}min`;
    return `hace ${Math.floor(seg/3600)}h`;
}

document.getElementById('buscador').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    renderizar(_todas.filter(pc =>
        pc.nombre.toLowerCase().includes(q) || (pc.ip ?? '').includes(q)
    ));
});

cargarPcs();
setInterval(cargarPcs, 30000);
</script>
@endpush
