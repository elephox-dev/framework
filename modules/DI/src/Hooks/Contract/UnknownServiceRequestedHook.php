<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceHookData;

interface UnknownServiceRequestedHook
{
	/**
	 * @template TService of object
	 *
	 * @param ServiceHookData<TService> $data
	 *
	 * @return void
	 */
	public function unknownServiceRequested(ServiceHookData $data): void;
}
