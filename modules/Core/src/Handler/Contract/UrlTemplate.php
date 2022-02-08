<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Http\Url;

interface UrlTemplate
{
	public function getSource(): string;

	public function matches(Url $url): bool;

	/**
	 * @param Url $url
	 *
	 * @return GenericKeyedEnumerable<string, string>
	 */
	public function getValues(Url $url): GenericKeyedEnumerable;

	public function getSanitizedSource(): string;
}
