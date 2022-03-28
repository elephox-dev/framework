<?php
declare(strict_types=1);

namespace Elephox\DI;

enum ServiceLifetime: string
{
	case Transient = 'transient';
	case Singleton = 'singleton';
}
