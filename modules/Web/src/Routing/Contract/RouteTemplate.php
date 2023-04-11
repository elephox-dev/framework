<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface RouteTemplate
{
	public function getSource(): string;

	public function getVariableNames(): GenericEnumerable;

	public function getDynamicNames(): GenericEnumerable;

	/**
	 * @param array<string, string> $dynamics
	 * @return string
	 */
	public function renderRegExp(array $dynamics): string;
}
