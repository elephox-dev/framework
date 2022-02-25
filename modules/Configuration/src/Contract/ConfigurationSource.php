<?php
declare(strict_types=1);

namespace Elephox\Configuration\Contract;

interface ConfigurationSource
{
	public function build(ConfigurationBuilder $builder): ConfigurationProvider;
}
