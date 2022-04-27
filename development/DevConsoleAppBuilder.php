<?php
declare(strict_types=1);

namespace Elephox\Development;

use Elephox\Builder\Whoops\AddsWhoopsHandler;
use Elephox\Console\ConsoleApplicationBuilder;

class DevConsoleAppBuilder extends ConsoleApplicationBuilder
{
	use AddsWhoopsHandler;
}
