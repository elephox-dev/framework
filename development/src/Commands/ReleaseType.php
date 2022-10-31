<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

enum ReleaseType: string
{
	case Major = 'major';
	case Minor = 'minor';
	case Patch = 'patch';
	case Preview = 'preview';
}
