<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\Contract\GenericKeyValuePair;
use Elephox\Collection\ObjectMap;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\DI\Contract\Resolver;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<CommandTemplate, CommandHandler>
 */
class CommandCollection implements IteratorAggregate
{
	/**
	 * @var ObjectMap<CommandTemplate, CommandHandler> $templateMap
	 */
	private readonly ObjectMap $templateMap;

	public function __construct(private readonly Resolver $resolver)
	{
		$this->templateMap = new ObjectMap();
	}

	public function add(CommandTemplate $template, CommandHandler $handler): void
	{
		$this->templateMap->put($template, $handler);
	}

	public function loadFromNamespace(string $namespace): static
	{
		NamespaceLoader::iterateNamespace($namespace, function (string $className): void {
			$this->loadFromClass($className);
		});

		return $this;
	}

	protected function preProcessCommandTemplate(CommandTemplateBuilder $builder): void
	{
		$builder->addOption('help', '?', description: 'Show this help message and exit');
	}

	protected function postProcessCommandTemplate(CommandTemplateBuilder $builder): void
	{
		$commandName = $builder->getName();
		if ($commandName === null) {
			throw new InvalidArgumentException('Command name is not set');
		}
	}

	/**
	 * @param class-string $className
	 */
	public function loadFromClass(string $className): void
	{
		$interfaces = class_implements($className);
		if (!$interfaces || !in_array(CommandHandler::class, $interfaces, true)) {
			return;
		}

		/** @var CommandHandler $instance */
		$instance = $this->resolver->instantiate($className);

		$templateBuilder = new CommandTemplateBuilder();
		$this->preProcessCommandTemplate($templateBuilder);
		$instance->configure($templateBuilder);
		$this->postProcessCommandTemplate($templateBuilder);
		$template = $templateBuilder->build();

		$this->add($template, $instance);
	}

	public function findCompiled(RawCommandInvocation $invocation): CompiledCommandHandler
	{
		$pair = $this->findPairByName($invocation->name);

		return new CompiledCommandHandler($invocation, $pair->getKey(), $pair->getValue());
	}

	public function getTemplateByName(string $name): CommandTemplate
	{
		return $this->findPairByName($name)->getKey();
	}

	/**
	 * @param string $name
	 *
	 * @return GenericKeyValuePair<CommandTemplate, CommandHandler>
	 *
	 * @throws CommandNotFoundException
	 */
	protected function findPairByName(string $name): GenericKeyValuePair
	{
		return $this->templateMap
			->whereKey(static fn (CommandTemplate $template): bool => $template->name === $name)
			->firstPairOrDefault(null)
			?? throw new CommandNotFoundException($name);
	}

	public function getIterator(): Traversable
	{
		return $this->templateMap->getIterator();
	}
}
