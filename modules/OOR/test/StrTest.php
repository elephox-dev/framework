<?php
declare(strict_types=1);

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @covers \Elephox\OOR\Str
 */
class StrTest extends TestCase
{
	public function testIs(): void
	{
		self::assertTrue(Str::is('/', '/'));
		self::assertFalse(Str::is('/', ' /'));
		self::assertFalse(Str::is('/', '/a'));
		self::assertTrue(Str::is('foo/*', 'foo/bar/baz'));

		self::assertTrue(Str::is('*@*', 'App\Class@method'));
		self::assertTrue(Str::is('*@*', 'app\Class@'));
		self::assertTrue(Str::is('*@*', '@method'));

		// is case sensitive
		self::assertFalse(Str::is('*BAZ*', 'foo/bar/baz'));
		self::assertFalse(Str::is('*FOO*', 'foo/bar/baz'));
		self::assertFalse(Str::is('A', 'a'));

		// Accepts array of patterns
		self::assertTrue(Str::is(['a*', 'b*'], 'a/'));
		self::assertTrue(Str::is(['a*', 'b*'], 'b/'));
		self::assertFalse(Str::is(['a*', 'b*'], 'f/'));

		// numeric values and patterns
		self::assertFalse(Str::is(['a*', 'b*'], 123));
		self::assertTrue(Str::is(['*2*', 'b*'], 11211));

		self::assertTrue(Str::is('*/foo', 'blah/baz/foo'));

		$valueObject = new class implements Stringable { public function __toString() { return 'foo/bar/baz'; }};
		$patternObject = new class implements Stringable { public function __toString() { return 'foo/*'; }};

		self::assertTrue(Str::is('foo/bar/baz', $valueObject));
		self::assertTrue(Str::is($patternObject, $valueObject));

		// empty patterns
		self::assertFalse(Str::is([], 'test'));

		self::assertFalse(Str::is('', 0));
		self::assertFalse(Str::is([null], 0));
		self::assertTrue(Str::is([null], null));
	}
}
