<?php
declare(strict_types=1);

namespace Elephox\Core;

use Closure;
use Elephox\Collection\ArrayList;
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
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\Http\Request;
use Elephox\Text\Regex;
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

		self::getContainer()->singleton(App::class, $app);

		try {
			if (is_object($app)) {
				$appClass = $app::class;
			} else {
				$appClass = $app;
			}

			self::getContainer()->alias($appClass, App::class);

			self::loadHandlers($appClass);
		} catch (ReflectionException $e) {
			self::handleException($e);
		}
	}

	/**
	 * @throws ReflectionException
	 */
	public static function loadHandlersInNamespace(string $namespace): void
	{
		self::checkEntrypointCalled();

		$classLoader = self::getClassLoader();
		foreach ($classLoader->getPrefixesPsr4() as $nsPrefix => $dirs) {
			if (!str_starts_with($namespace, $nsPrefix) && !str_starts_with($nsPrefix, $namespace)) {
				continue;
			}

			$parts = Regex::split('/\\\\/', rtrim($namespace, '\\') . '\\');
			// remove first element since it is the alias for the directories we are iterating
			$root = $parts->shift();

			foreach ($dirs as $dir) {
				$directory = new Directory($dir);

				self::loadClassesRecursive($root, $parts, new ArrayList(), $directory, $classLoader);
			}
		}
	}

	/**
	 * @param string $rootNs
	 * @param ArrayList<string> $nsParts
	 * @param ArrayList<string> $nsPartsUsed
	 * @param DirectoryContract $directory
	 * @param ComposerClassLoader $classLoader
	 * @param int $depth
	 * @throws ReflectionException
	 */
	private static function loadClassesRecursive(string $rootNs, ArrayList $nsParts, ArrayList $nsPartsUsed, DirectoryContract $directory, object $classLoader, int $depth = 0): void
	{
		if ($depth > 10) {
			throw new RuntimeException("Recursion limit exceeded. Please choose a more specific namespace.");
		}

		$lastPart = $nsParts->shift();
		$nsPartsUsed->push($lastPart);
		foreach ($directory->getDirectories() as $dir) {
			if ($dir->getName() !== $lastPart) {

				continue;
			}

			self::loadClassesRecursive($rootNs, $nsParts, $nsPartsUsed, $dir, $classLoader, $depth++);
		}

		if ($lastPart === '') {
			foreach ($directory->getFiles() as $file) {
				$filename = $file->getName();
				if (!str_ends_with($filename, '.php')) {
					continue;
				}

				$className = substr($filename, 0, -4);

				/** @var class-string $fqcn */
				$fqcn = $rootNs . "\\" . implode("\\", $nsPartsUsed->asArray()) . $className;

				$classLoader->loadClass($fqcn);

				self::loadHandlers($fqcn);
			}
		}

		$nsParts->unshift($nsPartsUsed->pop());
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

		$handlerContainer = self::getContainer()->get(HandlerContainerContract::class);

		$classInstance = null;

		$classReflection = new ReflectionClass($className);
		$classAttributes = $classReflection->getAttributes(HandlerAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
		if (!empty($classAttributes)) {
			if (!method_exists($className, "__invoke")) {
				throw new InvalidClassCallableHandlerException($className);
			}

			$classInstance = self::getContainer()->get($className);

			/** @var Closure(): mixed $closure */
			$closure = Closure::fromCallable($classInstance);
			self::registerAttributes($handlerContainer, $closure, $classAttributes);
		}

		$methods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
		if (empty($methods)) {
			return;
		}

		foreach ($methods as $methodReflection) {
			$methodAttributes = $methodReflection->getAttributes(HandlerAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
			if (empty($methodAttributes)) {
				continue;
			}

			$classInstance ??= self::getContainer()->get($className);

			/** @var Closure(): mixed $closure */
			$closure = $methodReflection->getClosure($classInstance);
			self::registerAttributes($handlerContainer, $closure, $methodAttributes);
		}

		if ($classInstance !== null) {
			self::checkRegistrar($classInstance);
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

	/**
	 * @return ComposerClassLoader
	 */
	public static function getClassLoader(): object
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
				'cli' => new CommandLineContext(self::getContainer(), implode(" ", array_splice($argv, 1))),
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
		} catch (Throwable $innerThrowable) {
			echo "Could not handle exception. " . $throwable->getMessage() . "\n";
			echo "\n";
			echo "Additionally, the exception handler threw an exception while trying to handle the first exception: " . $innerThrowable->getMessage() . "\n";
			echo $innerThrowable->getTraceAsString();

			exit(2);
		}

		exit(1);
	}
}
