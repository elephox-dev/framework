<?php
declare(strict_types=1);

namespace Elephox\Database;

use BadMethodCallException;

/**
 * @template TId of int|string
 *
 * @template-implements Contract\Entity<TId>
 */
abstract class AbstractEntity implements Contract\Entity
{
	public function getUniqueId(): string|int
	{
		foreach (['getId', 'getUid', 'getUuid', 'getGuid'] as $idMethod) {
			if (method_exists($this, $idMethod)) {
				return $this->{$idMethod}();
			}
		}

		foreach (['id', 'uid', 'uuid', 'guid'] as $idProperty) {
			if (property_exists($this, $idProperty)) {
				return $this->{$idProperty};
			}
		}

		throw new BadMethodCallException("Could not find unique id method or property. Please override " . __METHOD__);
	}
}
