<?php
declare(strict_types=1);

namespace Elephox\DI;

enum ServiceLifetime
{
	case Transient;
	case Singleton;
}
