# infoAdmin — Panel Administrativo de Notificaciones

Panel web Laravel para enviar mensajes a las PCs de los empleados en tiempo real mediante WebSocket (Reverb).

---

## Arquitectura

```
infoAdmin (Docker/Coolify)
├── Nginx     :8000  →  Panel web (detrás de Traefik con SSL)
├── php-fpm          →  Procesa PHP
├── Reverb    :8081  →  WebSocket para clientes infoDesk
├── queue:work       →  Procesa trabajos en cola (database)
└── schedule:work    →  Ejecuta mensajes programados cada minuto
```

---

## Variables de entorno (.env)

### Aplicación

| Variable | Descripción |
|----------|-------------|
| `APP_NAME` | Nombre de la aplicación |
| `APP_ENV` | `local` en desarrollo, `production` en producción |
| `APP_KEY` | Clave de cifrado — generada con `php artisan key:generate` |
| `APP_DEBUG` | `true` en desarrollo, `false` en producción |
| `APP_URL` | URL pública del panel (ej: `https://notificaciones.beni.gob.bo`) |

### Base de datos

| Variable | Descripción |
|----------|-------------|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Host de la base de datos |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | Nombre de la base de datos |
| `DB_USERNAME` | Usuario |
| `DB_PASSWORD` | Contraseña |

### API Token (infoDesk)

| Variable | Descripción |
|----------|-------------|
| `API_TOKEN` | Token secreto enviado por los clientes infoDesk en cada request HTTP. Debe coincidir exactamente con `_API_TOKEN` compilado dentro del exe. Si se cambia hay que **recompilar y redistribuir** el exe. |

### Reverb (WebSocket)

| Variable | Valor en producción | Descripción |
|----------|---------------------|-------------|
| `REVERB_APP_ID` | `infodesk` | Identificador de la app Reverb |
| `REVERB_APP_KEY` | `iKW9AMG...` | Clave pública del canal. Debe coincidir con `_SERVIDOR_APP_KEY` compilado en el exe. Si se cambia hay que recompilar el exe. |
| `REVERB_APP_SECRET` | `jTUVBz...` | Secreto usado por Laravel para firmar broadcasts. Solo vive en el servidor, **nunca** en el cliente. |
| `REVERB_HOST` | `0.0.0.0` | Interface de escucha. `0.0.0.0` es obligatorio en Docker para aceptar conexiones externas. |
| `REVERB_PORT` | `8081` | Puerto del WebSocket. Debe coincidir con el Port Mapping de Coolify. |
| `REVERB_SCHEME` | `http` | Siempre `http` dentro del contenedor. El SSL lo maneja Traefik externamente. |

### Cola, sesiones y caché

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `BROADCAST_CONNECTION` | `reverb` | Motor de broadcast en tiempo real |
| `QUEUE_CONNECTION` | `database` | Cola de trabajos en base de datos |
| `SESSION_DRIVER` | `database` | Sesiones en base de datos |
| `CACHE_STORE` | `database` | Caché en base de datos |

---

## Mensajes programados

Los mensajes programados usan el scheduler de Laravel definido en `routes/console.php`:

```php
Schedule::command('mensajes:enviar-programados')->everyMinute();
```

El comando revisa la tabla de mensajes con `estado = 'programado'` y `scheduled_at <= now()`, los emite por broadcast y los marca como `enviado`.

**Requisito:** el proceso `schedule:work` debe estar corriendo en el servidor. En Docker está configurado como programa en supervisord.

---

## Despliegue con Docker (Coolify)

### Procesos en el contenedor (supervisord)

| Proceso | Comando | Puerto |
|---------|---------|--------|
| `nginx` | `nginx -g "daemon off;"` | `8000` |
| `php-fpm` | `php-fpm -F` | interno |
| `queue-worker` | `php artisan queue:work` | — |
| `reverb` | `php artisan reverb:start --host=0.0.0.0 --port=$REVERB_PORT` | `8081` |
| `scheduler` | `php artisan schedule:work` | — |

