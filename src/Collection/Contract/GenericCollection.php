<?php
declare(strict_types=1);

namespace Philly\Collection\Contract;

/**
 * @template T
 *
 * @extends \Philly\Collection\Contract\Filterable<T>
 * @extends \Philly\Collection\Contract\Mappable<T>
 */
interface GenericCollection extends Filterable, Mappable
{
}
