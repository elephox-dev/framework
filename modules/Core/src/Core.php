<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Context\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\Context as ContextContract;
use Elephox\Core\Context\ExceptionContext;
use Elephox\Core\Context\RequestContext;
use Elephox\Core\Contract\App;
use Elephox\Core\Handler\Contract\HandlerContainer as HandlerContainerContract;
use Elephox\Core\Handler\HandlerContainer;
use Elephox\DI\Container;
use Elephox\DI\Contract\Container as ContainerContract;
use Elephox\Http\Request;
use JetBrains\PhpStorm\NoReturn;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
class Core implements Contract\Core
{
	private static ?Contract\Core $instance = null;

	public static function instance(): Contract\Core
	{
		if (self::$instance === null) {
			self::$instance = self::entrypoint();
		}

		return self::$instance;
	}

	public static function entrypoint(): Contract\Core
	{
		if (defined("ELEPHOX_VERSION")) {
			throw new LogicException("Entrypoint already called.");
		}

		$container = new Container();
		self::$instance = new self($container);

		define("ELEPHOX_VERSION", self::$instance->getVersion());

		if (!$container->has(HandlerContainerContract::class)) {
			$container->register(HandlerContainerContract::class, fn (ContainerContract $c) => new HandlerContainer($c));
		}

		return self::$instance;
	}

	private ?HandlerContainerContract $handlerContainer = null;

	protected function __construct(
		private ContainerContract $container
	)
	{
	}

	public function getVersion(): string
	{
		return "1.0";
	}

	public function registerApp(App|string $app): App
	{
		if (is_string($app)) {
			$appClassName = $app;
			$registerParameter = $appClassName;
		} else {
			$appClassName = $app::class;
			$registerParameter = $app;
		}

		$this->container->register(App::class, $registerParameter);
		$this->container->register($appClassName, $registerParameter);

		$appInstance = $this->container->get($appClassName);
		$this->getHandlerContainer()->checkRegistrar($appInstance);

		return $appInstance;
	}

	public function handleException(Throwable $throwable): void
	{
		$exceptionContext = new ExceptionContext($this->getContainer(), $throwable);

		try {
			$this->getHandlerContainer()->findHandler($exceptionContext)->handle($exceptionContext);
		} catch (Throwable $innerThrowable) {
			if (!headers_sent()) {
				header('HTTP/1.1 500 Internal Server Error');
				header('Content-Type: text/plain; charset=utf-8');
			}

			echo "Could not handle exception. " . $throwable->getMessage() . "\n";
			echo "\n";
			echo "Additionally, the exception handler threw an exception while trying to handle the first exception: " . $innerThrowable->getMessage() . "\n";
			echo $innerThrowable->getTraceAsString();
		}
	}

	public function handleContext(ContextContract $context): mixed
	{
		try {
			return $this->getHandlerContainer()->findHandler($context)->handle($context);
		} catch (Throwable $e) {
			$this->handleException($e);

			return null;
		}
	}

	public function getGlobalContext(): ContextContract
	{
		global $argv;

		return match (PHP_SAPI) {
			'cli' => new CommandLineContext($this->getContainer(), array_splice($argv, 1)),
			default => new RequestContext($this->getContainer(), Request::fromGlobals())
		};
	}

	public function getContainer(): ContainerContract
	{
		return $this->container;
	}

	public function getHandlerContainer(): HandlerContainerContract
	{
		if ($this->handlerContainer === null) {
			$this->handlerContainer = $this->getContainer()->get(HandlerContainerContract::class);
		}

		return $this->handlerContainer;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$context = new RequestContext($this->getContainer(), $request);

		$result = $this->handleContext($context);
		if (!$result instanceof ResponseInterface) {
			throw new LogicException("Result must be an instance of ResponseInterface.");
		}

		return $result;
	}

	#[NoReturn] public function handleGlobal(): void
	{
		$context = $this->getGlobalContext();

		/** @var mixed $result */
		$result = $this->handleContext($context);
		if (is_object($result) && method_exists($result, 'send')) {
			$result->send();
		}

		exit();
	}
}
