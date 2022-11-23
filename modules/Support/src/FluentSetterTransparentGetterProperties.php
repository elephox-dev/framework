<?php
declare(strict_types=1);

namespace Elephox\Support;

trait FluentSetterTransparentGetterProperties
{
	use TransparentProperties {
		TransparentProperties::_transparentSet as private _traitTransparentSet;
	}

	public function _transparentSet(string $name, mixed $value): self
	{
		$this->_traitTransparentSet($name, $value);

		return $this;
	}
}
