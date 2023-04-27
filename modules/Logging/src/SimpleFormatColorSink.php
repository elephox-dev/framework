<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Closure;
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

	/**
	 * @var Closure(int): string
	 */
	private readonly Closure $foregroundOpener;

	/**
	 * @var Closure(int): string
	 */
	private readonly Closure $foregroundCloser;

	/**
	 * @var Closure(int): string
	 */
	private readonly Closure $backgroundOpener;

	/**
	 * @var Closure(int): string
	 */
	private readonly Closure $backgroundCloser;

	/**
	 * @var Closure(array{0: int, 1: int}): string
	 */
	private readonly Closure $optionOpener;

	/**
	 * @var Closure(array{0: int, 1: int}): string
	 */
	private readonly Closure $optionCloser;

	public function __construct(private readonly Sink $innerSink)
	{
		if ($this->getInnerSink()->hasCapability(SinkCapability::AnsiFormatting)) {
			$this->foregroundOpener = static fn (int $code): string => "\033[{$code}m";
			$this->foregroundCloser = static fn (int $previous): string => "\033[{$previous}m";
			$this->backgroundOpener = static fn (int $code): string => "\033[{$code}m";
			$this->backgroundCloser = static fn (int $previous): string => "\033[{$previous}m";
			$this->optionOpener = static fn (array $codes): string => "\033[$codes[0]m";
			$this->optionCloser = static fn (array $codes): string => "\033[$codes[1]m";
		} else {
			$this->foregroundOpener = $this->foregroundCloser = $this->backgroundOpener = $this->backgroundCloser = $this->optionOpener = $this->optionCloser = static fn (): string => '';
		}
	}

	public function write(LogLevel $level, string $message, array $context): void
	{
		$message = $this->replaceSingleCodes(self::FOREGROUND_MAP['default'], self::FOREGROUND_MAP, $message, $this->foregroundOpener, $this->foregroundCloser);
		$message = $this->replaceSingleCodes(self::BACKGROUND_MAP['defaultBack'], self::BACKGROUND_MAP, $message, $this->backgroundOpener, $this->backgroundCloser);
		$message = $this->replaceDoubleCodes($message, $this->optionOpener, $this->optionCloser);

		$this->getInnerSink()->write($level, $message, $context);
	}

	/**
	 * @param int $default
	 * @param array<string, int> $map
	 * @param string $message
	 * @param Closure(int): string $openerGenerator
	 * @param Closure(int): string $closerGenerator
	 *
	 * @return string
	 */
	protected function replaceSingleCodes(int $default, array $map, string $message, Closure $openerGenerator, Closure $closerGenerator): string
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
	 * @param Closure(array{0: int, 1: int}): string $openerGenerator
	 * @param Closure(array{0: int, 1: int}): string $closerGenerator
	 *
	 * @return string
	 */
	protected function replaceDoubleCodes(string $message, Closure $openerGenerator, Closure $closerGenerator): string
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
		return match ($capability) {
			SinkCapability::ElephoxFormatting => true,
			default => $this->getInnerSink()->hasCapability($capability),
		};
	}
}
