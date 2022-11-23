<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use Elephox\OOR\Casing;

trait TransparentGetterSetter
{
	use GetterSetterPrefixBuilder;

	/**
	 * @return iterable<mixed, string>
	 *
	 * @param string $propertyName
	 */
	protected function buildGetterNames(string $propertyName): iterable
	{
		$getterPropertyName = str_contains($propertyName, '_') ? Casing::toPascal($propertyName) : ucfirst($propertyName);

		foreach ($this->_buildGetterPrefixes() as $prefix) {
			yield $prefix . $getterPropertyName;
		}
	}

	/**
	 * @return iterable<mixed, string>
	 *
	 * @param string $propertyName
	 */
	protected function buildSetterNames(string $propertyName): iterable
	{
		$setterPropertyName = str_contains($propertyName, '_') ? Casing::toPascal($propertyName) : ucfirst($propertyName);

		foreach ($this->_buildSetterPrefixes() as $prefix) {
			yield $prefix . $setterPropertyName;
		}
	}

	public function __get(string $name)
	{
		$tried = [];
		foreach ($this->buildGetterNames($name) as $method) {
			if (method_exists($this, $method)) {
				return $this->$method();
			}

			$tried[] = $method;
		}

		throw new BadMethodCallException('None of the tried getter methods exists: ' . implode(', ', $tried));
	}

	public function __set(string $name, mixed $value)
	{
		$tried = [];
		foreach ($this->buildSetterNames($name) as $method) {
			if (method_exists($this, $method)) {
				return $this->$method($value);
			}

			$tried[] = $method;
		}

		throw new BadMethodCallException('None of the tried setter methods exists: ' . implode(', ', $tried));
	}

	public function __isset(string $name)
	{
		foreach ($this->buildGetterNames($name) as $method) {
			if (method_exists($this, $method)) {
				return true;
			}
		}

		return false;
	}
}
