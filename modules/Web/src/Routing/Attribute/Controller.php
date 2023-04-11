<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute;

use Attribute;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Controller extends AbstractRoutingAttribute implements ControllerAttribute
{
}
