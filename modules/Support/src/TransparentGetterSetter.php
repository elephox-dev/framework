<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use Elephox\OOR\Casing;

trait TransparentGetterSetter
{
	/**
	 * @return iterable<mixed, string>
	 */
	protected function buildGetterNames(string $propertyName): iterable
	{
		$getterPropertyName = Casing::toPascal($propertyName);
		yield 'get' . $getterPropertyName;
		yield 'is' . $getterPropertyName;
		yield 'has' . $getterPropertyName;
	}

	/**
	 * @return iterable<mixed, string>
	 */
	protected function buildSetterNames(string $propertyName): iterable
	{
		$setterPropertyName = Casing::toPascal($propertyName);

		yield 'set' . $setterPropertyName;
		yield 'put' . $setterPropertyName;
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
