<?php
declare(strict_types=1);

namespace Elephox\Support;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class CloneBehaviour
{
	public function __construct(public CloneAction $action)
	{
	}
}
