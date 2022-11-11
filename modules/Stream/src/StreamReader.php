<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Generator;

trait StreamReader
{
	abstract public function eof(): bool;

	abstract public function read(int $length): string;

	public function readChar(string $encoding = 'UTF-8'): string
	{
		$buffer = '';
		$read = 0;
		do {
			$buffer .= $this->read(1);
			$read++;

			// either mbstring is able to convert the buffer to a multibyte character,
			// or we abort at 4 bytes read (max length for most multibyte encodings)
		} while (mb_ord($buffer, $encoding) === false && $read < 4);

		return $buffer;
	}

	public function readLine(string $eol = "\r\n", string $encoding = 'UTF-8'): string
	{
		$line = '';
		while (!$this->eof()) {
			$line .= $this->readChar($encoding);

			if (($terminatedLine = mb_strstr($line, $eol, before_needle: true, encoding: $encoding)) !== false) {
				return $terminatedLine;
			}
		}

		return $line;
	}

	/**
	 * @return iterable<int, string>
	 */
	public function readAllLines(string $eol = "\r\n", string $encoding = 'UTF-8'): iterable
	{
		$line = 0;
		while (!$this->eof()) {
			yield $line++ => $this->readLine($eol, $encoding);
		}
	}

	/**
	 * @return int<0, 255>
	 */
	public function readByte(): int
	{
		/** @var int<0, 255> */
		return ord($this->read(1));
	}

	/**
	 * @return iterable<int, int<0, 255>>
	 */
	public function readBytes(int $length, int $chunkSize = 1024): iterable
	{
		$yielded = 0;
		while (!$this->eof() && $yielded < $length) {
			$buffer = $this->read($chunkSize);

			for ($i = 0, $iMax = strlen($buffer); $i < $iMax && $yielded < $length; $i++) {
				/** @var int<0, 255> $byte */
				$byte = ord($buffer[$i]);

				yield $yielded++ => $byte;
			}
		}
	}
}
