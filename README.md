## MediaWiki Stakeholders Group - Components
# WellKnown for MediaWiki

Provides a `/.well-known/{filename}` REST endpoint with a registry for virtual files,
following [RFC 8615](https://tools.ietf.org/html/rfc8615).

**This code is meant to be executed within the MediaWiki application context. No standalone usage is intended.**

## Compatibility
- `1.0.x` -> MediaWiki 1.43

## Use in a MediaWiki extension

Require this component in the `composer.json` of your extension:

```json
{
  "require": {
    "mwstake/mediawiki-component-wellknown": "~1"
  }
}
```

## Services

| Service name | Class | Description |
|---|---|---|
| `MWStake.WellKnown.FileRegistry` | `FileRegistry` | Registry where components register virtual files |

## Registering a well-known file

Register files in an `ExtensionFunctions` callback or during component bootstrap.
Each registration requires a filename, a content callback, and a content type:

```php
use MediaWiki\MediaWikiServices;

$registry = MediaWikiServices::getInstance()
    ->getService( 'MWStake.WellKnown.FileRegistry' );

$registry->register(
    'openid-configuration',
    static function () {
        return json_encode( [
            'issuer' => 'https://wiki.example.com',
            'jwks_uri' => 'https://wiki.example.com/.well-known/jwks.json',
            'response_types_supported' => [ 'id_token' ],
        ] );
    },
    'application/json'
);
```

The content callback is invoked on each request — you can return dynamic content.

### Parameters

| Parameter | Type | Description |
|---|---|---|
| `$filename` | string | The filename portion of the URL (e.g. `jwks.json` → `/.well-known/jwks.json`) |
| `$contentCallback` | callable | Returns the file content as a string |
| `$contentType` | string | MIME type for the `Content-Type` response header |

## Accessing well-known files

Files are served at:

```
GET {wiki_base_url}/rest.php/.well-known/{filename}
```

For example:
```
GET https://wiki.example.com/rest.php/.well-known/jwks.json
GET https://wiki.example.com/rest.php/.well-known/openid-configuration
```

### Nginx rewrite (optional)

To serve files at the standard `/.well-known/` path without the `/rest.php` prefix,
add a rewrite rule to your web server:

```nginx
location /.well-known/ {
    rewrite ^/\.well-known/(.*)$ /rest.php/.well-known/$1 last;
}
```

## How it works

The component registers a single REST route handler (`WellKnownHandler`) at
`/.well-known/{filename}`. When a request arrives:

1. The handler looks up the filename in the `FileRegistry`
2. If found, it calls the content callback and returns the result with the registered content type
3. If not found, it returns a 404 response

## Example: multiple registrations

```php
// JSON discovery document
$registry->register( 'openid-configuration', static function () {
    return json_encode( [ 'issuer' => '...' ] );
}, 'application/json' );

// Plain text security contact
$registry->register( 'security.txt', static function () {
    return "Contact: security@example.com\nExpires: 2027-01-01T00:00:00.000Z\n";
}, 'text/plain' );

// PEM-encoded certificate
$registry->register( 'est/cacerts', static function () use ( $certPath ) {
    return file_get_contents( $certPath );
}, 'application/pkcs7-mime' );
```
