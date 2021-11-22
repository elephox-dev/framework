<?php
declare(strict_types=1);

namespace Elephox\Database\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Optional extends DatabaseAttribute
{
}
