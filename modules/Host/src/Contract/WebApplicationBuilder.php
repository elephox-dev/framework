<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Host\WebApplication;

interface WebApplicationBuilder extends ApplicationBuilder
{
	public function getEnvironment(): WebHostEnvironment;

	public function getWebHost(): WebHostBuilder;

	public function getHost(): HostBuilder;

	public function build(): WebApplication;
}
