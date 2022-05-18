<?php
declare(strict_types=1);

namespace Elephox\DI\Hooks;

use Elephox\DI\ServiceDescriptor;

class ServiceReplacedHookData
{
	public function __construct(
		public readonly ServiceDescriptor $oldService,
		public ServiceDescriptor $newService,
	) {
	}
}
