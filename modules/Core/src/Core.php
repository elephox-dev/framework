<?php
declare(strict_types=1);

namespace Elephox\Core;

use Closure;
use Elephox\Core\Context\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\ExceptionContext;
use Elephox\Core\Context\RequestContext;
use Elephox\Core\Contract\App;
use Elephox\Core\Handler\Attribute\Contract\HandlerAttribute;
use Elephox\Core\Handler\Contract\ComposerAutoloaderInit;
use Elephox\Core\Handler\Contract\ComposerClassLoader;
use Elephox\Core\Handler\Contract\HandlerContainer as HandlerContainerContract;
use Elephox\Core\Handler\HandlerBinding;
use Elephox\Core\Handler\HandlerContainer;
use Elephox\DI\Container;
use Elephox\DI\Contract\Container as ContainerContract;
use Elephox\Http\Request;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;

class Core
{
	public const Version = '0.1';

	private static ?Container $container = null;

	public static function getContainer(): ContainerContract
	{
		if (self::$container === null) {
			self::$container = new Container();
		}

		return self::$container;
	}

	public static function entrypoint(): void
	{
		if (defined("ELEPHOX_VERSION")) {
			throw new LogicException("Entrypoint already called.");
		}

		define("ELEPHOX_VERSION", self::Version);

		if (!self::getContainer()->has(HandlerContainerContract::class)) {
			self::getContainer()->register(HandlerContainerContract::class, new HandlerContainer());
		}
	}

	private static function checkEntrypointCalled(): void
	{
		if (!defined("ELEPHOX_VERSION")) {
			throw new LogicException("Core::entrypoint() not called.");
		}
	}

	/**
	 * @param class-string<App>|App $app
	 */
	public static function setApp(string|App $app): void
	{
		self::checkEntrypointCalled();

		self::getContainer()->register(App::class, $app);

		try {
			if (is_object($app)) {
				$app = $app::class;
			}

			self::getContainer()->alias($app, App::class);

			self::loadHandlers($app);
		} catch (ReflectionException $e) {
			self::handleException($e);
		}
	}

	public static function loadHandlersInNamespace(string $namespace): void
	{
		self::checkEntrypointCalled();

		$classLoader = self::getClassLoader();
		foreach (array_keys($classLoader->getClassMap()) as $class) {
			if (!str_starts_with($class, $namespace)) {
				continue;
			}

			if ($classLoader->loadClass($class) !== true) {
				throw new RuntimeException('Could not load class ' . $class);
			}
		}

		try {
			foreach (get_declared_classes() as $class) {
				if (!str_starts_with($class, $namespace)) {
					continue;
				}

				self::loadHandlers($class);
			}
		} catch (ReflectionException $e) {
			self::handleException($e);
		}
	}

	/**
	 * @param class-string $className
	 * @throws ReflectionException
	 */
	public static function loadHandlers(string $className): void
	{
		self::checkEntrypointCalled();

		if (!self::getContainer()->has($className)) {
			self::getContainer()->register($className, $className);
		}

		$classInstance = self::getContainer()->get($className);

		self::checkRegistrar($classInstance);

		$handlerContainer = self::getContainer()->get(HandlerContainerContract::class);
		$classReflection = new ReflectionClass($className);
		$classAttributes = $classReflection->getAttributes(HandlerAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
		if (!empty($classAttributes)) {
			if (!is_callable($classInstance)) {
				throw new InvalidClassCallableHandlerException($className);
			}

			/** @var Closure(): mixed $closure */
			$closure = Closure::fromCallable($classInstance);
			self::registerAttributes($handlerContainer, $closure, $classAttributes);
		}

		$methods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $methodReflection) {
			$methodAttributes = $methodReflection->getAttributes(HandlerAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
			if (empty($methodAttributes)) {
				continue;
			}

			/** @var Closure(): mixed $closure */
			$closure = $methodReflection->getClosure($classInstance);
			self::registerAttributes($handlerContainer, $closure, $methodAttributes);
		}
	}

	/**
	 * @param HandlerContainerContract $handlerContainer
	 * @param Closure():mixed $closure
	 * @param array<array-key, ReflectionAttribute<HandlerAttribute>> $attributes
	 */
	private static function registerAttributes(HandlerContainerContract $handlerContainer, Closure $closure, array $attributes): void
	{
		foreach ($attributes as $handlerAttribute) {
			$attributeInstance = $handlerAttribute->newInstance();

			/** @var HandlerBinding<Closure():mixed, Context> $binding */
			$binding = new HandlerBinding($closure, $attributeInstance);

			$handlerContainer->register($binding);
		}
	}

	public static function checkRegistrar(object $potentialRegistrar): void
	{
		$traits = class_uses($potentialRegistrar);
		if (!($potentialRegistrar instanceof Contract\Registrar) && ($traits === false || !in_array(Registrar::class, $traits, true))) {
			return;
		}

		/** @var Contract\Registrar $potentialRegistrar */
		$potentialRegistrar->registerAll(self::getContainer());
	}

	public static function getClassLoader(): ComposerClassLoader
	{
		self::checkEntrypointCalled();

		/** @var null|class-string<ComposerAutoloaderInit> $autoloaderClassName */
		$autoloaderClassName = null;
		foreach (get_declared_classes() as $class) {
			if (!str_starts_with($class, 'ComposerAutoloaderInit')) {
				continue;
			}

			$autoloaderClassName = $class;

			break;
		}

		if ($autoloaderClassName === null) {
			throw new RuntimeException('Could not find ComposerAutoloaderInit class. Did you install the dependencies using composer?');
		}

		/** @var ComposerClassLoader */
		return call_user_func([$autoloaderClassName, 'getLoader']);
	}

	#[NoReturn]
	public static function handle(): void
	{
		global $argv;

		self::checkEntrypointCalled();

		$handlerContainer = self::getContainer()->get(HandlerContainerContract::class);

		try {
			/** @var Context $context */
			$context = match (PHP_SAPI) {
				'cli' => new CommandLineContext(self::getContainer(), count($argv) > 1 ? $argv[1] : null, array_slice($argv, 2)),
				default => new RequestContext(self::getContainer(), Request::fromGlobals())
			};

			$handler = $handlerContainer->findHandler($context);
			$handler->handle($context);
		} catch (Throwable $e) {
			self::handleException($e);
		}

		exit();
	}

	#[NoReturn]
	public static function handleException(Throwable $throwable): void
	{
		self::checkEntrypointCalled();

		$handlerContainer = self::getContainer()->get(HandlerContainerContract::class);
		$exceptionContext = new ExceptionContext(self::getContainer(), $throwable);

		try {
			$handlerContainer->findHandler($exceptionContext)->handle($exceptionContext);
		} catch (Exception $e) {
			echo "Could not handle exception. " . $e->getMessage();

			exit(2);
		}

		exit(1);
	}
}
