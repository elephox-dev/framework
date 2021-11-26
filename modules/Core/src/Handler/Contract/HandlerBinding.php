<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\InvalidResultException;

/**
 * @template THandler as Closure(): mixed
 * @template TContext as Context
 */
interface HandlerBinding
{
	/**
	 * @return THandler
	 */
	public function getHandler(): Closure;

	/**
	 * @param TContext $context
	 */
	public function isApplicable(Context $context): bool;

	/**
	 * @param TContext $context
	 *
	 * @throws InvalidContextException
	 * @throws InvalidResultException
	 */
	public function handle(Context $context): void;
}
