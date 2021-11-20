<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute\Contract;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Contract\ActionType;

interface HandlerAttribute
{
	public function getType(): ActionType;

	public function handles(Context $context): bool;

	public function invoke(object $handler, string $method, Context $context): void;
}
