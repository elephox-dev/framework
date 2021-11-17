<?php
declare(strict_types=1);

namespace Elephox\Database;

use BadMethodCallException;

abstract class AbstractEntity implements Contract\Entity
{
	public function getUniqueId(): string|int
	{
		/** @var string|int|null $idVal */
		$idVal = null;
		foreach (['getId', 'getUid', 'getUuid', 'getGuid'] as $idMethod) {
			if (method_exists($this, $idMethod)) {
				/** @var mixed $idVal */
				$idVal = $this->{$idMethod}();

				break;
			}
		}

		if (is_int($idVal) || is_string($idVal)) {
			return $idVal;
		}

		foreach (['id', 'uid', 'uuid', 'guid'] as $idProperty) {
			if (property_exists($this, $idProperty)) {
				/** @var mixed $idVal */
				$idVal = $this->{$idProperty};

				break;
			}
		}

		if (is_int($idVal) || is_string($idVal)) {
			return $idVal;
		}

		throw new BadMethodCallException("Could not find unique id method or property. Please override " . __METHOD__);
	}
}
