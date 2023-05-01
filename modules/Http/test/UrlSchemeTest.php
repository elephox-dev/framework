<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Http\CustomUrlScheme
 *
 * @internal
 */
final class UrlSchemeTest extends TestCase
{
	public function defaultPortProvider(): iterable
	{
		yield [UrlScheme::FILE, null];
		yield [UrlScheme::FTP, 21];
		yield [UrlScheme::SFTP, 22];
		yield [UrlScheme::HTTP, 80];
		yield [UrlScheme::HTTPS, 443];
		yield [UrlScheme::MAILTO, null];
		yield [UrlScheme::SSH, 22];
		yield [UrlScheme::MYSQL, 3306];
	}

	/**
	 * @dataProvider defaultPortProvider
	 *
	 * @param ?int $port
	 * @param UrlScheme $scheme
	 */
	public function testDefaultPorts(UrlScheme $scheme, ?int $port): void
	{
		self::assertSame($scheme->getDefaultPort(), $port);
	}

	public function testCustomScheme(): void
	{
		$scheme = new CustomUrlScheme('custom', 80);

		self::assertSame('custom', $scheme->getScheme());
		self::assertSame(80, $scheme->getDefaultPort());
	}
}
