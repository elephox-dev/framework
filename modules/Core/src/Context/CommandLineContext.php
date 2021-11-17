<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\Handler\ActionType;
use Elephox\DI\Contract\Container;
use JetBrains\PhpStorm\Pure;

class CommandLineContext extends AbstractContext implements Contract\CommandLineContext
{
	#[Pure] public function __construct(
		Container $container
	)
	{
		parent::__construct(ActionType::Command, $container);
	}
}
