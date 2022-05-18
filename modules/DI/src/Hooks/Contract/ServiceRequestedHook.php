<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceHookData;

interface ServiceRequestedHook
{
	/**
	 * @template TService of object
	 *
	 * @param ServiceHookData<TService> $data
	 *
	 * @return void
	 */
	public function serviceRequested(ServiceHookData $data): void;
}
