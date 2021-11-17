<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

use Elephox\Core\Handler\ActionType;
use Elephox\DI\Contract\Container;

interface Context
{
	public function getActionType(): ActionType;

	public function getContainer(): Container;
}
