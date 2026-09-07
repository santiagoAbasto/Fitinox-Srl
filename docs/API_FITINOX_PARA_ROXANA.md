# Fitinox — integración con el sistema de gestión

## Estado

La API está publicada y validada en producción.

- Documentación Swagger: `https://fitinox.osole.com.ar/integracion/api`
- Especificación OpenAPI 3.1: `https://fitinox.osole.com.ar/api/openapi.json`
- Base URL: `https://fitinox.osole.com.ar/api/v1/integration`
- Zona horaria operativa: Argentina, UTC−03:00. Enviar fechas ISO 8601 con offset, por ejemplo: `2026-09-07T12:01:44-03:00`.

## Autenticación

Todas las llamadas requieren HTTPS y este encabezado:

```http
Authorization: Bearer TOKEN_PRIVADO
Content-Type: application/json
Accept: application/json
```

El token se entrega por un canal privado separado. No guardarlo en código fuente, repositorios, planillas, capturas ni correos. En Swagger, pegar sólo el valor del token en **Authorize**; Swagger agrega `Bearer` automáticamente.

## Alcance de la integración

| Sentido | Información |
| --- | --- |
| Sistema de gestión → web | Artículos, stock y clientes. |
| Web → sistema de gestión | Altas/cambios de clientes, presupuestos y pedidos web. |
| Sistema de gestión → web | Estado, referencia interna y fecha de entrega de pedidos web. |

No hay acceso directo a la base de datos de la web.

## Prueba de conectividad

```http
GET /health
```

Respuesta esperada:

```json
{
  "ok": true,
  "service": "fitinox-legacy-integration",
  "version": "v1",
  "server_time": "2026-09-07T12:01:44-03:00"
}
```

## 1. Enviar artículos y stock

```http
POST /articles/upsert
Idempotency-Key: articulos-20260907-0001
```

Enviar como máximo 500 registros por solicitud.

```json
{
  "generated_at": "2026-09-07T12:00:00-03:00",
  "records": [
    {
      "cod_articulo": "TU-SC6LB-04-09",
      "configuracion": "Tubo sin costura AISI 316",
      "modelo": "TUBOS",
      "TipoArticulo": "B",
      "fabricacion": "I",
      "categoria": "Articulo",
      "stock": 4455.2,
      "UnMedida": "m",
      "actualizado_en": "2026-09-07T12:00:00-03:00"
    }
  ]
}
```

Reglas:

- `cod_articulo` es obligatorio y debe coincidir exactamente con el código de variante existente en la web para actualizar el stock.
- Los artículos recibidos que aún no existen en el catálogo web quedan registrados, pero no se crean ni publican automáticamente.
- `stock` acepta tres decimales. El sistema de gestión es la fuente de verdad: los artículos compuestos no se fuerzan a cero, para respetar devoluciones u otras excepciones.
- Enviar `actualizado_en` por registro o `generated_at` por lote. La web no aplica datos anteriores a la última actualización recibida.

## 2. Enviar clientes

```http
POST /clients/upsert
Idempotency-Key: clientes-20260907-0001
```

```json
{
  "generated_at": "2026-09-07T12:00:00-03:00",
  "records": [
    {
      "cod_cliente": "1737",
      "razon_social": "ACEROS DAHIR S.A.",
      "email": "ventas@acerosdahir.com",
      "Nro_documento": "30-711503362"
    }
  ]
}
```

Reglas:

- Máximo 500 registros por solicitud.
- `cod_cliente` y `razon_social` son obligatorios.
- El campo `email` puede traer texto adicional: la web conserva el original y extrae direcciones válidas.
- La sincronización no crea usuarios ni contraseñas para la Zona Clientes.
- Sólo vincula una cuenta web ya existente si coincide exactamente por email válido o CUIT/documento.

## 3. Idempotencia y reintentos

Los dos `POST` anteriores requieren `Idempotency-Key`.

- Para un envío nuevo, generar una clave nueva y única.
- Si hay timeout, caída de red o respuesta `5xx`, reenviar el mismo cuerpo con la misma clave.
- La repetición correcta devuelve `Idempotency-Replayed: true` y no duplica cambios.
- No reutilizar una misma clave con contenido diferente: devuelve `409 idempotency_conflict`.

## 4. Obtener eventos generados en la web

```http
GET /events?after_id=0&limit=100
```

La web entrega eventos pendientes ordenados por `id`:

- `client.registered`
- `client.updated`
- `quote.created`
- `order.created`
- `order.updated`

Flujo requerido:

1. Leer los eventos en orden.
2. Guardar cada `uuid` localmente y procesarlo una sola vez.
3. Cuando el sistema de gestión persistió el evento correctamente, confirmarlo.
4. Si falla, no confirmarlo ni avanzar el cursor más allá de ese evento.

Confirmación:

```http
POST /events/acknowledge
```

```json
{
  "event_uuids": ["uuid-del-evento-procesado"]
}
```

Se admiten hasta 200 eventos por lectura o confirmación.

## 5. Actualizar un pedido de la web

```http
PATCH /orders/{numero_web}
```

Ejemplo:

```json
{
  "external_reference": "PV-15468",
  "status": "processing",
  "delivery_date": "2026-09-12"
}
```

Estados permitidos:

```text
received | processing | quoted | completed | cancelled
```

Esta actualización no vuelve a emitir un evento, evitando ciclos entre sistemas.

## Errores y reintentos

| HTTP | Acción |
| --- | --- |
| `200` | Operación exitosa. Registrar respuesta y continuar. |
| `401` | Detener el conector y revisar el token. |
| `404` | Registrar el pedido inexistente para revisión; no reintentar indefinidamente. |
| `409` | Crear una Idempotency-Key nueva para un envío nuevo. |
| `422` | Corregir los campos indicados antes de reenviar. |
| `429` | Esperar y aplicar reintento con pausa progresiva. Límite: 120 solicitudes por minuto. |
| `5xx` | Reintentar el mismo POST con la misma Idempotency-Key. |

## Puesta en marcha recomendada

1. Probar `GET /health` desde Swagger o el conector.
2. Enviar un solo artículo de prueba con un código que exista en la web y verificar el stock.
3. Enviar un solo cliente de prueba.
4. Generar un presupuesto o pedido web y confirmar que se obtiene y confirma como evento.
5. Activar la sincronización periódica cada cinco minutos inicialmente.

Para el detalle exhaustivo de cada campo, ejemplos y schemas, usar Swagger como fuente oficial del contrato.
