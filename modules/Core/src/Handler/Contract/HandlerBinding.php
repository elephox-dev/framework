<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\InvalidResultException;
use Elephox\Core\Middleware\Contract\Middleware;

interface HandlerBinding
{
	public function getHandlerMeta(): HandlerMeta;

	/**
	 * @return ReadonlyList<Middleware>
	 */
	public function getMiddlewares(): ReadonlyList;

	/**
	 * @throws InvalidContextException
	 * @throws InvalidResultException
	 */
	public function handle(Context $context): mixed;
}
