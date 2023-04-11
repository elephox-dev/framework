<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute\Http;

use Attribute;
use Elephox\Web\Routing\Attribute\AbstractActionAttribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Action extends AbstractActionAttribute
{
}
