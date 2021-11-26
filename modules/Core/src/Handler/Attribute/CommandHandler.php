<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Closure;
use Elephox\Core\Context\Contract\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\ActionType;
use Elephox\Core\Handler\InvalidContextException;
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

	public function handles(CommandLineContext|Context $context): bool
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

		return preg_match($this->commandSignature, $commandLine) === 1;
	}

	public function invoke(Closure $callback, CommandLineContext|Context $context): void
	{
		if (!$context instanceof CommandLineContext) {
			throw new InvalidContextException($context, CommandLineContext::class);
		}

		$context->getContainer()->callback($callback, [
			'context' => $context,
			...$context->getArgs()
		]);
	}
}
