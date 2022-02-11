<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

interface InMemoryCache extends Cache
{
	public function getConfiguration(): InMemoryCacheConfiguration;
}
