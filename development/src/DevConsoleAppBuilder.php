<?php
declare(strict_types=1);

namespace Elephox\Development\src;

use Elephox\Builder\Whoops\AddsWhoopsHandler;
use Elephox\Console\ConsoleApplicationBuilder;

class DevConsoleAppBuilder extends ConsoleApplicationBuilder
{
	use AddsWhoopsHandler;
}
