<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Handler\Handlers;
use Elephox\Core\Handler\RequestContext;
use Elephox\DI\Container;
use Elephox\Http\Request;
use LogicException;

class Core
{
	public const Version = '0.0.1';

	private static Container $container;

	public static function entrypoint(): void
	{
		if (defined("ELEPHOX_ENTRY")) {
			throw new LogicException("Entrypoint already called.");
		}

		define("ELEPHOX_ENTRY", microtime(true));
		define("ELEPHOX_VERSION", self::Version);

		self::$container = new Container();

		assert(class_exists("App\\App", true));

		define("ELEPHOX_HANDLER_LOAD", microtime(true));

		Handlers::load(self::$container);

		define("ELEPHOX_HANDLER_LOADED", microtime(true));

		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (str_starts_with($name, 'HTTP_')) {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}

		$context = new RequestContext(self::$container, new Request($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"], $headers));

		define("ELEPHOX_HANDLER_HANDLE", microtime(true));

		Handlers::handle($context);

		define("ELEPHOX_HANDLER_HANDLED", microtime(true));
	}
}
