<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\OOR\Casing;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
final class HeaderNameTest extends TestCase
{
	public function testCanBeDuplicate(): void
	{
		foreach (HeaderName::cases() as $name) {
			if ($name === HeaderName::SetCookie) {
				self::assertTrue($name->canBeDuplicate());
			} else {
				self::assertFalse($name->canBeDuplicate());
			}
		}
	}

	public function testIsOnlyRequest(): void
	{
		foreach (HeaderName::cases() as $name) {
			if (in_array(
				$name,
				[
					HeaderName::Expect,
					HeaderName::Host,
					HeaderName::MaxForwards,
					HeaderName::Pragma,
					HeaderName::Range,
					HeaderName::IfMatch,
					HeaderName::IfNoneMatch,
					HeaderName::IfModifiedSince,
					HeaderName::IfUnmodifiedSince,
					HeaderName::IfRange,
					HeaderName::Accept,
					HeaderName::AcceptCharset,
					HeaderName::AcceptEncoding,
					HeaderName::AcceptLanguage,
					HeaderName::Authorization,
					HeaderName::ProxyAuthorization,
					HeaderName::From,
					HeaderName::Referer,
					HeaderName::Cookie,
					HeaderName::UserAgent,
				],
				true,
			)) {
				self::assertTrue($name->isOnlyRequest());
			} else {
				self::assertFalse($name->isOnlyRequest());
			}
		}
	}

	public function testIsOnlyResponse(): void
	{
		foreach (HeaderName::cases() as $name) {
			if (in_array(
				$name,
				[
					HeaderName::Age,
					HeaderName::Expires,
					HeaderName::Date,
					HeaderName::Location,
					HeaderName::RetryAfter,
					HeaderName::Vary,
					HeaderName::Warning,
					HeaderName::ETag,
					HeaderName::LastModified,
					HeaderName::WwwAuthenticate,
					HeaderName::ProxyAuthenticate,
					HeaderName::AcceptRanges,
					HeaderName::Allow,
					HeaderName::SetCookie,
					HeaderName::Server,
				],
				true,
			)) {
				self::assertTrue($name->isOnlyResponse());
			} else {
				self::assertFalse($name->isOnlyResponse());
			}
		}
	}

	public function testTryFromIgnoreCase(): void
	{
		foreach (HeaderName::cases() as $name) {
			$lowercase = strtolower($name->value);
			$uppercase = strtoupper($name->value);
			$randomCase = Casing::random($name->value, (int) ($_ENV['ELEPHOX_TEST_SEED'] ?? time()));

			self::assertSame($name, HeaderName::tryFromIgnoreCase($name->value));
			self::assertSame($name, HeaderName::tryFromIgnoreCase($lowercase));
			self::assertSame($name, HeaderName::tryFromIgnoreCase($uppercase));
			self::assertSame($name, HeaderName::tryFromIgnoreCase($randomCase));
		}

		self::assertNull(HeaderName::tryFromIgnoreCase('foo'));
	}

	public function invalidHeaderNameProvider(): iterable
	{
		yield [''];
		yield [' '];
	}

	/**
	 * @dataProvider invalidHeaderNameProvider
	 *
	 * @param string $name
	 */
	public function testTryFromIgnoreCaseThrowsForInvalidInput(string $name): void
	{
		$this->expectException(InvalidArgumentException::class);

		HeaderName::tryFromIgnoreCase($name);
	}
}
