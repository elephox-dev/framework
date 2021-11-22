<?php
declare(strict_types=1);

namespace Elephox\Database\Attributes;

class AttributeMetaData
{
	public function __construct(
		public readonly bool $optional,
		public readonly bool $generated,
	)
	{
	}
}
