# Motor de Suscripciones

Motor de suscripciones: administra **clientes**, sus **suscripciones** (asignados a clientes) y los **intentos de cobro** asociados, ejecutando un ciclo de cobro recurrente contra una **pasarela de pagos simulada** con confirmación vía **webhook**.

La solución funciona de punta a punta: se crea un cliente, se le asigna una suscripción, se ejecuta el motor de cobro y el resultado se refleja en la interfaz.

> Prueba técnica · Desarrollador Fullstack PHP + React

---

## Tabla de contenidos

1. [Stack tecnológico](#stack-tecnológico)
2. [Estructura del repositorio](#estructura-del-repositorio)
3. [Requisitos previos](#requisitos-previos)
4. [Instalación del backend](#instalación-del-backend)
5. [Instalación del frontend](#instalación-del-frontend)
6. [Cómo levantar el proyecto (4 procesos)](#cómo-levantar-el-proyecto-4-procesos)
7. [Modelo de base de datos](#modelo-de-base-de-datos)
8. [Reglas de dominio del motor de cobro](#reglas-de-dominio-del-motor-de-cobro)
9. [Cómo simular el paso del tiempo](#cómo-simular-el-paso-del-tiempo)
10. [Cómo forzar el resultado del simulador](#cómo-forzar-el-resultado-del-simulador)
11. [Endpoints de la API](#endpoints-de-la-api)
12. [Flujo de punta a punta (paso a paso)](#flujo-de-punta-a-punta-paso-a-paso)
13. [Datos de prueba](#datos-de-prueba)
14. [Ejecutar pruebas automatizadas](#ejecutar-pruebas-automatizadas)
15. [Herramientas utilizadas y por qué](#herramientas-utilizadas-y-por-qué)
16. [Consideraciones para el técnico que revisa](#consideraciones-para-el-técnico-que-revisa)
17. [Notas sobre el uso de inteligencia artificial](#notas-sobre-el-uso-de-inteligencia-artificial)
18. [Trabajo pendiente y mejoras](#trabajo-pendiente-y-mejoras)

---

## Stack tecnológico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | PHP + Laravel | PHP >= 8.2 · Laravel 12 |
| Base de datos | MySQL | 5.7 / 8.x |
| Frontend | React | React 19 |
| Build frontend | Vite | 7/8 |
| UI | Ant Design + Tailwind CSS | antd 6 · tailwind 4 |
| Data fetching | TanStack Query (React Query) | 5.x |
| Formularios | React Hook Form | 7.x |
| Rutas frontend | React Router | 7.x |
| Colas | Laravel Queue (**driver database**) | — |
| Tareas programadas | Laravel Scheduler | — |

---

## Estructura del repositorio

El repositorio contiene **dos proyectos independientes**:

```
motor_suscripciones/
├── backendLaravelApi/        # API REST (Laravel + MySQL)
│   ├── app/
│   │   ├── Console/Commands/EjecutarCobroCommand.php   # comando del motor: cobro:ejecutar
│   │   ├── Http/Controllers/                           # controllers REST
│   │   ├── Http/Requests/                              # validación de cada endpoint
│   │   ├── Jobs/NotificarWebhookGateway.php            # job de cola que llama al webhook
│   │   ├── Models/                                     # Cliente, Suscripcion, ClienteSuscripcion, CobroSuscripcion
│   │   └── Services/
│   │       ├── CobroMotorService.php                   # motor de cobro (lógica de dominio)
│   │       ├── GatewaySimulatorService.php             # pasarela simulada
│   │       └── GatewayWebhookService.php               # procesa confirmación del webhook
│   ├── config/motor.php        # PARÁMETROS CLAVE (periodos, reintentos, timeout)
│   ├── database/migrations/    # esquema completo
│   ├── routes/api.php          # todas las rutas REST
│   └── routes/console.php      # registro del scheduler cada 1 minuto
└── suscripcionesFrontEnd/      # SPA React (Vite + Ant Design)
    └── src/
        ├── pages/cliente/      # listado + detalle de clientes
        ├── pages/suscripcion/  # suscripciones asignadas y detalle con historial
        ├── hooks/              # lógica por vista (React Query + React Hook Form)
        ├── components/         # layout, estados vacíos/error/carga, tags, modales
        └── services/api.js     # cliente HTTP -> http://127.0.0.1:8000/api
```

---

## Requisitos previos

- **PHP** >= 8.2 con extensiones `pdo_mysql`, `openssl`, `mbstring`, `xml` (el proyecto fue desarrollado con **XAMPP** en Windows).
- **Composer** 2.x.
- **Node.js** >= 20 y **npm**.
- **MySQL** corriendo en el equipo (XAMPP: panel de control → Start MySQL).

Para verificar:

```powershell
php -v
composer --version
node -v
npm -v
```

---

## Instalación del backend

Ubicación: `backendLaravelApi/`.

```powershell
cd backendLaravelApi

# 1) Dependencias de PHP
composer install

# 2) Crear el archivo .env a partir del ejemplo
Copy-Item .env.example .env      # Windows PowerShell
#   Linux/macOS:  cp .env.example .env

# 3) Configurar la base de datos en .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=motor_suscripcion_bd
#    DB_USERNAME=root
#    DB_PASSWORD=            (vacío en XAMPP por defecto)

# 4) Generar la llave de la aplicación
php artisan key:generate

# 5) Crear la base de datos en MySQL (phpMyAdmin o consola):
#    CREATE DATABASE motor_suscripcion_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6) Crear las tablas (migraciones)
php artisan migrate
```

> Las migraciones crean automáticamente las 4 tablas de dominio (clientes, suscripciones, asignaciones, cobros) y las tablas de framework necesarias: `jobs`, `failed_jobs` y `cache`. Como `QUEUE_CONNECTION=database`, **las tablas de cola son obligatorias** — si no existen, los jobs fallarán.

---

## Instalación del frontend

Ubicación: `suscripcionesFrontEnd/`.

```powershell
cd suscripcionesFrontEnd

npm install
npm run dev
```

Vite levantará la SPA en `http://127.0.0.1:5173` por defecto.

**Punto importante:** la URL de la API está configurada en `src/services/api.js`:

```js
const BASE_URL = 'http://127.0.0.1:8000/api';
```

Recuerde usar **`127.0.0.1`, no `localhost`** (ver sección [Consideraciones](#consideraciones-para-el-técnico-que-revisa)). Si corre el backend en otro puerto o desde otro equipo, ajuste este valor (y el `APP_URL` del `.env`).

---

## Cómo levantar el proyecto (4 procesos)

Son **dos carpetas diferentes**, así que se debe levantar **por separado** cada servidor. Como el motor usa **colas** y un **scheduler**, en desarrollo se necesitan **4 procesos en 4 terminales**:

| Terminal | Carpeta | Comando | Qué hace |
|----------|---------|---------|----------|
| 1 | `backendLaravelApi` | `php artisan serve` | Sirve la API en `http://127.0.0.1:8000`. |
| 2 | `backendLaravelApi` | `php artisan queue:work` | Worker de cola: procesa el job `NotificarWebhookGateway` (la pasarela notifica el resultado). **Si no corre, los cobros nunca se resuelven.** |
| 3 | `backendLaravelApi` | `php artisan schedule:work` | Scheduler: ejecuta `cobro:ejecutar` **cada 1 minuto** (el motor automático). |
| 4 | `suscripcionesFrontEnd` | `npm run dev` | Sirve la SPA en `http://127.0.0.1:5173`. |

> **Windows / XAMPP:** `php artisan serve` evita depender del Apache de XAMPP y de configurar un host virtual; es la vía recomendada.

### Alternativa (todo en un comando)

El backend incluye el script `composer dev` que levanta servidor HTTP + worker + scheduler + Vite juntos:

```powershell
# en backendLaravelApi
composer dev
```

Es práctico, pero no se recomienda como flujo principal: es más claro y controlable tener cada proceso en su terminal, especialmente para observar los logs del scheduler y del worker mientras pruebas el motor.

## Modelo de base de datos

MySQL, base de datos `motor_suscripcion_bd`. Todas las tablas de dominio usan **UUID como llave primaria** (mejor práctica para sistemas distribuidos y evita exponer enteros secuenciales en la API).

> **Decisión de diseño:** el planteamiento de la prueba describe "crear un cliente y una suscripción asignándole el cliente". Se prefirió **separar el plan (`suscripcion`) de la asignación (`cliente_suscripcion`)** con una tabla intermedia: evita duplicar el plan por cliente (`UNIQUE(cliente_id, suscripcion_id)` impide que un mismo cliente repita el mismo plan) y permite que **un cliente tenga N suscripciones**, cada una con su estado y fechas de cobro propios por asignación. Así se simplifica el CRUD de suscripciones y se conserva un historial de cobros por asignación.

```
┌───────────────────────┐        ┌───────────────────────────────┐
│  cliente              │        │  suscripcion (plan)           │
│  ─────────────────    │        │  ────────────────────────    │
│  cliente_id (PK uuid) │        │  suscripcion_id (PK uuid)     │
│  cliente_nombre       │        │  suscripcion_nombre           │
│  cliente_correo (UQ)  │        │  suscripcion_descripcion      │
│  cliente_documento    │        │  suscripcion_precio           │
│  cliente_telefono     │        │  suscripcion_periodo          │
│                       │        │    (mensual | anual)          │
└───────────┬───────────┘        └────────────┬──────────────────┘
            │ 1                            N │
            ▼                                 ▼
┌───────────────────────────────────────────────────────────────┐
│  cliente_suscripcion  (asignación cliente<->plan)             │
│  ─────────────────────────────────────────────               │
│  cliente_suscripcion_id (PK uuid)                             │
│  cliente_id      (FK -> cliente)         ON DELETE CASCADE    │
│  suscripcion_id  (FK -> suscripcion)     ON DELETE CASCADE    │
│  estado_cliente_suscripcion (activa|pausada|cancelada)        │
│  fecha_ultimo_cobro                                           │
│  fecha_proximo_cobro                                          │
│  UNIQUE (cliente_id, suscripcion_id)   -- un mismo plan una sola vez por cliente
└───────────────────────────┬───────────────────────────────────┘
                            │ 1
                            ▼
┌───────────────────────────────────────────────────────────────┐
│  cobro_suscripcion  (intento de cobro)                        │
│  ─────────────────────────────────────────                    │
│  cobro_suscripcion_id (PK uuid)                               │
│  cliente_suscripcion_id (FK -> cliente_suscripcion) CASCADE   │
│  cobro_monto                                                  │
│  cobro_estado (pendiente|exitoso|fallido)                     │
│  cobro_intento_numero (1..3)                                  │
│  cobro_resultado_pasarela (aprobado|rechazado|timeout) NULL   │
│  cobro_fecha                                                  │
└───────────────────────────────────────────────────────────────┘
```

### Tablas de framework (creadas por las migraciones)

- **`jobs`**: cola de Laravel (driver `database`). Aquí se encolan los jobs `NotificarWebhookGateway`.
- **`failed_jobs`**: jobs que fallaron (reintentos agotados o errores).
- **`cache`**: almacén de caché (driver `database`).

### Migraciones (en orden de ejecución)

| Archivo | Tabla |
|---------|-------|
| `2026_08_29_191956_create_clientes_table.php` | `cliente` |
| `2026_08_30_030515_create_suscripcion_table.php` | `suscripcion` |
| `2026_08_30_030516_create_cliente_suscripcion_table.php` | `cliente_suscripcion` |
| `2026_08_30_030518_create_cobro_suscripcion_table.php` | `cobro_suscripcion` |
| `2026_09_01_194032_create_jobs_table.php` | `jobs` |
| `2026_09_01_202158_create_failed_jobs_table.php` | `failed_jobs` |
| `2026_09_01_205059_create_cache_table.php` | `cache` |

### Convenciones de nombres

Los campos usan prefijo de tabla (`cliente_nombre`, `suscripcion_precio`, `cobro_estado`, ...) para hacerlo explícito en una API con UUIDs. El precio y el monto son **enteros** (unidades de moneda, sin decimales; para este ejercicio se asume moneda sin centavos).

---

## Reglas de dominio del motor de cobro

Este es el corazón del problema y lo que evalúa el criterio de dominio:

1. **Solo se cobran suscripciones en estado `activa`** y cuya `fecha_proximo_cobro` sea `null` (primer cobro) o ya haya vencido (`<= now()`).

2. **Cada intento de cobro nace `pendiente`** y solo cambia a `exitoso` / `fallido` cuando llega la confirmación del webhook de la pasarela.

3. **La pasarela simulada responde: aprobado 60 %, rechazado 30 %, timeout 10 %.** Tras resolver, notifica el resultado llamando al webhook `POST /api/webhooks/gateway`.

4. **Intentos fallidos se reintentan hasta 3 veces**, con intervalo entre reintento y reintento configurable (`motor.reintento.intervalo_minutos`, por defecto 2 minutos). El número de intento se incrementa: intento 1 → 2 → 3.

5. **Al tercer intento fallido, la suscripción pasa a `pausada`** y `fecha_proximo_cobro` queda `null`. Para volver a cobrarla, se cambia el estado a `activa` (edición del estado).

6. **Un cobro aprobado** deja la suscripción en `activa`, actualiza `fecha_ultimo_cobro` y calcula `fecha_proximo_cobro` sumando los minutos de `config/motor.php` según la periodicidad: **`mensual` → +30 minutos** (simula 1 mes) y **`anual` → +35 minutos** (simula 1 año). Deliberadamente no son 30 días ni 365: son minutos ajustables para poder ver el re-cobro en una demo.

7. **Guarda contra pendientes "pegados" (timeout real):** si una solicitud de cobro quedó `pendiente` sin respuesta de la pasarela (webhook nunca llegó) y supera `motor.cobro.timeout_min` (2 min), el motor la marca como `fallido = timeout` y sigue con el ciclo. Esto protege de que un cobro se quede "colgado" para siempre.

8. **No se crea un intento nuevo mientras exista uno `pendiente` no pegado** — evita dobles cobros cuando se ejecuta el motor varias veces seguidas o manualmente.

9. **El webhook es idempotente:** si ya llega una confirmación para un intento con estado final, se ignora. Se procesa dentro de una **transacción con bloqueo de fila** (`lockForUpdate`) para evitar que dos confirmaciones simultáneas dupliquen efectos.

### Ubicación de la lógica

- Motor principal: `app/Services/CobroMotorService.php` (`ejecutar()` para el ciclo masivo, `cobrarSuscripcion()` para el cobro puntual).
- Pasarela simulada: `app/Services/GatewaySimulatorService.php`.
- Confirmación del webhook: `app/Services/GatewayWebhookService.php`.
- Job asíncrono: `app/Jobs/NotificarWebhookGateway.php`.

---

## Cómo simular el paso del tiempo

Los tiempos son parámetros configurables en **`backendLaravelApi/config/motor.php`**, expresados en **minutos** para poder probar el ciclo completo en minutos y no en meses:

```php
'reintento' => [
    'max_intentos'    => 3,   // máximo de intentos por ciclo
    'intervalo_minutos' => 2, // [SIMULA 24 h] espera entre un intento y el siguiente
],
'periodos' => [
    'mensual' => 30,          // [SIMULA 1 mes] frecuencia de cobro mensual
    'anual'   => 35,          // [SIMULA 1 año] frecuencia de cobro anual
],
'cobro' => [
    'timeout_min' => 2,       // tiempo sin respuesta de la pasarela para dar por "timeout"
],
```

### Cómo probar la secuencia de reintentos sin esperar 3 días

1. Asigne una suscripción `activa` a un cliente (desde la UI o la API).
2. Ejecute el motor forzando rechazos:

```powershell
# intento 1 -> rechazado
php artisan cobro:ejecutar --resultado=rechazado

# el motor programa fecha_proximo_cobro = ahora + intervalo_minutos (2 min)
# intento 2 -> rechazado
sleep 120 ; php artisan cobro:ejecutar --resultado=rechazado

# intento 3 -> rechazado -> la suscripción pasa a PAUSADA
sleep 120 ; php artisan cobro:ejecutar --resultado=rechazado
```

3. Confirme en la interfaz (o en la BD) que el historial tiene 3 intentos `fallido` con `cobro_intento_numero` 1, 2 y 3, y que la suscripción quedó **`pausada`**.

> **Atajo para pruebas rápidas:** el endpoint de **cobro puntual** `POST /api/cliente-suscripciones/{id}/cobrar` **no** respeta `fecha_proximo_cobro`; permite forzar los 3 reintentos en segundos. Con `schedule:work` corriendo, el ciclo automático sí respeta el intervalo.

### Cómo se aplica en cobros exitosos

Tras un cobro exitoso: `fecha_proximo_cobro = fecha_ultimo_cobro + periodos.<mensual|anual>` (30/35 min por defecto). Con `mensual = 1` / `anual = 2`, la siguiente corrida del scheduler (cada 1 min) ve la fecha vencida y vuelve a cobrar sola.

### Ajustar la duración de los periodos para la demo

Si quiere ver el refinanciamiento automático (cobro mensual/anual), ponga `mensual => 1` y `anual => 2` en `config/motor.php` para que la siguiente fecha de cobro llegue a los 1–2 minutos y el scheduler dispare el siguiente cobro.

---

## Cómo forzar el resultado del simulador

Existen **5 vías** (todas opcionales; si no se indica resultado, la pasarela responde aleatoriamente 60/30/10):

### 1. Desde la interfaz (3 lugares)

El resultado se puede forzar desde el frontend en tres puntos:

- **Al crear un cliente** (sección *Clientes* → *Nuevo cliente*): si se selecciona *Suscripción (opcional)*, aparece el campo **Resultado del cobro** para forzar el primer cobro de esa asignación.
- **Al asignar una suscripción** (detalle del cliente → *Añadir suscripción*): aparecen los mismos campos *Suscripción* y **Resultado del cobro**.
- **En el módulo *Suscripciones de clientes***: el selector **Resultado del cobro** + el botón **Ejecutar cobro** fuerzan el resultado para toda la corrida del motor.

En los tres casos, el resultado aplica a los cobros generados en ese momento; si el cliente se crea sin suscripción, se le puede asignar una más adelante desde su detalle.

### 2. Comando de consola (la más directa)

```powershell
php artisan cobro:ejecutar --resultado=aprobado   # o rechazado | timeout
```

### 3. Endpoint del motor

```bash
curl -X POST http://127.0.0.1:8000/api/cobro/ejecutar \
  -H "Content-Type: application/json" \
  -d '{"resultado":"rechazado"}'
```

### 4. Cobro puntual de una suscripción (con su UUID)

```bash
curl -X POST http://127.0.0.1:8000/api/cliente-suscripciones/{cliente_suscripcion_id}/cobrar \
  -H "Content-Type: application/json" \
  -d '{"resultado":"timeout"}'
```

### 5. Directo contra la pasarela simulada (con su UUID)

```bash
curl -X POST http://127.0.0.1:8000/api/pasarela/cobrar \
  -H "Content-Type: application/json" \
  -d '{"cobro_suscripcion_id":"{cobro_suscripcion_id}","resultado":"aprobado"}'
```

> **Nota sobre `timeout`:** en este simulador, al forzar `timeout` la pasarela **sí notifica** el webhook con ese resultado y el motor lo trata como intento `fallido` (evidencia de negocio registrada como `cobro_resultado_pasarela = timeout`). El caso de "la pasarela no responde **jamás**" está cubierto por la guarda de pendientes pegados: si el webhook nunca llega (por ejemplo, el worker de cola está detenido), un intento `pendiente` que supere `cobro.timeout_min` se marca `fallido/timeout` en la siguiente corrida del motor.

Los valores válidos son siempre: `aprobado`, `rechazado`, `timeout`.

---

## Endpoints de la API

Todas las rutas están en `routes/api.php` y responden con el mismo formato:

```json
{
  "success": true,
  "statusCode": 200,
  "message": "mensaje",
  "data": { }
}
```

Los errores (validación, 404, duplicados, 500) usan `success: false` con el mismo cuerpo y el código HTTP correspondiente. El manejo central de excepciones está en `bootstrap/app.php`.

### Clientes

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/clientes` | Lista clientes (con conteo de suscripciones). |
| POST | `/api/clientes` | Crea cliente. |
| GET | `/api/clientes/{cliente}` | Detalle del cliente + sus **suscripciones** con sus cobros. |
| PUT | `/api/clientes/{cliente}` | Edita cliente. |
| DELETE | `/api/clientes/{cliente}` | Elimina cliente. |

Body de creación/edición:

```json
{
  "cliente_nombre": "Ana Gómez",
  "cliente_correo": "ana@example.com",
  "cliente_documento": "1234567890",
  "cliente_telefono": "3001234567"
}
```

Reglas: nombre obligatorio (max 100), correo único y formato email (max 150), documento **exactamente 10 dígitos** y único, teléfono **exactamente 10 dígitos**.

### Planes de suscripción (`suscripcion`)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/suscripciones` | Lista suscripciones. |
| POST | `/api/suscripciones` | Crea plan. |
| GET | `/api/suscripciones/{suscripcion}` | Detalle del plan. |
| PUT | `/api/suscripciones/{suscripcion}` | Edita plan. |
| DELETE | `/api/suscripciones/{suscripcion}` | Elimina plan. |

Body:

```json
{
  "suscripcion_nombre": "Plan Premium",
  "suscripcion_descripcion": "Acceso ilimitado",
  "suscripcion_precio": 50000,
  "suscripcion_periodo": "mensual"
}
```

`periodo`: `mensual` o `anual`. Precio entero > 0.

### Suscripciones del cliente (asignación)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/cliente-suscripciones` | Lista asignaciones (con cliente y plan). |
| POST | `/api/cliente-suscripciones` | Asigna un plan a un cliente. |
| GET | `/api/cliente-suscripciones/{id}` | Detalle con **historial de intentos de cobro**. |
| PUT | `/api/cliente-suscripciones/{id}` | Edita (cambiar estado, etc.). |
| GET | `/api/cliente-suscripciones/{id}/cobros` | Historial de cobros. |
| DELETE | `/api/cliente-suscripciones/{id}` | Remueve la asignación. |
| POST | `/api/cliente-suscripciones/{id}/cobrar` | **Cobro puntual** de esa suscripción (acepta `resultado` forzado). |

Body de creación:

```json
{
  "cliente_id": "<uuid del cliente>",
  "suscripcion_id": "<uuid del plan>",
  "estado_cliente_suscripcion": "activa"
}
```

Al asignar, `fecha_proximo_cobro` se inicializa en `now()`, de modo que la primera ejecución del motor cobra de inmediato. Un cliente **no puede tener el mismo plan dos veces** (`UNIQUE(cliente_id, suscripcion_id)` → 409).

### Motor y pasarela

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/cobro/ejecutar` | Ejecuta el motor sobre todas las suscripciones activas que corresponda. Body opcional `{"resultado":"aprobado"|"rechazado"|"timeout"}`. |
| POST | `/api/pasarela/cobrar` | Simula la pasarela: procesa un intento por `cobro_suscripcion_id` y **encola la notificación** al webhook. |
| POST | `/api/webhooks/gateway` | **Webhook** de confirmación. Recibe `{"cobro_suscripcion_id":"...","resultado":"aprobado"|"rechazado"|"timeout"}` y actualiza el intento y la suscripción. |

Respuesta del motor:

```json
{
  "success": true,
  "statusCode": 200,
  "message": "Motor de cobro ejecutado exitosamente",
  "data": {
    "procesadas": 1,
    "aprobadas": 1,
    "rechazadas": 0,
    "tiempo_expirado": 0
  }
}
```

### Colección de Postman

El repositorio incluye **`motor_suscripciones.postman_collection.json`** (raíz) con **todos los endpoints ya configurados**. Para usarla: *Postman → Import → seleccionar el archivo*.

Viene preparada con **variables de la colección** para facilitar el envío de datos entre requests:

- **`localhostapi`** → es la **URL base**; antes de probar, defínala como `http://127.0.0.1:8000/api` (variable de la colección → valor inicial/actual). Recuerde que el valor `http://127.0.0.1:8000` se define sobre todo como **variable de entorno `APP_URL`** en `backendLaravelApi/.env` (`APP_URL=http://127.0.0.1:8000`) — es la que usa el job `NotificarWebhookGateway` para disparar el webhook. Si cambia de puerto o usa la IP de la red, ajuste ambos valores (`.env` y variable de Postman), sin tocar cada request.
- **`cliente_id`**, **`suscripcion_id`** y **`cliente_suscripcion_id`** → guardan los UUID que devuelve cada creación, así los endpoints que dependen de un ID (detalle, edición, cobro) funcionan sin reescribir URLs a mano.

> Dos avisos de la colección: la variable `localhostapi` está vacía por defecto (Fill en *Collection variables*) y los requests *"simulador pasarela"* y *"webhooks pasarela"* traen un **UUID de ejemplo** (`01a05962-...`) que debe reemplazarse con un `cobro_suscripcion_id` real de la BD. Y recuerde: use `127.0.0.1`, no `localhost` (ver sección [Consideraciones](#consideraciones-para-el-técnico-que-revisa)).

---

## Flujo de punta a punta (paso a paso)

Con los 4 procesos corriendo y la SPA en `http://127.0.0.1:5173`:

1. **Crear un cliente** → sección *Clientes* → *Nuevo cliente*. Es **opcional** crearlo y asignarle de una vez una suscripción (seleccionando *Suscripción (opcional)* y, si se desea, forzar el resultado del primer cobro); si no, la suscripción se puede **asignar después** desde el detalle del cliente → *Añadir suscripción* (paso 3).
2. **Crear un plan** → sección *Planes de Suscripción* → *Nueva suscripción* (nombre, precio, periodo).
3. **Asignar el plan al cliente** → *Clientes* → en el cliente, *Suscripciones* → *Añadir suscripción* (opcional: forzar el resultado del primer cobro).
4. **Ejecutar el motor** (cualquiera de estos):
   - *Suscripciones de clientes* → *Ejecutar cobro* (con resultado automático o forzado); o
   - `php artisan cobro:ejecutar`; o
   - esperar el **scheduler** (cada 1 minuto).
5. Ver el **historial de intentos** → en la fila de la suscripción, *Detalle cobro*. Cada corrida deja su intento con número, fecha y resultado.
6. Ver el **resultado en la suscripción**: si fue aprobado, `fecha_ultimo_cobro` se actualiza y se programa `fecha_proximo_cobro`; si fue rechazado/timeout en el tercer intento, el estado cambia a **`pausada`**.

> El worker de cola (terminal 2) es esencial: el webhook de confirmación se envía mediante un **job asíncrono**, así que si el worker no está corriendo, los intentos quedan `pendiente` hasta que se marquen como timeout o se procesen.

---

## Datos de prueba

El seeder actual (`database/seeders/DatabaseSeeder.php`) está vacío a propósito: **no hay usuarios** en este proyecto y los datos se crean desde la interfaz o por API. Para poblar rápido, ejecute:

```bash
# 1. Cliente
curl -X POST http://127.0.0.1:8000/api/clientes \
  -H "Content-Type: application/json" \
  -d '{"cliente_nombre":"Ana Gómez","cliente_correo":"ana@example.com","cliente_documento":"1234567890","cliente_telefono":"3001234567"}'

# 2. Dos suscripciones (mensual y anual)
curl -X POST http://127.0.0.1:8000/api/suscripciones \
  -H "Content-Type: application/json" \
  -d '{"suscripcion_nombre":"Plan Premium","suscripcion_descripcion":"Acceso ilimitado","suscripcion_precio":50000,"suscripcion_periodo":"mensual"}'

curl -X POST http://127.0.0.1:8000/api/suscripciones \
  -H "Content-Type: application/json" \
  -d '{"suscripcion_nombre":"Plan Anual","suscripcion_descripcion":"Pago anual","suscripcion_precio":500000,"suscripcion_periodo":"anual"}'

# 3. Asignar el plan al cliente (tome los UUID de las respuestas anteriores)
curl -X POST http://127.0.0.1:8000/api/cliente-suscripciones \
  -H "Content-Type: application/json" \
  -d '{"cliente_id":"<uuid_cliente>","suscripcion_id":"<uuid_plan>","estado_cliente_suscripcion":"activa"}'

# 4. Ejecutar el motor
curl -X POST http://127.0.0.1:8000/api/cobro/ejecutar \
  -H "Content-Type: application/json" \
  -d '{}'
```

---

## Herramientas utilizadas y por qué

### Backend

- **Jobs + cola con driver `database`**: el punto 4 de la prueba pide que la pasarela "notifique el resultado llamando al webhook". Esa notificación es un **efecto secundario asíncrono**: el simulador encola `NotificarWebhookGateway` y responde de inmediato, sin bloquear el request con el HTTP hacia el webhook. Esto refleja el patrón real de pasarelas y desacopla el procesamiento. la cola vive en MySQL y solo necesita las migraciones `jobs`/`failed_jobs`.

- **Scheduler + comando `cobro:ejecutar` cada 1 minuto**: el motor debe "ejecutarse varias veces seguidas" Laravel Scheduler (`routes/console.php`: `Schedule::command('cobro:ejecutar')->everyMinute()`) es la forma portable de hacer tareas recurrentes. El comando `php artisan cobro:ejecutar` también permite correrlo a mano, lo que facilita las demos y las pruebas.

- **Idempotencia del webhook**: solo procesa intentos en `pendiente`; las confirmaciones duplicadas se ignoran.

- **UUIDs como llaves primarias**: no exponen conteo de registros y son robustos para APIs consumidas por terceros y sistemas distribuidos.

- **Form Requests por endpoint** (`app/Http/Requests/`): validación centralizada, mensajes en español y respuestas 422 consistentes.

- **`ApiResponse` + manejo global de excepciones** (`bootstrap/app.php`): toda respuesta (éxito, validación, 404, duplicado, 500) tiene el mismo contrato JSON.

- **Ajuste de tiempos por configuración** (`config/motor.php`): reintentos, periodo y timeout en minutos → permite "simular el tiempo" sin tocar código.

### Frontend

- **Ant Design**: componentes listos (tablas, modales, formularios, tags) que aceleran el CRUD y dan estados visuales claros.
- **TanStack Query**: caché, carga/error por vistas e invalidación automática tras mutaciones (ej.: al ejecutar el motor, se refrescan las vistas de suscripciones).
- **React Hook Form + controllers**: formularios controlados con reglas espejo de las del backend.
- **React Router**: vistas de listados, detalle de cliente y detalle de suscripción con historial.
- **Tailwind CSS**: estilos utilitarios para el layout general.

---

## Consideraciones para el técnico que revisa

- **El orden de arranque importa**: primero el backend (`php artisan serve`), después `queue:work` (sin worker no se resuelven los cobros), después `schedule:work` y por último el frontend.
- **`QUEUE_CONNECTION=database`**: si borra tablas `jobs`/`failed_jobs`, `php artisan migrate` las recrea. Es la razón de que existan estas migraciones además de las de dominio.
- **Use `127.0.0.1`, no `localhost`**: el cambio principal es la variable de entorno `APP_URL=http://127.0.0.1:8000` en `backendLaravelApi/.env` (la que usa el job `NotificarWebhookGateway` para el webhook) y, en espejo, `http://127.0.0.1:8000/api` en el frontend `src/services/api.js`. El POST del webhook se envía desde el worker de cola usando `APP_URL`; con `localhost` en Windows el cliente intenta primero IPv6 (`::1`), falla y hace *fallback* a IPv4, volviendo **lenta la notificación del webhook**. Con `127.0.0.1` la resolución es inmediata. Si se consume desde otro equipo, use la IP LAN (`http://192.168.x.x:8000`) y levante la API con `php artisan serve --host=0.0.0.0 --port=8000`.
- **Puertos**: API en `8000`, SPA en `5173`. Si ocupa uno de esos puertos, cámbielo con `php artisan serve --port=XXXX` y actualice `src/services/api.js` y, de ser necesario, `APP_URL` en `.env`. **Importante:** el job `NotificarWebhookGateway` genera la URL del webhook con `APP_URL` (porque corre fuera de un request HTTP). Si la API se sirve en otro puerto, el `.env` debe reflejarlo, o el webhook apuntará al puerto equivocado.
- **CORS**: el frontend llama del `5173` al `8000`. Laravel 12 no trae `config/cors.php` pero el middleware global usa los defaults (`*`) — suficiente para desarrollo local.
- **La pasarela simulada responde HTTP 200 inmediatamente** y devuelve "aprobado/rechazado/timeout" solo como respuesta de la solicitud; el estado **final** del intento lo decide el webhook. Es un comportamiento intencional para respetar el flujo de la prueba (punto 4 y 5).
- **El `timeout` forzado actúa como rechazo** (intento fallido con `cobro_resultado_pasarela=timeout`). El caso "no responde nada" se cubre con la guarda de pendientes pegados.
- **`fecha_proximo_cobro` y cola**: el scheduler cobra cada 1 minuto, pero solo "toca" suscripciones activas cuya fecha de cobro ya venció. Crear/editar una suscripción con `fecha_proximo_cobro` futuro retrasa deliberadamente el cobro.

- **Reinicio limpio** (si quiere probar desde cero):

```powershell
cd backendLaravelApi
php artisan migrate:fresh
```

---

## Notas sobre el uso de inteligencia artificial

Tal como pide la prueba, documento el uso de IA de forma honesta:

- **Qué se le pidió**: que propusiera la arquitectura para un motor de cobros recurrentes en Laravel con reintentos, webhook y pasarela simulada, y que explicara cómo disparar el ciclo automáticamente en el proyecto.
- **Qué sugirió la IA**: utilizar **Jobs de cola + un comando de consola programado con Laravel Scheduler cada minuto** para el ciclo de cobro automático.
- **Qué decidí hacer y por qué**: **aprobé el enfoque del Job y del scheduler**, pero **solo después de validarlo contra la documentación oficial y ejemplos que ya conocía**: el driver de cola `database` de Laravel (documentado en *Laravel Queues*), el patrón `Schedule::command(...)->everyMinute()` (documentado en *Laravel Scheduling* — en producción un cron `* * * * * php artisan schedule:run`, en desarrollo `php artisan schedule:work`) y el flujo típico de pasarelas (crear cargo → pasarela responde → notificación webhook → actualizar estado). La propuesta de IA coincidía con ese patrón estándar, por lo que la adopté y la ajusté a este caso: el job no reintenta por sí mismo el pago (los reintentos son de **dominio** del motor y viven en `CobroMotorService`), sino que solo transporta la notificación del resultado al webhook.
- **Qué descarté de la IA y por qué**:
  - Me sugirió tablas bajo convención plural y timestamps automáticos como única PK incremental. Lo ajusté a **UUIDs** y a los nombres con prefijo de la prueba existente, para mantener consistencia con el código ya escrito y la API.
  - Me sugirió reintentos de pago en 24 h usando el `backoff` del job de cola. Lo descarté: el intervalo entre cobros es una regla de **negocio** (se debe poder simular rápidamente), así que vive en `config/motor.php` (`intervalo_minutos`) y no en la configuración de reintentos de la cola.

En resumen: la IA se usó como asesor técnico, se verificó cada propuesta contra la documentación y ejemplos propios, y se adaptó el resultado al dominio concreto (dinero recurrente, idempotencia de webhook).

---

## Trabajo pendiente y mejoras

- **Integración real con una pasarela sandbox** (Wompi/PayU/ePayco): el simulador ya aísla la lógica en `GatewaySimulatorService`, por lo que reemplazar su implementación por llamadas HTTP reales al sandbox y exponer el webhook público serían cambios acotados.

---
