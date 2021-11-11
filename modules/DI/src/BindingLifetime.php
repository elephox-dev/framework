<?php
declare(strict_types=1);

namespace Elephox\DI;

enum BindingLifetime
{
	case Transient;
	case Request;
}
