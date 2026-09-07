# Fitinox — guía de integración para Roxana

## Propósito

Esta integración conecta el sistema de gestión de Fitinox con la web sin acceso directo a la base de datos. La comunicación es por HTTPS y JSON, autenticada con un token Bearer privado.

La integración es bidireccional:

- **Sistema de gestión → web:** artículos, stock y clientes.
- **Web → sistema de gestión:** altas/cambios de clientes, solicitudes de presupuesto y pedidos de la Zona Clientes.
- **Sistema de gestión → web:** estado, referencia interna y fecha de entrega de cada pedido web.

## Documentación y URLs

| Recurso | URL de producción |
| --- | --- |
| Swagger / documentación interactiva | `https://fitinox.osole.com.ar/integracion/api` |
| Contrato OpenAPI 3.1 | `https://fitinox.osole.com.ar/api/openapi.json` |
| Base de la API | `https://fitinox.osole.com.ar/api/v1/integration` |
| Prueba de conexión | `GET /health` |

Swagger permite revisar campos, respuestas y ejemplos, y probar una llamada con el token desde **Authorize**. El navegador no guarda el token al recargar la pantalla.

## Seguridad

Todas las llamadas de negocio requieren:

```http
Authorization: Bearer TOKEN_PRIVADO
Content-Type: application/json
```

- Usar exclusivamente HTTPS.
- Guardar el token en la configuración protegida del conector, nunca en código fuente, Excel, correos ni capturas.
- Ante una respuesta `401`, detener la sincronización y revisar/rotar el token.
- El token se entrega por un canal privado distinto de esta documentación.

## Lo que debe hacer Fitinox antes de habilitar el conector

En el servidor web, una persona con acceso técnico debe ejecutar una sola vez:

```bash
php artisan migrate --force
php artisan integration:token --write
php artisan optimize:clear
```

El segundo comando muestra un token nuevo y lo guarda como `FITINOX_INTEGRATION_TOKEN` en `.env`. Debe entregarse a Roxana de forma segura. Para rotarlo conscientemente:

```bash
php artisan integration:token --write --force
php artisan optimize:clear
```

Antes de producción, hacer backup de base de datos y código. No ejecutar `migrate:fresh`, porque borra datos.

## Prueba inicial

Con el token recibido, probar:

```bash
curl -H "Authorization: Bearer TOKEN_PRIVADO" \
  https://fitinox.osole.com.ar/api/v1/integration/health
```

Respuesta esperada:

```json
{
  "ok": true,
  "service": "fitinox-legacy-integration",
  "version": "v1",
  "server_time": "2026-09-07T12:00:00-03:00"
}
```

## Sincronización del sistema de gestión hacia la web

### Artículos y stock

`POST /articles/upsert`

- Enviar lotes de hasta **500** registros.
- `cod_articulo` es obligatorio y debe coincidir exactamente con el código de variante de la web para actualizar su stock.
- Si el artículo aún no existe en el catálogo web, queda registrado como recibido pero **no** se crea ni publica automáticamente.
- `stock` acepta hasta tres decimales: por ejemplo, `4455.2` con unidad `m`.
- El sistema de gestión es la fuente de verdad del stock. Los compuestos no se fuerzan a cero: si hay una devolución u otra excepción, se respeta el valor enviado.
- Enviar `actualizado_en` por registro o `generated_at` por lote, en formato ISO 8601. La web descarta datos más viejos que el último recibido.

Ejemplo:

```json
{
  "generated_at": "2026-09-07T12:00:00-03:00",
  "records": [{
    "cod_articulo": "TU-SC6LB-04-09",
    "configuracion": "Tubo sin costura AISI 316",
    "modelo": "TUBOS",
    "TipoArticulo": "B",
    "fabricacion": "I",
    "categoria": "Articulo",
    "stock": 4455.2,
    "UnMedida": "m",
    "actualizado_en": "2026-09-07T12:00:00-03:00"
  }]
}
```

### Clientes

`POST /clients/upsert`

