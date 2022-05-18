<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

class ServiceReplacedHookData
{
	/**
	 * @param ServiceDescriptor<object, object> $oldService
	 * @param ServiceDescriptor<object, object> $newService
	 * @param bool $cancel
	 */
	public function __construct(
		public readonly ServiceDescriptor $oldService,
		public readonly ServiceDescriptor $newService,
		public bool $cancel = false,
	) {
	}
}
