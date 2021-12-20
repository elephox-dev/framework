<?php
declare(strict_types=1);

namespace Elephox\Database\Attributes;

use JetBrains\PhpStorm\Pure;

class AttributeMetaDataBuilder
{
	private bool $isOptional = false;
	private bool $isGenerated = false;

	public function setIsOptional(bool $isOptional): void
	{
		$this->isOptional = $isOptional;
	}

	public function setIsGenerated(bool $isGenerated): void
	{
		$this->isGenerated = $isGenerated;
	}

	#[Pure] public function build(): AttributeMetaData
	{
		return new AttributeMetaData($this->isOptional, $this->isGenerated);
	}
}
