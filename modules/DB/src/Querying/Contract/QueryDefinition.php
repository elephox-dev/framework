<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Stringable;

/**
 * @template TResult
 */
interface QueryDefinition extends Stringable
{
	public function getVerb(): string;

	/**
	 * @return non-empty-list<string|QueryDefinition|QueryExpression>
	 */
	public function getParams(): array;
}
