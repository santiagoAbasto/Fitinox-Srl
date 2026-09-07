# Integración con el sistema de gestión Fitinox

## Objetivo

La integración une el sistema de gestión con la web mediante HTTPS y JSON.

- El sistema de gestión envía clientes y artículos. La web conserva el registro recibido y actualiza el stock del catálogo cuando el código de artículo ya existe.
- La web deja pedidos de clientes, solicitudes de presupuesto y altas o cambios de clientes en una bandeja de salida. El sistema de gestión los consulta, los procesa y confirma cada evento.
- El sistema de gestión puede actualizar el estado, la fecha de entrega y el número de referencia de un pedido web.

La web no crea productos públicos ni cuentas de acceso para clientes a partir de una importación. Esto evita publicar información o habilitar accesos por error.

## Seguridad

Todas las llamadas requieren HTTPS y el encabezado Authorization: Bearer con el valor definido en FITINOX_INTEGRATION_TOKEN.

Definir un token aleatorio de al menos 32 bytes en el archivo .env de producción:

    FITINOX_INTEGRATION_TOKEN=pegar-aqui-un-token-largo-y-secreto

Nunca incluir este token en el programa cliente, repositorios, capturas o correos. Debe quedar en la configuración privada del ejecutable o servicio que realiza la sincronización.

La URL base de producción es:

    https://fitinox.osole.com.ar/api/v1/integration

## Documentación interactiva

La documentación OpenAPI 3.1 se publica en:

    https://fitinox.osole.com.ar/integracion/api

Desde esa pantalla se puede revisar el contrato, descargar la especificación y probar ejemplos en un entorno autorizado. El token nunca queda guardado en el navegador.

## Generación del token

Para generar un token sin modificar archivos:

    php artisan integration:token

Para generarlo y escribirlo en el archivo .env:

    php artisan integration:token --write

Para rotar un token existente de forma explícita:

    php artisan integration:token --write --force

Después de cambiar el token, ejecutar php artisan optimize:clear. Actualizar el token del conector antes de que se reanude la sincronización.

## Mapeo de la muestra recibida

### Artículos

| Sistema de gestión | API | Uso en la web |
| --- | --- | --- |
| cod_articulo | cod_articulo | Identificador único y vínculo con el código de variante web. |
| configuracion | configuracion | Descripción proveniente del sistema. |
| modelo | modelo | Familia o modelo. |
| TipoArticulo | TipoArticulo | Conservado como dato del sistema. |
| fabricacion | fabricacion | Conservado como dato del sistema. |
| categoria | categoria | Conservado como dato del sistema. |
| stock | stock | Actualiza stock físico sólo si existe el mismo código en la web. |
| UnMedida | UnMedida | Unidad, por ejemplo Un o m. |

El stock acepta hasta tres decimales. Esto permite valores como 4455.2 m para tubos. No se infiere que un artículo compuesto no tiene stock: el valor enviado por el sistema de gestión es la fuente de verdad. Así, si existe una devolución u otra excepción, el dato se conserva correctamente.

### Clientes

| Sistema de gestión | API | Uso en la web |
| --- | --- | --- |
| cod_cliente | cod_cliente | Identificador único del cliente en el sistema de gestión. |
| razon_social | razon_social | Razón social sincronizada. |
| email | email | Se guarda el texto original y se extraen direcciones válidas. |
| Nro_documento | Nro_documento | CUIT/documento, conservado como texto. |

Muchos clientes pueden no tener email o tener observaciones mezcladas con el correo. Por eso se almacenan, pero no se crea un usuario de la Zona Clientes. Si existe una cuenta de cliente web y coincide exactamente por un email válido o por CUIT, se vincula y se actualizan su razón social y CUIT.

## Envío desde el sistema de gestión a la web

Enviar lotes de hasta 500 registros. Cada POST debe incluir el encabezado Idempotency-Key con una clave única. Si la conexión se corta, repetir el mismo envío con la misma clave: la web devuelve la respuesta original y no duplica cambios.

### Artículos

POST /articles/upsert

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

La respuesta informa processed, created, updated, unchanged, stale, linked y unlinked. linked indica que el código ya existe en la web y su stock fue actualizado. unlinked indica que el artículo quedó registrado para auditoría, pero no existe una variante pública con ese código; no se publica ni crea automáticamente.

### Clientes

POST /clients/upsert

    {
      "records": [
        {
          "cod_cliente": "2",
          "razon_social": "ALTO PARANA S.A.",
          "email": "mmorra@altoparana.com (Sr. Miguel Morra)",
          "Nro_documento": "33-56324011-9",
          "actualizado_en": "2026-09-07T12:00:00-03:00"
        }
      ]
    }

Para consultar registros recibidos:

    GET /articles?after_id=0&limit=100
    GET /clients?after_id=0&limit=100

Usar next_after_id de la respuesta para continuar con la siguiente página.

## Envío desde la web al sistema de gestión

El sistema de gestión debe consultar periódicamente:

    GET /events?after_id=0&limit=100

Los tipos actuales son:

- client.registered: una persona solicitó una cuenta de cliente.
- client.updated: el administrador modificó o aprobó una cuenta.
- quote.created: se generó una solicitud pública de presupuesto.
- order.created: un cliente envió un pedido desde su área privada.
- order.updated: el administrador modificó estado o entrega de un pedido.

Cada evento tiene un uuid permanente. Procesarlo una sola vez según ese uuid y, sólo después de guardarlo correctamente en el sistema de gestión, confirmarlo:

    POST /events/acknowledge

    { "event_uuids": ["4d92b79d-cd13-4f82-9d9a-9cd9a17f5742"] }

Si el sistema falla antes de confirmar, no enviar la confirmación. La web volverá a entregar ese evento en la próxima consulta. Esto evita pérdidas de pedidos o presupuestos.

## Actualización de pedidos desde el sistema de gestión

Una vez creado el pedido en el sistema de gestión, enviar su número de referencia, estado y fecha estimada:

    PATCH /orders/FC-20260907-00001

    {
      "external_reference": "PV-15468",
      "status": "processing",
      "delivery_date": "2026-09-12"
    }

Estados permitidos: received, processing, quoted, completed y cancelled.

## Secuencia recomendada

1. Publicar la migración y definir el token privado.
2. Probar GET /health desde la red donde corre el sistema de gestión.
3. Enviar uno o dos artículos conocidos y verificar linked.
4. Enviar uno o dos clientes y verificar que no se creen usuarios web inesperados.
5. Habilitar envíos periódicos de artículos y clientes.
6. Activar la consulta de eventos cada uno o dos minutos y confirmar cada evento tras guardarlo localmente.
