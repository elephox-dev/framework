<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Collection\ObjectSet;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<CommandMetadata>
 */
readonly class CommandProvider implements IteratorAggregate
{
	/**
	 * @param ObjectSet<CommandMetadata> $metadataSet
	 */
	public function __construct(private ObjectSet $metadataSet)
	{
	}

	/**
	 * @throws CommandNotFoundException
	 */
	public function get(string $name): CommandMetadata
	{
		$metadata = $this->metadataSet
			->where(static fn (CommandMetadata $metadata): bool => $metadata->template->name === $name)
			->firstOrDefault(null)
		;

		return $metadata ?? throw new CommandNotFoundException($name);
	}

	public function has(string $name): bool
	{
		return $this->metadataSet
			->any(static fn (CommandMetadata $metadata): bool => $metadata->template->name === $name)
		;
	}

	public function getIterator(): Traversable
	{
		return $this->metadataSet->getIterator();
	}
}
