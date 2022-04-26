<?php
declare(strict_types=1);

namespace Elephox\Support;

enum CloneAction
{
	case Clone;
	case Skip;
	case Keep;
}
