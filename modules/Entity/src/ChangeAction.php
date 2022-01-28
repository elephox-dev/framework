<?php
declare(strict_types=1);

namespace Elephox\Entity;

enum ChangeAction
{
	case Created;
	case Updated;
	case Deleted;
}
