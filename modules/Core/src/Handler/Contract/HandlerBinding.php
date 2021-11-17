<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Core\Context\Contract\Context;

/**
 * @template THandler as object
 * @template TContext as Context
 */
interface HandlerBinding
{
	public function getMethodName(): string;

	/**
	 * @return THandler
	 */
	public function getHandler(): object;

	/**
	 * @param TContext $context
	 */
	public function isApplicable(Context $context): bool;

	/**
	 * @param TContext $context
	 */
	public function handle(Context $context): void;
}
