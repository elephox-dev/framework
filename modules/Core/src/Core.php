<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Context\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\RequestContext;
use Elephox\Core\Handler\Handlers;
use Elephox\DI\Container;
use Elephox\Http\Request;
use Exception;
use LogicException;

class Core
{
	public const Version = '0.0.1';

	private static Container $container;

	/**
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	public static function entrypoint(): void
	{
		if (defined("ELEPHOX_VERSION")) {
			throw new LogicException("Entrypoint already called.");
		}

		define("ELEPHOX_VERSION", self::Version);

		self::$container = new Container();

		Handlers::load(self::$container);

		/** @var Context $context */
		$context = match(PHP_SAPI) {
			'cli' => new CommandLineContext(self::$container),
			default => new RequestContext(self::$container, Request::fromGlobals())
		};

		Handlers::handle($context);
	}
}
