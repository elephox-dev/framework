<?php
declare(strict_types=1);

namespace Elephox\OOR;

readonly class Range
{
	public int $length;
	public int $sign;

	public function __construct(
		public int $from,
		public int $to,
	) {
		$this->length = abs($to - $from) + 1;
		$this->sign = $to >= $from ? 1 : -1;
	}

	public function of(string $s): string
	{
		return substr($s, $this->from, $this->length);
	}
}
