<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

interface InMemoryPool extends Pool
{
	public function getConfiguration(): InMemoryCacheConfiguration;
}
