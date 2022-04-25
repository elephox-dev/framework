<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;

abstract class AbstractStream implements Stream
{
	public function readLine(string $eol = "\r\n"): string
	{
		$line = '';
		while (!$this->eof()) {
			$line .= $this->read(1);

			if (str_ends_with($line, $eol)) {
				return rtrim($line, $eol);
			}
		}

		return $line;
	}

	public function readAllLines(string $eol = "\r\n"): iterable
	{
		$line = 0;
		while (!$this->eof()) {
			yield $line++ => $this->readLine($eol);
		}
	}

	public function readByte(): int
	{
		return ord($this->read(1));
	}
}
