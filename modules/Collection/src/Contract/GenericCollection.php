<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Elephox\Support\Contract\DeepCloneable;

/**
 * @template T
 *
 * @extends Filterable<T>
 * @extends Mappable<T>
 */
interface GenericCollection extends DeepCloneable
{
}
