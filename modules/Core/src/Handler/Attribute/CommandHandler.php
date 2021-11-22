<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\Context\Contract\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\ActionType;
use Exception;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class CommandHandler extends AbstractHandlerAttribute
{
	#[Pure] public function __construct(
		private ?string $commandSignature = null
	)
	{
		parent::__construct(ActionType::Command);
	}

	public function handles(CommandLineContext|Context $context): bool
	{
		if (!$context instanceof CommandLineContext) {
			return false;
		}

		if ($this->commandSignature === null) {
			return true;
		}

		return preg_match($this->commandSignature, $context->getCommand()) === 1;
	}

	/**
	 * @throws Exception
	 */
	public function invoke(object $handler, string $method, CommandLineContext|Context $context): void
	{
		if (!$context instanceof CommandLineContext) {
			throw new Exception('Invalid context type');
		}

		$context->getContainer()->call($handler, $method, ['context' => $context]);
	}
}
