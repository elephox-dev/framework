<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Autoloading\Composer\NamespaceLoader;
use Elephox\Collection\ArraySet;
use Elephox\Collection\ObjectSet;
use Elephox\Console\Command\Contract\CommandHandler;
use Elephox\DI\Contract\Resolver;
use InvalidArgumentException;
use LogicException;

readonly class CommandCollection
{
	/**
	 * @var ArraySet<class-string<CommandHandler>> $templates
	 */
	private ArraySet $templates;

	public function __construct()
	{
		$this->templates = new ArraySet();
	}

	public function add(string $className): void
	{
		$interfaces = class_implements($className);
		if (!$interfaces || !in_array(CommandHandler::class, $interfaces, true)) {
			throw new LogicException(sprintf('%s must implement %s', $className, CommandHandler::class));
		}

		/** @var class-string<CommandHandler> $className */
		$this->templates->add($className);
	}

	public function addNamespace(string $namespace): static
	{
		/** @var class-string $className */
		foreach (NamespaceLoader::iterateNamespace($namespace) as $className) {
			$this->add($className);
		}

		return $this;
	}

	public function build(Resolver $resolver): CommandProvider
	{
		/** @var ObjectSet<CommandMetadata> $metadataSet */
		$metadataSet = new ObjectSet();

		foreach ($this->templates as $className) {
			$metadata = $this->getMetadataFromClassName($className, $resolver);

			$metadataSet->add($metadata);
		}

		return new CommandProvider($metadataSet);
	}

	/**
	 * @param class-string $className
	 */
	protected function getMetadataFromClassName(string $className, Resolver $resolver): CommandMetadata
	{
		/** @var CommandHandler $handler */
		$handler = $resolver->instantiate($className);

		$templateBuilder = new CommandTemplateBuilder();

		$this->preProcessCommandTemplate($templateBuilder);
		$handler->configure($templateBuilder);
		$this->postProcessCommandTemplate($templateBuilder);

		$template = $templateBuilder->build();

		return new CommandMetadata($template, $handler);
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
}
