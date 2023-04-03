<?php
declare(strict_types=1);

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @covers \Elephox\OOR\Str
 *
 * @internal
 */
class StrTest extends TestCase
{
	public function testIs(): void
	{
		static::assertTrue(Str::is('/', '/'));
		static::assertFalse(Str::is('/', ' /'));
		static::assertFalse(Str::is('/', '/a'));
		static::assertTrue(Str::is('foo/*', 'foo/bar/baz'));

		static::assertTrue(Str::is('*@*', 'App\Class@method'));
		static::assertTrue(Str::is('*@*', 'app\Class@'));
		static::assertTrue(Str::is('*@*', '@method'));

		// is case sensitive
		static::assertFalse(Str::is('*BAZ*', 'foo/bar/baz'));
		static::assertFalse(Str::is('*FOO*', 'foo/bar/baz'));
		static::assertFalse(Str::is('A', 'a'));

		// Accepts array of patterns
		static::assertTrue(Str::is(['a*', 'b*'], 'a/'));
		static::assertTrue(Str::is(['a*', 'b*'], 'b/'));
		static::assertFalse(Str::is(['a*', 'b*'], 'f/'));

		// numeric values and patterns
		static::assertFalse(Str::is(['a*', 'b*'], 123));
		static::assertTrue(Str::is(['*2*', 'b*'], 11211));

		static::assertTrue(Str::is('*/foo', 'blah/baz/foo'));

		$valueObject = new class implements Stringable {
			public function __toString(): string {
				return 'foo/bar/baz';
			}
		};
		$patternObject = new class implements Stringable {
			public function __toString(): string {
				return 'foo/*';
			}
		};

		static::assertTrue(Str::is('foo/bar/baz', $valueObject));
		static::assertTrue(Str::is($patternObject, $valueObject));

		// empty patterns
		static::assertFalse(Str::is([], 'test'));

		static::assertFalse(Str::is('', 0));
		static::assertFalse(Str::is([null], 0));
		static::assertTrue(Str::is([null], null));
	}
}
