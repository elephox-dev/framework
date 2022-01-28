<?php
declare(strict_types=1);

namespace Elephox\Entity\Contract;

use Elephox\Collection\Contract\GenericSet;

/**
 * @template T
 *
 * @extends GenericSet<T>
 */
interface EntitySet extends GenericSet
{
	/**
	 * @return class-string<T>
	 */
	public function getEntityClass(): string;
}
