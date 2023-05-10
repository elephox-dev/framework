<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

final readonly class QueryParameter implements Contract\QueryParameter
{

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __construct(
		private string $name,
		private mixed $value,
	) {
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
