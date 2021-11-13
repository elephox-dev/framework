<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

/**
 * @template THandler as object
 */
interface HandlerBinding
{
	public function getMethodName(): string;

	/**
	 * @return THandler
	 */
	public function getHandler(): object;

	public function isApplicable(Context $context): bool;
}