### Configuración de red en Coolify

| Campo | Valor |
|-------|-------|
| Ports Exposes | `8000` |
| Port Mappings | `8081:8081` |

- Puerto `8000` → gestionado por Traefik, accesible en `https://dominio` con SSL.
- Puerto `8081` → expuesto directamente para WebSocket `ws://` desde los clientes infoDesk.

> El puerto 8081 se elige porque el 8080 estaba ocupado por otro contenedor en el servidor.

### Variables de entorno mínimas en Coolify

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://notificaciones.beni.gob.bo

DB_CONNECTION=mysql
DB_HOST=<host_db>
DB_DATABASE=<nombre_db>
DB_USERNAME=<usuario>
DB_PASSWORD=<contraseña>

REVERB_HOST=0.0.0.0
REVERB_PORT=8081
REVERB_SCHEME=http

API_TOKEN=<token_secreto>
REVERB_APP_KEY=<app_key>
REVERB_APP_SECRET=<app_secret>
```

### Entrypoint del contenedor

Al iniciar el contenedor (`docker/entrypoint.sh`) se ejecuta en orden:
1. Copia `.env.example` → `.env` si no existe
2. Genera `APP_KEY` si falta
3. Crea symlink de storage
4. Espera hasta 60s a que la base de datos esté disponible
5. Corre `php artisan migrate --force --seed`
6. En producción: cachea config, rutas y vistas
7. Arranca `supervisord` con todos los procesos

---

## Desarrollo local

Necesitás 4 terminales:

```bash
# Terminal 1 — Laravel
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 — Reverb
php artisan reverb:start --host=0.0.0.0 --port=8081

# Terminal 3 — Cola
php artisan queue:work

# Terminal 4 — Scheduler
php artisan schedule:work
```

`.env` local:

```env
APP_URL=http://localhost:8000
REVERB_HOST=0.0.0.0
REVERB_PORT=8081
REVERB_SCHEME=http
```

Panel disponible en: `http://localhost:8000/panel`

---

## APIs para infoDesk (.exe)

| Método | Ruta | Descripción |
|--------|------|-------------|
| `POST` | `/api/registro` | PC registra su presencia (heartbeat cada 30s) |
| `POST` | `/api/confirmacion` | PC confirma recibido/visto/descargado |
| `GET` | `/api/version` | Versión actual del exe (auto-update) |

Todas requieren el header `X-Api-Token: <API_TOKEN>`.

---

## Estructura Docker

```
docker/
├── entrypoint.sh               <- inicio del contenedor
├── nginx/
│   └── default.conf            <- HTTP en :8000 → php-fpm
├── php/
│   ├── php.ini
│   └── php-fpm.conf
└── supervisor/
    └── supervisord.conf        <- nginx, php-fpm, queue, reverb, scheduler
```

---

## Solución de problemas

| Problema | Causa probable | Solución |
|----------|---------------|----------|
| Mensajes programados no se envían | `schedule:work` no corre | En terminal Coolify: `ps aux \| grep schedule` |
| Clientes no reciben mensajes | Reverb no corre o puerto bloqueado | `ps aux \| grep reverb` + verificar Port Mapping `8081:8081` |
| Error 4001 en log de infoDesk | `REVERB_APP_KEY` no coincide con el exe | Verificar que la variable en Coolify y `_SERVIDOR_APP_KEY` en `cliente.py` son iguales |
| Deploy falla con "port already allocated" | Puerto ocupado por otro contenedor | Cambiar `REVERB_PORT` y Port Mapping a un puerto libre (ej: `8081`) |
| Panel no carga | Nginx o php-fpm caído | `cat /var/log/supervisor/nginx.err.log` en terminal Coolify |
| Login falla | Usuario no creado | Verificar que `migrate --seed` corrió en entrypoint |
