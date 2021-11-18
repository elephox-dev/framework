<?php
declare(strict_types=1);

namespace Elephox\Database;

use BadMethodCallException;

abstract class AbstractEntity implements Contract\Entity
{
	public function getUniqueIdProperty(): string
	{
		foreach (['id', 'uid', 'uuid', 'guid'] as $idProperty) {
			if (property_exists($this, $idProperty)) {
				return $idProperty;
			}
		}

		throw new BadMethodCallException("Could not find unique id method or property. Please override " . __METHOD__);
	}

	public function getUniqueId(): null|string|int
	{
		$idVal = $this->{$this->getUniqueIdProperty()};

		if ($idVal === null || is_int($idVal) || is_string($idVal)) {
			return $idVal;
		}

		throw new BadMethodCallException("Unique id property did not return a valid ID. Please override " . __METHOD__);
	}
}
