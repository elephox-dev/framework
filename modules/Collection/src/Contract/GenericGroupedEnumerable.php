<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TElement
 *
 * @extends GenericEnumerable<Grouping<TKey, mixed, TElement>>
 */
interface GenericGroupedEnumerable extends GenericEnumerable
{
}
