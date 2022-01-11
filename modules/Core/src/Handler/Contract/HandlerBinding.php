<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\InvalidResultException;
use Elephox\Core\Middleware\Contract\Middleware;

interface HandlerBinding
{
	public function getHandlerMeta(): HandlerMeta;

	/**
	 * @return GenericList<Middleware>
	 */
	public function getMiddlewares(): GenericList;

	/**
	 * @throws InvalidContextException
	 * @throws InvalidResultException
	 */
	public function handle(Context $context): mixed;
}
