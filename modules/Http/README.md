# Elephox HTTP Module

This module is used by [Elephox] to work with HTTP messages.
It provides a range of features like parsing incoming HTTP messages, creating outgoing HTTP messages, parsing and modifying headers, cookies, query parameters, uploaded files, and URL parsing.

## Examples

```php
<?php

use Elephox\Http\ServerRequestBuilder;
use Elephox\Http\Url;
use Elephox\Http\Cookie;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Http\ResponseSender;
use Elephox\Http\UrlScheme;

$request = ServerRequestBuilder::fromGlobals();
$request->getUrl()->path; // '/requested/path'

$newRequest = $request->with()
    ->header('X-Foo', 'bar')
    ->cookie(new Cookie('dough', 'choco'))
    ->requestUrl(Url::fromString('https://example.com/new/url'))
    ->get();

$response = Response::build()
    ->responseCode(ResponseCode::OK)
    ->htmlBody('<h1>Hello World</h1>')
    ->header('X-Foo', 'bar')
    ->get();

ResponseSender::sendResponse($response); // you can also pass a response builder object here

$url = Url::fromString('https://user@example.com/with/query?foo=bar&baz=qux');

$url->path; // '/with/query'
$url->query; // ['foo' => 'bar', 'baz' => 'qux']
$url->host; // 'example.com'
$url->port; // null
$url->scheme === UrlScheme::HTTPS; // true
$url->scheme->getDefaultPort(); // 443
$url->scheme->getScheme(); // 'https'
$url->user; // 'user'
$url->pass; // null
$url->fragment; // null
```

[Elephox]: https://github.com/elephox-dev/framework
