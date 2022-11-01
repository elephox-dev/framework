<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use Elephox\OOR\Casing;

trait TransparentProperties
{
	use GetterSetterPrefixBuilder;

	protected function buildPropertyName(string $prefix, string $method): string
	{
		$name = mb_substr($method, mb_strlen($prefix), encoding: 'UTF-8');

		return Casing::toSnake($name);
	}

	public function __call(string $name, array $args)
	{
		foreach ($this->buildGetterPrefixes() as $prefix) {
			if (!str_starts_with($name, $prefix)) {
				continue;
			}

			$propertyName = $this->buildPropertyName($prefix, $name);

			if (property_exists($this, $propertyName)) {
				return $this->{$propertyName};
			}
		}

		if (count($args) === 0) {
			throw new BadMethodCallException(sprintf('No property for reading could be found using %s::%s. If you intend to set a value, pass at least one argument.', __CLASS__, $name));
		}

		foreach ($this->buildSetterPrefixes() as $prefix) {
			if (!str_starts_with($name, $prefix)) {
				continue;
			}

			$propertyName = $this->buildPropertyName($prefix, $name);

			if (property_exists($this, $propertyName)) {
				return $this->{$propertyName} = $args[0];
			}
		}

		throw new BadMethodCallException(sprintf('Unknown method %s::%s()', __CLASS__, $name));
	}
}
