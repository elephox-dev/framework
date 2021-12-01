<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\UnhandledContextException;

interface HandlerContainer
{
	public function register(HandlerBinding $binding): void;

	/**
	 * @throws UnhandledContextException
	 */
	public function findHandler(Context $context): HandlerBinding;
}
