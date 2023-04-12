<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\Web\Routing\RouteTemplateVariable;

interface RouteTemplate
{
	public function getSource(): string;

	/**
	 * @return GenericReadonlyList<RouteTemplateVariable>
	 */
	public function getVariables(): GenericReadonlyList;

	/**
	 * @param array<string, string> $dynamics
	 *
	 * @return string
	 */
	public function renderRegExp(array $dynamics): string;
}
