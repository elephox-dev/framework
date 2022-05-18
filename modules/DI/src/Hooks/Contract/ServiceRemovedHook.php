<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks\Contract;

use Elephox\DI\Hooks\ServiceHookData;

interface ServiceRemovedHook
{
	/**
	 * @template TService of object
	 *
	 * @param ServiceHookData<TService> $data
	 *
	 * @return void
	 */
	public function serviceRemoved(ServiceHookData $data): void;
}
