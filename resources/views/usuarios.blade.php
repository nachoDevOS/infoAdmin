@extends('layouts.app')

@section('title', 'Usuarios')

@push('styles')
<style>
    .usuario-avatar {
        width: 38px;
        height: 38px;
        border-radius: 4px;
        background: #EAF8FE;
        color: #1677A8;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 700;
    }

    .usuario-email {
        color: #8A99A8;
        font-size: .8rem;
    }

    .role-badge {
        border-radius: 4px;
        font-size: .72rem;
        font-weight: 700;
        padding: .35rem .55rem;
    }

    .role-badge.admin {
        background: #EAF8FE;
        color: #1677A8;
        border: 1px solid #BEE9FB;
    }

    .role-badge.administrador {
        background: #E9F8F5;
        color: #16836C;
        border: 1px solid #BFECE2;
    }

    .form-control-mp {
        border: 1px solid #D9E0E7;
        border-radius: 4px;
        padding: .65rem 1rem;
        font-size: .9rem;
        background: #fff;
    }

    .form-control-mp:focus {
        border-color: #22A7F0;
        box-shadow: 0 0 0 3px rgba(34,167,240,.14);
        outline: none;
    }

    .form-label-mp {
        font-size: .82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .4rem;
    }

    .seccion-titulo {
        font-size: .95rem;
        font-weight: 700;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seccion-titulo i {
        color: #22A7F0;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        border: 1px solid #D9E0E7;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748B;
    }

    .btn-icon:hover {
        border-color: #E74C3C;
        color: #C0392B;
        background: #FDECEA;
    }

    .count-chip {
        background: #EAF8FE;
        border: 1px solid #BEE9FB;
        border-radius: 4px;
        color: #1677A8;
        font-size: .78rem;
        font-weight: 700;
        padding: .35rem .65rem;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h4 class="page-title"><i class="fas fa-users-cog me-2"></i>Usuarios</h4>
        <div class="page-subtitle">Crea accesos y asigna el rol que corresponde a cada persona.</div>
    </div>
    <span class="count-chip">{{ $usuarios->count() }} usuario(s)</span>
</div>

@if(session('ok'))
    <div class="alert alert-success py-2">
        <i class="fas fa-check-circle me-1"></i>{{ session('ok') }}
    </div>
@endif

@if(session('err'))
    <div class="alert alert-danger py-2">
        <i class="fas fa-exclamation-circle me-1"></i>{{ session('err') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger py-2">
        <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-mp">
            <div class="card-header bg-white px-4 py-3">
                <span class="section-title-mp"><i class="fas fa-users"></i>Usuarios registrados</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Usuario</th>
                                <th>Rol</th>
                                <th>Creado</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="usuario-avatar">
                                                {{ Str::upper(Str::substr($usuario->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $usuario->name }}</div>
                                                <div class="usuario-email">{{ $usuario->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge {{ $usuario->role === 'Administrador' ? 'administrador' : 'admin' }}">
                                            {{ $usuario->role ?? 'Admin' }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $usuario->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        @if(auth()->id() === $usuario->id)
                                            <span class="badge text-bg-light border">Actual</span>
                                        @else
                                            <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-icon" title="Eliminar usuario">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Sin usuarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-mp">
            <div class="card-header bg-white px-4 py-3">
                <span class="section-title-mp"><i class="fas fa-user-plus"></i>Nuevo usuario</span>
            </div>
            <div class="card-body px-4 py-4">
                <form action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-mp" for="name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control form-control-mp"
                               value="{{ old('name') }}" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-mp" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control form-control-mp"
                               value="{{ old('email') }}" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-mp" for="role">Rol <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select form-control-mp" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role', 'Admin') === $role->name)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-mp" for="password">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control form-control-mp"
                               minlength="8" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-mp" for="password_confirmation">Confirmar contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control form-control-mp" minlength="8" required>
                    </div>

                    <div class="form-actions-mp">
                        <button type="submit" class="btn btn-mp btn-sm px-3">
                            <i class="fas fa-save me-1"></i>Crear usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
