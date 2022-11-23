<?php
declare(strict_types=1);

namespace Elephox\Support;

use BadMethodCallException;
use Elephox\OOR\Casing;

trait TransparentProperties
{
	use GetterSetterPrefixBuilder;

	protected function _buildPropertyName(string $prefix, string $method): string
	{
		$name = mb_substr($method, mb_strlen($prefix), encoding: 'UTF-8');

		return Casing::toSnake($name);
	}

	protected function _transparentGet(string $propertyName): mixed
	{
		return $this->{$propertyName};
	}

	protected function _transparentSet(string $propertyName, mixed $value): mixed
	{
		return $this->{$propertyName} = $value;
	}

	public function __call(string $name, array $args)
	{
		foreach ($this->_buildGetterPrefixes() as $prefix) {
			if (!str_starts_with($name, $prefix)) {
				continue;
			}

			$propertyName = $this->_buildPropertyName($prefix, $name);

			if (property_exists($this, $propertyName)) {
				return $this->_transparentGet($propertyName);
			}
		}

		if (count($args) === 0) {
			throw new BadMethodCallException(sprintf('No property for reading could be found using %s::%s. If you intend to set a value, pass at least one argument.', __CLASS__, $name));
		}

		foreach ($this->_buildSetterPrefixes() as $prefix) {
			if (!str_starts_with($name, $prefix)) {
				continue;
			}

			$propertyName = $this->_buildPropertyName($prefix, $name);

			if (property_exists($this, $propertyName)) {
				return $this->_transparentSet($propertyName, $args[0]);
			}
		}

		throw new BadMethodCallException(sprintf('Unknown method %s::%s()', __CLASS__, $name));
	}
}
