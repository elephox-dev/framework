<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\InvalidResultException;

interface HandlerMeta
{
	public function getType(): ActionType;

	public function getWeight(): int;

	public function handles(Context $context): bool;

	/**
	 * @throws InvalidContextException
	 */
	public function getHandlerParams(Context $context): array;
}
