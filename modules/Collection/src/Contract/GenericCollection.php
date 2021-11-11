<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 *
 * @extends \Elephox\Collection\Contract\Filterable<T>
 * @extends \Elephox\Collection\Contract\Mappable<T>
 */
interface GenericCollection extends Filterable, Mappable
{
}
