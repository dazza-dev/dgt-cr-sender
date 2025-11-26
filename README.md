# DGT Sender 🇨🇷

Paquete para enviar documentos electrónicos (Factura, Nota crédito, Nota débito y Tiquete Electrónico) al Ministerio de Hacienda (Costa Rica).

## Instalación

```bash
composer require dazza-dev/dgt-cr-sender
```

## Uso

```php
use DazzaDev\DgtCrSender\Sender;

// Instanciar el sender
$sender = new Sender();

// Entorno de pruebas (true para pruebas, false para producción)
$sender->setTestMode(true);
```

### Token de acceso

```php
$token = $sender->auth('usuario_api', 'clave_api');
```

El método `auth` retorna un arreglo con `access_token` y `refresh_token`. El `access_token` tiene una duración de 5 minutos y el `refresh_token` de 10 horas. Si el `access_token` vence, puedes generar uno nuevo usando el `refresh_token` asi:

```php
$newToken = $sender->renewToken($token['refresh_token']);
```

Si vas a reutilizar los tokens en solicitudes futuras, almacénalos y configúralos en el `sender`:

```php
$sender->setBearerToken($token['access_token']);
$sender->setRefreshToken($token['refresh_token']);
```

Para cerrar la sesión al finalizar el proceso:

```php
$sender->logout($token['refresh_token']);
```

### Emisor y Receptor

Para realizar operaciones con los documentos electrónicos, debes configurar el emisor y receptor:

```php
// Setear Emisor
$sender->setIssuer([
    'identification_type' => '02',
    'identification_number' => 'identification_del_emisor',
]);

// Setear Receptor
$sender->setReceiver([
    'identification_type' => '02',
    'identification_number' => 'identification_del_receptor',
]);
```

### Callback URL

Para recibir notificaciones sobre el estado de los documentos enviados, debes configurar una URL de callback:

```php
$sender->setCallbackUrl('https://tu-dominio.com/callback');
```

### Recepción de Documentos

```php
$sender->send(
    documentType: 'invoice',
    documentKey: $clave,
    date: '2025-11-18T00:00:00-06:00',
    signedXml: base64_encode($xml)
);
```

### Consultar estado del documento enviado

Después de enviar un documento, puedes consultar su estado usando el método `checkStatus`:

```php
$documentStatus = $sender->checkStatus(
    documentKey: $clave
);
```

### Obtener listado de documentos enviados

Puedes obtener un listado de documentos enviados usando el método `getDocuments`:

```php
$documents = $sender->getDocuments(
    offset: 0,
    limit: 50
);
```

### Consulta un documento enviado

```php
$document = $sender->getDocument(
    documentKey: $clave
);
```

## Firmar Documentos

Para firmar documentos electrónicos, puedes utilizar el paquete:

- [DGT Signer](https://github.com/dazza-dev/dgt-cr-signer)

## Contribuciones

Contribuciones son bienvenidas. Si encuentras algún error o tienes ideas para mejoras, por favor abre un issue o envía un pull request. Asegúrate de seguir las guías de contribución.

## Autor

DGT Sender fue creado por [DAZZA](https://github.com/dazza-dev).

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).
