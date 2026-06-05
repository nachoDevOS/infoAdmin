@extends('layouts.app')

@section('title', 'Roles')

@push('styles')
<style>
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

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }

    .permission-check {
        border: 1px solid #E5E9EF;
        border-radius: 4px;
        padding: .65rem .75rem;
        display: flex;
        align-items: center;
        gap: .55rem;
        font-size: .84rem;
        color: #52616F;
        background: #fff;
    }

    .permission-check input {
        margin: 0;
    }

    .role-title {
        font-weight: 700;
        color: #2A3F54;
    }

    .role-meta {
        color: #8A99A8;
        font-size: .78rem;
    }

    .role-card-head {
        border-bottom: 1px solid #EEF1F5;
        margin: -0.25rem -0.25rem 1rem;
        padding: .25rem .25rem 1rem;
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

    .btn-outline-mp {
        border: 1px solid #D9E0E7;
        border-radius: 4px;
        color: #52616F;
        background: #fff;
    }

    .btn-outline-mp:hover {
        border-color: #22A7F0;
        color: #1677A8;
        background: #EAF8FE;
    }

    @media (max-width: 768px) {
        .permission-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h4 class="page-title"><i class="fas fa-user-shield me-2"></i>Roles y permisos</h4>
        <div class="page-subtitle">Define que puede ver o modificar cada grupo de usuarios.</div>
    </div>
    <span class="count-chip">{{ $roles->count() }} rol(es)</span>
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
        <div class="d-flex flex-column gap-3">
            @forelse($roles as $role)
                <div class="card card-mp">
                    <div class="card-body px-4 py-3">
                        <form action="{{ route('roles.update', $role) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="role-card-head d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="role-title">{{ $role->name }}</div>
                                    <div class="role-meta">{{ count($role->permissions ?? []) }} permiso(s)</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-mp px-3" style="box-shadow:none;">
                                        <i class="fas fa-save me-1"></i>Guardar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-mp"
                                            onclick="if (confirm('¿Eliminar este rol?')) document.getElementById('delete-role-{{ $role->id }}').submit()">
                                        <i class="fas fa-trash me-1"></i>Eliminar
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label-mp">Nombre del rol</label>
                                <input type="text" name="name" class="form-control form-control-mp"
                                       value="{{ old('name', $role->name) }}" maxlength="80" required>
                            </div>

                            <div class="permission-grid">
                                @foreach($permissions as $key => $label)
                                    <label class="permission-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                               @checked(in_array($key, $role->permissions ?? [], true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </form>

                        <form id="delete-role-{{ $role->id }}" action="{{ route('roles.destroy', $role) }}" method="POST">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            @empty
                <div class="card card-mp">
                    <div class="card-body text-center text-muted py-4">Sin roles registrados.</div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-mp">
            <div class="card-header bg-white px-4 py-3">
                <span class="section-title-mp"><i class="fas fa-plus-circle"></i>Nuevo rol</span>
            </div>
            <div class="card-body px-4 py-4">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-mp" for="name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control form-control-mp"
                               value="{{ old('name') }}" maxlength="80" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-mp">Permisos</label>
                        <div class="permission-grid">
                            @foreach($permissions as $key => $label)
                                <label class="permission-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                           @checked(in_array($key, old('permissions', []), true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-actions-mp">
                        <button type="submit" class="btn btn-mp btn-sm px-3">
                            <i class="fas fa-save me-1"></i>Crear rol
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
