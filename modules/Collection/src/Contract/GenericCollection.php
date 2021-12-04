<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 *
 * @extends Filterable<T>
 * @extends Mappable<T>
 */
interface GenericCollection extends Filterable, Mappable
{
}
