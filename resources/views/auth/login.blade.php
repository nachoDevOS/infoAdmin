<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>infoAdmin - Acceso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: #F5F7FA;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-shell {
            max-width: 920px;
            width: 100%;
        }

        .brand-panel {
            background: #2A3F54;
            border-radius: 4px 0 0 4px;
            color: #fff;
            min-height: 390px;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 4px;
            background: #22A7F0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .brand-panel h1 {
            font-size: 1.9rem;
            font-weight: 700;
            margin: 0;
        }

        .brand-panel p {
            opacity: .78;
            font-size: .92rem;
            margin: .35rem 0 0;
        }

        .login-card {
            border: 1px solid #E5E9EF;
            border-left: 0;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 12px 34px rgba(42,63,84,.12);
            min-height: 390px;
            width: 100%;
        }

        .login-body {
            padding: 2.25rem;
        }

        .login-title {
            color: #2A3F54;
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .login-subtitle {
            color: #8A99A8;
            font-size: .85rem;
            margin-bottom: 1.4rem;
        }

        .form-label {
            color: #52616F;
            font-size: .84rem;
        }

        .form-control {
            border-color: #D9E0E7;
            border-radius: 4px;
        }

        .form-control:focus {
            border-color: #22A7F0;
            box-shadow: 0 0 0 .2rem rgba(34,167,240,.18);
        }

        .btn-login {
            background: #22A7F0;
            border: none;
            border-radius: 4px;
            font-size: .9rem;
            font-weight: 600;
            padding: .58rem .9rem;
        }

        .btn-login:hover {
            background: #1A8AC7;
        }

        @media (max-width: 768px) {
            .brand-panel {
                border-radius: 4px 4px 0 0;
                min-height: auto;
                padding: 1.6rem;
            }

            .login-card {
                border-left: 1px solid #E5E9EF;
                border-radius: 0 0 4px 4px;
                min-height: auto;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-shell mx-auto">
        <div class="row g-0">
            <div class="col-md-5">
                <div class="brand-panel">
                    <div>
                        <div class="brand-mark"><i class="fas fa-paper-plane"></i></div>
                        <h1>infoAdmin</h1>
                        <p>Sistema de mensajeria interna</p>
                    </div>
                    <small style="opacity:.55;">Panel administrativo</small>
                </div>
            </div>
            <div class="col-md-7">
                <div class="login-card bg-white">
                    <div class="login-body">
                        <h5 class="login-title">Acceso al panel</h5>
                        <div class="login-subtitle">Ingresa con tu usuario asignado.</div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="/login" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-envelope me-1 text-secondary"></i>Email del administrador
                                </label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email') }}" placeholder="admin@admin.com" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-lock me-1 text-secondary"></i>Contrasena
                                </label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-4 form-check">
                                <input type="checkbox" name="recordar" id="recordar" class="form-check-input">
                                <label for="recordar" class="form-check-label small">Recordar sesion</label>
                            </div>
                            <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                                <i class="fas fa-sign-in-alt me-2"></i>Ingresar al panel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
