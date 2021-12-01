<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute\Contract;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Contract\ActionType;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\InvalidResultException;

interface HandlerAttribute
{
	public function getType(): ActionType;

	public function getWeight(): int;

	public function handles(Context $context): bool;

	/**
	 * @template T
	 *
	 * @param Closure(): T $callback
	 * @param Context $context
	 *
	 * @throws InvalidResultException
	 * @throws InvalidContextException
	 */
	public function invoke(Closure $callback, Context $context): void;
}
