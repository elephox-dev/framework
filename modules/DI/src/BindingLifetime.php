<?php
declare(strict_types=1);

namespace Philly\DI;

enum BindingLifetime
{
	case Transient;
	case Request;
}
