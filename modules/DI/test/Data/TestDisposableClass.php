<?php
declare(strict_types=1);

namespace Elephox\DI\Data;

class TestDisposableClass implements TestDisposableInterface {
	public static int $disposeCount = 0;

	public function dispose(): void {
		self::$disposeCount++;
	}
}
