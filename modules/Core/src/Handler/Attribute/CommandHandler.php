<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Core\Context\Contract\CommandLineContext;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\ActionType;
use Exception;
use JetBrains\PhpStorm\Pure;

#[Attribute(Attribute::TARGET_METHOD)]
class CommandHandler extends AbstractHandler
{
	#[Pure] public function __construct()
	{
		parent::__construct(ActionType::Command);
	}

	public function handles(CommandLineContext|Context $context): bool
	{
		return $context instanceof CommandLineContext;
	}

	/**
	 * @throws \Exception
	 */
	public function invoke(object $handler, string $method, CommandLineContext|Context $context): void
	{
		if (!$context instanceof CommandLineContext) {
			throw new Exception('Invalid context type');
		}

		$context->getContainer()->call($handler, $method, ['context' => $context]);
	}
}