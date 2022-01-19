<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Context\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\Context as ContextContract;
use Elephox\Core\Context\ExceptionContext;
use Elephox\Core\Context\RequestContext;
use Elephox\Core\Contract\App;
use Elephox\Core\Contract\Registrar as RegistrarContract;
use Elephox\Core\Handler\Contract\HandlerContainer as HandlerContainerContract;
use Elephox\Core\Handler\HandlerContainer;
use Elephox\Core\Registrar as RegistrarTrait;
use Elephox\DI\Container;
use Elephox\DI\Contract\Container as ContainerContract;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\Response as ResponseContract;
use Elephox\Http\ServerRequestBuilder;
use JetBrains\PhpStorm\NoReturn;
use LogicException;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
class Core implements Contract\Core
{
	private static ?Contract\Core $instance = null;

	/**
	 * @throws \Safe\Exceptions\MiscException
	 */
	public static function instance(): Contract\Core
	{
		if (self::$instance === null) {
			self::$instance = self::create();
		}

		return self::$instance;
	}

	/**
	 * @throws \Safe\Exceptions\MiscException
	 */
	public static function create(): Contract\Core
	{
		if (defined("ELEPHOX_VERSION")) {
			throw new LogicException("Core already created.");
		}

		$container = new Container();
		self::$instance = new static($container);

		$container->register(self::$instance::class, self::$instance);
		$container->alias(Contract\Core::class, self::$instance::class);

		\Safe\define("ELEPHOX_VERSION", self::$instance->getVersion());

		return self::$instance;
	}

	private ?HandlerContainerContract $handlerContainer = null;

	protected function __construct(
		private ContainerContract $container
	) {
	}

	public function getVersion(): string
	{
		return "1.0";
	}

	/**
	 * @throws \Safe\Exceptions\SplException
	 */
	public function registerApp(App|string $app): App
	{
		if (is_string($app)) {
			$appClassName = $app;
			$registerParameter = $appClassName;
		} else {
			$appClassName = $app::class;
			$registerParameter = $app;
		}

		// register app classes
		$this->getContainer()->register(App::class, $registerParameter);
		$this->getContainer()->register($appClassName, $registerParameter);

		// get app instance
		$appInstance = $this->getContainer()->get($appClassName);

		// check if app is registrar and register all services
		$this->checkRegistrar($appInstance);

		// check for handlers in app
		$this->getHandlerContainer()->loadFromClass($appClassName);

		return $appInstance;
	}

	/**
	 * @throws \Safe\Exceptions\SplException
	 */
	public function checkRegistrar(object $potentialRegistrar): void
	{
		$traits = \Safe\class_uses($potentialRegistrar);
		if (
			!($potentialRegistrar instanceof RegistrarContract) &&
			!in_array(RegistrarTrait::class, $traits, true)
		) {
			return;
		}

		/** @var RegistrarContract $potentialRegistrar */
		$potentialRegistrar->registerAll($this->getContainer());
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

			echo "Could not handle exception: " . $throwable->getMessage() . "\n";
			echo $throwable->getTraceAsString();
			echo "\n";
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
			default => new RequestContext($this->getContainer(), ServerRequestBuilder::fromGlobals())
		};
	}

	public function getContainer(): ContainerContract
	{
		return $this->container;
	}

	public function getHandlerContainer(): HandlerContainerContract
	{
		if ($this->handlerContainer === null) {
			if (!$this->container->has(HandlerContainerContract::class)) {
				$this->container->register(HandlerContainerContract::class, fn(ContainerContract $c) => new HandlerContainer($c));
			}

			$this->handlerContainer = $this->getContainer()->get(HandlerContainerContract::class);
		}

		return $this->handlerContainer;
	}

	public function handle(RequestContract $request): ResponseContract
	{
		$context = new RequestContext($this->getContainer(), $request);

		$result = $this->handleContext($context);
		if (!$result instanceof ResponseContract) {
			throw new LogicException("Result must be an instance of Response.");
		}

		return $result;
	}

	#[NoReturn]
	public function handleGlobal(): never
	{
		$context = $this->getGlobalContext();

		/** @var mixed $result */
		$result = $this->handleContext($context);
		if ($result instanceof ResponseContract) {
			$this->sendResponse($result);
		}

		$exitCode = 0;
		if (is_int($result)) {
			if ($result >= 0 && $result < 255) {
				$exitCode = $result;
			} else {
				throw new LogicException("Result must be an integer between 0 and 255.");
			}
		}

		exit($exitCode);
	}

	private function sendResponse(ResponseContract $response): void
	{
		$this->sendHeaders($response);
		$this->sendBody($response);
	}

	private function sendHeaders(ResponseContract $response): void
	{
		if (headers_sent()) {
			return;
		}

		foreach ($response->getHeaderMap() as $headerName => $values) {
			if (is_array($values)) {
				foreach ($values as $value) {
					header("$headerName: $value");
				}
			} else {
				header("$headerName: $values");
			}
		}
	}

	private function sendBody(ResponseContract $response): void
	{
		echo $response->getBody()->getContents();
	}
}
