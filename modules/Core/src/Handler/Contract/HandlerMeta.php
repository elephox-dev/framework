<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Contract\HandlerStackMeta;
use Elephox\Core\Handler\InvalidContextException;

interface HandlerMeta extends HandlerStackMeta
{
	public function handles(Context $context): bool;

	/**
	 * @throws InvalidContextException
	 */
	public function getHandlerParams(Context $context): iterable;
}