- Enviar lotes de hasta **500** registros.
- Campos obligatorios: `cod_cliente` y `razon_social`.
- La web conserva `email` tal como llega y extrae direcciones válidas si el campo trae observaciones.
- La sincronización **no crea usuarios ni contraseñas** para la Zona Clientes.
- Sólo vincula un usuario de la web ya existente si coincide de forma segura por email válido o CUIT/documento.

## Idempotencia y reintentos

Todo `POST` de artículos o clientes debe enviar un encabezado único:

```http
Idempotency-Key: articulos-20260907-0001
```

Si hay corte de red o timeout, reenviar exactamente el mismo cuerpo con la **misma** clave. La web devuelve el mismo resultado y el encabezado `Idempotency-Replayed: true`; no duplica registros ni actualizaciones.

No reutilizar una clave para un cuerpo distinto. Eso devuelve `409 idempotency_conflict`.

## Sincronización de la web hacia el sistema de gestión

El conector debe consultar periódicamente:

`GET /events?after_id=0&limit=100`

La respuesta contiene eventos pendientes como:

- `client.registered`
- `client.updated`
- `quote.created`
- `order.created`
- `order.updated`

Proceso obligatorio:

1. Leer los eventos pendientes ordenados por `id`.
2. Guardar/procesar cada `uuid` en el sistema de gestión una sola vez.
3. Sólo cuando la persistencia local terminó correctamente, confirmar los UUID recibidos:

```json
POST /events/acknowledge
{ "event_uuids": ["uuid-del-evento"] }
```

4. Si falla el procesamiento, no confirmar ese UUID: seguirá pendiente para el próximo intento.

Guardar localmente el `uuid` permite que el conector sea seguro aun si se corta después de importar un evento y antes de confirmarlo.

**Importante:** no avanzar `after_id` más allá del primer evento pendiente que falló. El conector debe procesar en orden; si necesita reanudar, puede volver a consultar desde el último ID confirmado (o desde `0`).

## Actualizar un pedido web desde el sistema de gestión

`PATCH /orders/{numero_web}`

Ejemplo:

```json
{
  "external_reference": "PV-15468",
  "status": "processing",
  "delivery_date": "2026-09-12"
}
```

Estados permitidos: `received`, `processing`, `quoted`, `completed`, `cancelled`.

Esta llamada actualiza la web y no vuelve a generar un evento, para evitar un bucle entre ambos sistemas.

## Errores y comportamiento esperado

| Código | Significado | Acción del conector |
| --- | --- | --- |
| `200` | Operación correcta | Guardar resultado y continuar. |
| `401` | Token ausente o incorrecto | Detener y avisar al responsable técnico. |
| `404` | Pedido inexistente | Registrar para revisión; no reintentar indefinidamente. |
| `409` | Clave de idempotencia usada con otro cuerpo | Generar una clave nueva para un envío nuevo. |
| `422` | Campo inválido | Registrar detalle, corregir origen y reenviar. |
| `429` | Límite temporal | Esperar y reintentar con pausa progresiva. |
| `503` | Integración aún no configurada | Confirmar que el token fue generado en el servidor y que se limpió la caché. |
| `5xx` | Error temporal del servidor | Reintentar el mismo POST con la misma `Idempotency-Key`. |

## Recomendación de operación

- Ejecutar la sincronización cada 5 minutos inicialmente.
- La API permite hasta 120 solicitudes por minuto; los lotes de entrada admiten hasta 500 registros y la lectura/confirmación de eventos hasta 200 por solicitud.
- Registrar request, respuesta, fecha, clave de idempotencia y UUID de evento procesado.
- No acceder ni escribir directamente en la base de datos de la web.
- Usar el contrato OpenAPI como fuente oficial de campos; ante una ampliación, versionar el conector antes de activarla.
- Empezar con un lote pequeño de prueba y validar resultados antes del primer envío masivo.

## Alcance entregado

La API ya incluye autenticación, limitación de solicitudes, idempotencia transaccional, registros de auditoría de sincronización, cola de salida confirmable, stock decimal, documentación Swagger/OpenAPI y pruebas automatizadas.
