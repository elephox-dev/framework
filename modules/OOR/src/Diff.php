<?php
declare(strict_types=1);

namespace Elephox\OOR;

enum Diff
{
	case Normal;
	case Assoc;
	case Key;
}
