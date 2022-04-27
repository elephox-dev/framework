<?php
declare(strict_types=1);

// Credits for this router goes to https://github.com/1ma/coaxing-php-server

// $_SERVER['DOCUMENT_ROOT'] is the absolute path to the public directory in your filesystem, e.g. /var/www/html/public
// $_SERVER['REQUEST_URI'] is the URI of the HTTP request, e.g. /assets/css/bulma.css
$path = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'];

// If $path is a direct file hit let the cli server handle this simple case.
if (is_file($path)) {
	return false;
}

// If $path is a directory and contains an index.html file let the
// cli server handle it, because we know it _will_ serve that index.html
if (is_dir($path) && is_file("$path/index.html")) {
	return false;
}

// provide STDERR as the built-in webserver only provides it for CLI scripts
define('STDERR', fopen('php://stderr', 'wb'));

// All other cases should be handled by the real front controller
require_once $_SERVER['DOCUMENT_ROOT'] . '/index.php';

// Log request in PHP built-in server format: [Wed Apr 27 01:33:35 2022] [::1]:56061 [200]: GET /favicon.ico
fwrite(STDERR, sprintf("[%s] %s:%d [%d]: %s %s\n", (new DateTime())->format('D M d H:i:s Y'), str_contains($_SERVER['REMOTE_ADDR'], ':') ? ('[' . $_SERVER['REMOTE_ADDR'] . ']') : $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], http_response_code(), $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']));
fclose(STDERR);
