<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Core\Context\Contract\Context;

interface HandlerContainer
{
	public function register(HandlerBinding $binding): void;

	public function findHandler(Context $context): HandlerBinding;
}
