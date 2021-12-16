<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\Context\Contract\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\ActionType;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Text\Regex;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CommandHandler extends AbstractHandlerAttribute
{
	#[Pure] public function __construct(
		private ?string $commandSignature = null,
		int $weight = 0,
	)
	{
		parent::__construct(ActionType::Command, $weight);
	}

	public function getWeight(): int
	{
		if ($this->commandSignature === null && parent::getWeight() === 0)
		{
			return -1;
		}

		return parent::getWeight();
	}

	public function handles(Context $context): bool
	{
		if (!$context instanceof CommandLineContext) {
			return false;
		}

		if ($this->commandSignature === null) {
			return true;
		}

		$commandLine = $context->getCommandLine();
		if (empty($commandLine)) {
			return false;
		}

		return Regex::matches($this->commandSignature, $commandLine);
	}

	public function getHandlerParams(Context $context): array
	{
		if (!$context instanceof CommandLineContext) {
			throw new InvalidContextException($context, CommandLineContext::class);
		}

		if ($this->commandSignature === null) {
			return [];
		}

		return Regex::match($this->commandSignature, $context->getCommandLine())
			->whereKey(static fn(string|int $key) => is_string($key))
			->asArray();
	}
}
