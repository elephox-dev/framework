<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\OOR\Range;

readonly class RouteTemplateVariable
{
	public function __construct(
		public string $name,
		public string $type,
		public Range $position,
	) {
	}

	public function getTypePattern(): string
	{
		return match ($this->type) {
			'int' => '\d+',
			'*' => '.*',
			default => '[^}/]+',
		};
	}
}
