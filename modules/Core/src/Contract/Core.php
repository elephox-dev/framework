<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Contract\HandlerContainer;
use Elephox\DI\Contract\Container;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

interface Core extends RequestHandlerInterface
{
	public function getVersion(): string;

	/**
	 * @param App|class-string<App> $app
	 */
	public function registerApp(App|string $app): App;

	public function checkRegistrar(object $potentialRegistrar): void;

	public function handleException(Throwable $throwable): void;

	public function handleContext(Context $context): mixed;

	public function getGlobalContext(): Context;

	public function getContainer(): Container;

	public function getHandlerContainer(): HandlerContainer;

	#[NoReturn]
	public function handleGlobal(): void;
}