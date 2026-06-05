@extends('layouts.app')

@section('title', 'Confirmaciones')

@push('styles')
<style>
    .delivery-toolbar .form-select {
        border-color: #D9E0E7;
        border-radius: 4px;
    }

    .metric-box {
        border-left: 3px solid #22A7F0;
        padding: 1rem 1.15rem;
    }

    .metric-box.success { border-left-color: #26B99A; }
    .metric-box.info { border-left-color: #3498DB; }

    .metric-value {
        color: #2A3F54;
        font-size: 1.9rem;
        font-weight: 700;
        line-height: 1;
    }

    .metric-label {
        color: #8A99A8;
        font-size: .78rem;
        margin-top: .35rem;
    }

    .status-cell {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: .25rem;
        min-width: 118px;
    }

    .status-time {
        color: #8A99A8;
        font-size: .72rem;
        white-space: nowrap;
    }

    .status-time.pending {
        color: #C8D1DA;
    }

    .status-na {
        color: #8A99A8;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: .25rem;
        min-width: 118px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h4 class="page-title"><i class="fas fa-check-double me-2"></i>Estado de Entrega</h4>
        <div class="page-subtitle">Revisa que PCs recibieron, vieron o descargaron cada mensaje.</div>
    </div>
</div>

<div class="card card-mp mb-4">
    <div class="card-header bg-white px-4 py-3">
        <span class="section-title-mp"><i class="fas fa-envelope-open-text"></i>Mensaje a revisar</span>
    </div>
    <div class="card-body px-4 py-3">
        <div class="row align-items-end g-3 delivery-toolbar">
            <div class="col-md-9">
                <label class="form-label small fw-semibold mb-1">Seleccionar mensaje</label>
                <select id="sel-mensaje" class="form-select">
                    <option value="">Elige un mensaje</option>
                    @foreach($mensajes as $m)
                    <option value="{{ $m->id }}">
                        {{ $m->created_at?->format('d/m/Y H:i') }} - {{ Str::limit($m->titulo, 60) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button id="btn-ver" class="btn btn-mp btn-sm flex-grow-1 px-3">
                    <i class="fas fa-search me-1"></i>Ver
                </button>
                <a id="btn-export" href="#" class="btn btn-sm btn-outline-success d-none px-3" title="Exportar CSV">
                    <i class="fas fa-file-csv"></i>
                </a>
                @can('mensajes.enviar')
                <button id="btn-reenviar" class="btn btn-sm btn-outline-warning d-none px-3" title="Reenviar a todas las PCs conectadas">
                    <i class="fas fa-redo"></i>
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div id="stats-sec" class="d-none mb-4">
    <div class="row g-3">
        <div class="col-sm-4">
            <div class="card card-mp metric-box">
                <div class="metric-value" id="stat-total">-</div>
                <div class="metric-label">PCs objetivo</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-mp metric-box success">
                <div class="metric-value text-success" id="stat-recibidos">-</div>
                <div class="metric-label">Recibieron (<span id="stat-pct-r">0</span>%)</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card card-mp metric-box info">
                <div class="metric-value text-info" id="stat-descargas">-</div>
                <div class="metric-label">Descargaron (<span id="stat-pct-d">0</span>%)</div>
            </div>
        </div>
    </div>
</div>

<div id="tabla-sec" class="d-none">
    <div class="card card-mp">
        <div class="card-header bg-white px-4 py-3">
            <span class="section-title-mp"><i class="fas fa-desktop"></i>PCs registradas</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">PC</th>
                            <th>IP</th>
                            <th class="text-center">Recibido</th>
                            <th class="text-center">Leído</th>
                            <th class="text-center">Descargado</th>
                        </tr>
                    </thead>
                    <tbody id="conf-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="sin-datos" class="card card-mp d-none">
    <div class="empty-state-mp">
        <i class="fas fa-inbox"></i>
        Sin confirmaciones registradas para este mensaje.
    </div>
</div>
@endsection

@push('scripts')
<script>
const statusCell = (ok, time) => `
    <span class="status-cell">
        ${ok
            ? '<i class="fas fa-check-circle text-success fs-5"></i>'
            : '<i class="fas fa-times-circle text-danger opacity-25 fs-5"></i>'}
        <span class="status-time ${time ? '' : 'pending'}">${time || 'Pendiente'}</span>
    </span>
`;

const noFileCell = () => `
    <span class="status-na">
        <i class="fas fa-minus-circle text-secondary opacity-50 fs-5"></i>
        <span class="status-time pending">Sin archivo</span>
    </span>
`;

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let mensajeActualId = null;

document.getElementById('btn-ver').addEventListener('click', async function() {
    const id = document.getElementById('sel-mensaje').value;
    if (!id) return;
    mensajeActualId = id;

    const r = await fetch(`{{ route('confirmaciones.data') }}?mensaje_id=${id}`);
    const d = await r.json();

    const s = d.stats;
    const tieneArchivo = Boolean(d.mensaje?.tiene_archivo);
    document.getElementById('stat-total').textContent     = s.total;
    document.getElementById('stat-recibidos').textContent = s.recibidos;
    document.getElementById('stat-pct-r').textContent     = s.pct_recibido;
    document.getElementById('stat-descargas').textContent = tieneArchivo ? s.descargas : '-';
    document.getElementById('stat-pct-d').textContent     = tieneArchivo ? s.pct_descarga : 'N/A';
    document.getElementById('stats-sec').classList.remove('d-none');

    // Mostrar botones de acción
    const btnExport   = document.getElementById('btn-export');
    const btnReenviar = document.getElementById('btn-reenviar');
    if (btnExport) {
        btnExport.href = `{{ route('confirmaciones.export') }}?mensaje_id=${id}`;
        btnExport.classList.remove('d-none');
    }
    if (btnReenviar) btnReenviar.classList.remove('d-none');

    const tbody   = document.getElementById('conf-body');
    const sinDatos = document.getElementById('sin-datos');
    const tablaSec = document.getElementById('tabla-sec');

    if (!d.confirmaciones.length) {
        tablaSec.classList.add('d-none');
        sinDatos.classList.remove('d-none');
        return;
    }

    sinDatos.classList.add('d-none');
    tablaSec.classList.remove('d-none');

    tbody.innerHTML = d.confirmaciones.map(c => `
        <tr>
            <td class="ps-3 fw-semibold">${c.pc_nombre}</td>
            <td class="text-muted small">${c.pc_ip}</td>
            <td class="text-center">${statusCell(c.recibido, c.recibido_hora)}</td>
            <td class="text-center">${statusCell(c.visto, c.leido_hora)}</td>
            <td class="text-center">${tieneArchivo ? statusCell(c.descargado, c.descargado_hora) : noFileCell()}</td>
        </tr>
    `).join('');
});

// Reenviar mensaje
const btnReenviar = document.getElementById('btn-reenviar');
if (btnReenviar) {
    btnReenviar.addEventListener('click', async function() {
        if (!mensajeActualId) return;
        if (!confirm('¿Reenviar este mensaje a todas las PCs conectadas ahora?')) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const r = await fetch('{{ route("confirmaciones.reenviar") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ mensaje_id: mensajeActualId }),
            });
            const d = await r.json();
            alert(d.ok ? '✅ ' + d.mensaje : '❌ ' + (d.error || 'Error al reenviar'));
        } catch {
            alert('❌ Error de conexión');
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-redo"></i>';
        }
    });
}
</script>
@endpush
