<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\Contract\SinkProxy;

class SimpleFormatColorSink implements Sink, SinkProxy
{
	public const FOREGROUND_MAP = [
		'black' => 30,
		'red' => 31,
		'green' => 32,
		'yellow' => 33,
		'blue' => 34,
		'magenta' => 35,
		'cyan' => 36,
		'gray' => 90,
		'white' => 97,
		'default' => 39,
	];

	public const BACKGROUND_MAP = [
		'blackBack' => 40,
		'redBack' => 41,
		'greenBack' => 42,
		'yellowBack' => 43,
		'blueBack' => 44,
		'magentaBack' => 45,
		'cyanBack' => 46,
		'grayBack' => 100,
		'whiteBack' => 107,
		'defaultBack' => 49,
	];

	public const OPTIONS_MAP = [
		'bold' => [1, 22],
		'underline' => [4, 24],
		'blink' => [5, 25],
		'inverse' => [7, 27],
		'hidden' => [8, 28],
	];

	public function __construct(private readonly Sink $innerSink)
	{
	}

	public function write(LogLevel $level, string $message, array $context): void
	{
		$foregroundOpener = $foregroundCloser = $backgroundOpener = $backgroundCloser = $optionOpener = $optionCloser = static fn (): string => '';

		if ($this->getInnerSink()->hasCapability(SinkCapability::AnsiFormatting)) {
			$foregroundOpener = static fn (int $code): string => "\033[{$code}m";
			$foregroundCloser = static fn (int $previous): string => "\033[{$previous}m";
			$backgroundOpener = static fn (int $code): string => "\033[{$code}m";
			$backgroundCloser = static fn (int $previous): string => "\033[{$previous}m";
			$optionOpener = static fn (array $codes): string => "\033[$codes[0]m";
			$optionCloser = static fn (array $codes): string => "\033[$codes[1]m";
		}

		$message = $this->replaceSingleCodes(self::FOREGROUND_MAP['default'], self::FOREGROUND_MAP, $message, $foregroundOpener, $foregroundCloser);
		$message = $this->replaceSingleCodes(self::BACKGROUND_MAP['defaultBack'], self::BACKGROUND_MAP, $message, $backgroundOpener, $backgroundCloser);
		$message = $this->replaceDoubleCodes($message, $optionOpener, $optionCloser);

		$this->getInnerSink()->write($level, $message, $context);
	}

	/**
	 * @param int $default
	 * @param array<string, int> $map
	 * @param string $message
	 * @param callable(int): string $openerGenerator
	 * @param callable(int): string $closerGenerator
	 *
	 * @return string
	 */
	protected function replaceSingleCodes(int $default, array $map, string $message, callable $openerGenerator, callable $closerGenerator): string
	{
		$stack = [];

		/** @var string */
		return preg_replace_callback("/<([\/a-zA-Z]+?)>/", static function (array $matches) use (
			&$stack,
			$default,
			$map,
			$openerGenerator,
			$closerGenerator
		): string {
			[$original, $color] = $matches;
			$closer = false;
			if (str_starts_with($color, '/')) {
				$color = substr($color, 1);
				$closer = true;
			}

			if (!isset($map[$color])) {
				return $original;
			}

			/** @var list<int> $stack */
			if (!$closer) {
				$stack[] = $map[$color];

				return $openerGenerator($map[$color]);
			}

			array_pop($stack);

			$previous = end($stack);
			if (!$previous) {
				$previous = $default;
			}

			return $closerGenerator($previous);
		}, $message);
	}

	/**
	 * @param string $message
	 * @param callable(array{0: int, 1: int}): string $openerGenerator
	 * @param callable(array{0: int, 1: int}): string $closerGenerator
	 *
	 * @return string
	 */
	protected function replaceDoubleCodes(string $message, callable $openerGenerator, callable $closerGenerator): string
	{
		foreach (self::OPTIONS_MAP as $option => $codes) {
			if (!str_contains($message, "<$option>")) {
				continue;
			}

			$opener = $openerGenerator($codes);
			$closer = $closerGenerator($codes);
			$message = (string) preg_replace(
				"/<$option>(.*?)<\/$option>/",
				"$opener$1$closer",
				$message,
			);
		}

		return $message;
	}

	public function getInnerSink(): Sink
	{
		return $this->innerSink;
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $capability === SinkCapability::ElephoxFormatting || $this->getInnerSink()->hasCapability($capability);
	}
}
