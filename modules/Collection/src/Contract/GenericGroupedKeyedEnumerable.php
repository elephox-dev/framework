<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TGroupKey
 * @template TIteratorKey
 * @template TSource
 *
 * @extends GenericKeyedEnumerable<TGroupKey, Grouping<TGroupKey, TIteratorKey, TSource>>
 */
interface GenericGroupedKeyedEnumerable extends GenericKeyedEnumerable
{
}
