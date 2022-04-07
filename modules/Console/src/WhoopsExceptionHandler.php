<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Support\Contract\ExceptionHandler;
use NunoMaduro\Collision\Handler as CollisionHandler;
use Throwable;
use Whoops\Handler\PlainTextHandler;
use Whoops\RunInterface as WhoopsRunInterface;

class WhoopsExceptionHandler implements ExceptionHandler
{
	public function __construct(
		private WhoopsRunInterface $whoopsRun,
	) {
	}

	public function handleException(Throwable $exception): void
	{
		if (empty($this->whoopsRun->getHandlers())) {
			/**
			 * @psalm-suppress InternalClass
			 * @psalm-suppress InternalMethod
			 */
			if (class_exists(CollisionHandler::class)) {
				$this->whoopsRun->pushHandler(new CollisionHandler());
			} else {
				$this->whoopsRun->pushHandler(new PlainTextHandler());
			}
		}

		$this->whoopsRun->handleException($exception);
	}
}
