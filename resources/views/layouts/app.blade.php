<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'infoAdmin') — Sistema de Mensajería</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --mp-azul:       #22A7F0;
            --mp-azul-dark:  #1A8AC7;
            --mp-azul-light: #5BC0F8;
            --mp-sidebar:    #2A3F54;
            --mp-sidebar-2:  #243649;
            --mp-text:       #334155;
            --mp-muted:      #8A99A8;
            --mp-sidebar-w:  230px;
        }

        * { box-sizing: border-box; }

        body {
            background: #F5F7FA;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            color: var(--mp-text);
        }

        /* ── Navbar ─────────────────────────────────────────── */
        .navbar-mp {
            background: #fff;
            height: 58px;
            border-bottom: 1px solid #E5E9EF;
            box-shadow: 0 1px 2px rgba(42,63,84,.06);
            margin-left: var(--mp-sidebar-w);
            z-index: 100;
        }
        .navbar-mp .user-chip {
            background: #F5F7FA;
            color: #52616F;
            border: 1px solid #E5E9EF;
            border-radius: 4px;
            padding: 4px 12px;
            font-size: .8rem;
            display: flex; align-items: center; gap: 6px;
        }
        .btn-salir {
            background: #fff;
            border: 1px solid #D9E0E7;
            color: #52616F;
            border-radius: 4px;
            padding: 4px 14px;
            font-size: .82rem;
            transition: all .2s;
        }
        .btn-salir:hover {
            background: #F5F7FA;
            color: #2A3F54;
            border-color: #C8D1DA;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            background: linear-gradient(180deg, var(--mp-sidebar) 0%, var(--mp-sidebar-2) 100%);
            width: var(--mp-sidebar-w);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 101;
            flex-shrink: 0;
            padding: 1.2rem 0 1.5rem;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: .25rem 1.2rem 1.25rem;
            margin-bottom: .35rem;
            display: flex;
            align-items: center;
            gap: .85rem;
            color: #fff;
        }
        .sidebar-brand img {
            width: 56px;
            height: 56px;
            border-radius: 4px;
            object-fit: contain;
            background: #fff;
            padding: 5px;
        }
        .sidebar-brand-name {
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.1;
        }
        .sidebar-brand-caption {
            color: rgba(255,255,255,.45);
            font-size: .7rem;
            margin-top: .15rem;
        }
        .sidebar-section-label {
            color: rgba(255,255,255,.35);
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 0 1.4rem .5rem;
        }
        .sidebar .nav-link {
            color: #C7D0D9;
            padding: .62rem 1.4rem;
            margin: 1px 0;
            border-radius: 0;
            font-size: .88rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all .18s;
            position: relative;
        }
        .sidebar .nav-link i {
            width: 18px;
            text-align: center;
            font-size: .85rem;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background: rgba(0,0,0,.18);
            color: #fff;
            font-weight: 600;
        }
        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            background: var(--mp-azul);
            border-radius: 0;
        }
        .sidebar-divider {
            border-top: 1px solid rgba(255,255,255,.1);
            margin: .8rem .6rem;
        }
        .sidebar-bottom {
            margin-top: auto;
            padding: 0 1.4rem;
            color: rgba(255,255,255,.3);
            font-size: .72rem;
        }

        /* ── Contenido ───────────────────────────────────────── */
        .main-content {
            margin-left: var(--mp-sidebar-w);
            padding: 1.8rem 2rem;
            width: calc(100% - var(--mp-sidebar-w));
        }

        /* ── Cards ───────────────────────────────────────────── */
        .card-mp {
            border: 1px solid #E5E9EF;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(42,63,84,.05);
            background: #fff;
        }
        .card-mp .card-header {
            border-radius: 4px 4px 0 0 !important;
            border-bottom: 1px solid #EEF1F5 !important;
        }

        /* ── Badges tipo ─────────────────────────────────────── */
        .badge-notificacion { background: #22A7F0; }
        .badge-instructivo  { background: #1D6A3A; }
        .badge-urgente      { background: #B71C1C; }
        .badge-reunion      { background: #E65100; }

        /* ── PC badge ────────────────────────────────────────── */
        .pc-badge {
            background: #EAF8FE;
            color: #1677A8;
            border: 1px solid #BEE9FB;
            border-radius: 4px;
            padding: 4px 14px;
            font-size: .8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* ── Botón principal ─────────────────────────────────── */
        .btn-mp {
            background: #22A7F0;
            border: none;
            color: #fff;
            border-radius: 4px;
            font-weight: 600;
            transition: all .2s;
            box-shadow: none;
        }
        .btn-mp:hover {
            background: #1A8AC7;
            color: #fff;
        }
        .btn-mp:active { background: #1677A8; }

        .page-title {
            color: #2A3F54;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .page-header .page-title {
            margin-bottom: 0;
        }

        .page-subtitle {
            color: #8A99A8;
            font-size: .84rem;
            margin-top: .2rem;
        }

        .section-title-mp {
            color: #2A3F54;
            font-size: .92rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .section-title-mp i {
            color: var(--mp-azul);
        }

        .btn-soft {
            border: 1px solid #D9E0E7;
            border-radius: 4px;
            background: #fff;
            color: #52616F;
            font-weight: 600;
        }

        .btn-soft:hover {
            border-color: #22A7F0;
            background: #EAF8FE;
            color: #1677A8;
        }

        .form-actions-mp {
            border-top: 1px solid #EEF1F5;
            padding-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .65rem;
        }

        .empty-state-mp {
            color: #8A99A8;
            text-align: center;
            padding: 2.2rem 1rem;
        }

        .empty-state-mp i {
            color: #C8D1DA;
            display: block;
            font-size: 2rem;
            margin-bottom: .65rem;
        }

        .table thead {
            background: #F7F9FB;
            color: #52616F;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .table > :not(caption) > * > * {
            padding: .85rem .75rem;
            border-bottom-color: #EEF1F5;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--mp-azul);
            box-shadow: 0 0 0 .18rem rgba(34,167,240,.16);
        }

        .modal-content {
            border: none;
            border-radius: 4px;
            box-shadow: 0 12px 42px rgba(42,63,84,.24);
        }

        @media (max-width: 767.98px) {
            .navbar-mp {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1.2rem;
            }

        @media (max-width: 576px) {
            .page-header,
            .form-actions-mp {
                align-items: stretch;
                flex-direction: column;
            }

            .form-actions-mp .btn {
                width: 100%;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-mp px-3">
    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="user-chip">
            <i class="fas fa-user-circle"></i>{{ Auth::user()->name ?? '' }}
        </span>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button class="btn-salir"><i class="fas fa-sign-out-alt me-1"></i>Salir</button>
        </form>
    </div>
</nav>

<div class="d-flex">
    <div class="sidebar d-none d-md-flex">
        <div class="sidebar-brand">
            <img src="{{ asset('image/logo.png') }}" alt="infoAdmin">
            <div>
                <div class="sidebar-brand-name">infoAdmin</div>
                <div class="sidebar-brand-caption">Panel administrativo</div>
            </div>
        </div>
        <p class="sidebar-section-label">Menú</p>
        <ul class="nav flex-column w-100">
            @if(Auth::user()->hasPermission('mensajes.enviar'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('panel') ? 'active' : '' }}" href="{{ route('panel') }}">
                    <i class="fas fa-paper-plane"></i> Enviar Mensaje
                </a>
            </li>
            @endif
            @if(Auth::user()->hasPermission('historial.ver'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('historial') ? 'active' : '' }}" href="{{ route('historial') }}">
                    <i class="fas fa-history"></i> Historial
                </a>
            </li>
            @endif
            @if(Auth::user()->hasPermission('confirmaciones.ver'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('confirmaciones') ? 'active' : '' }}" href="{{ route('confirmaciones') }}">
                    <i class="fas fa-check-double"></i> Confirmaciones
                </a>
            </li>
            @endif
        </ul>
        <div class="sidebar-divider"></div>

        {{-- PCs en línea --}}
        <div class="sidebar-divider"></div>
        <a href="{{ route('pcs') }}" class="nav-link {{ request()->routeIs('pcs') ? 'active' : '' }}" style="display:flex;align-items:center;justify-content:space-between;">
            <span><i class="fas fa-desktop"></i> PCs conectadas</span>
            <span id="sidebar-pcs-count" class="badge rounded-pill" style="background:#1D6A3A;font-size:.7rem;">0</span>
        </a>

        <div class="sidebar-divider"></div>
        <p class="sidebar-section-label" style="margin-top:.4rem;">Configuración</p>
        <ul class="nav flex-column w-100">
            @if(Auth::user()->hasPermission('usuarios.gestionar'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                    <i class="fas fa-users-cog"></i> Usuarios
                </a>
            </li>
            @endif
            @if(Auth::user()->hasPermission('roles.gestionar'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <i class="fas fa-user-shield"></i> Roles
                </a>
            </li>
            @endif
@if(Auth::user()->hasPermission('tipos.gestionar'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('tipos.*') ? 'active' : '' }}" href="{{ route('tipos.index') }}">
                    <i class="fas fa-tags"></i> Tipos de mensaje
                </a>
            </li>
            @endif
        </ul>
        <div class="sidebar-divider"></div>
        <div class="sidebar-bottom">v1.0 &nbsp;·&nbsp; infoAdmin</div>
    </div>

    <div class="flex-grow-1 main-content">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
(function () {
    function actualizarContador() {
        fetch('{{ route("panel.pcs-lista") }}')
            .then(r => r.json())
            .then(pcs => {
                document.getElementById('sidebar-pcs-count').textContent = pcs.length;
            })
            .catch(() => {});
    }

    actualizarContador();
    setInterval(actualizarContador, 30000);
})();
</script>
</body>
</html>

