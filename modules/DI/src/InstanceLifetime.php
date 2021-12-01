<?php
declare(strict_types=1);

namespace Elephox\DI;

enum InstanceLifetime
{
	case Transient;
	case Singleton;
}
