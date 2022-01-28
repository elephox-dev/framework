<?php
declare(strict_types=1);

namespace Elephox\Entity\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique extends AbstractPropertyAttribute
{
}
