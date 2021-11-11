<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 */
class UrlTest extends TestCase
{
	public function dataProvider(): array
	{
		return [
			[ '', null, null, null, null, null, '', null, null ],
			[ ':', null, null, null, null, null, ':', null, null ],
			[ '?', null, null, null, null, null, '', '', null ],
			[ '#', null, null, null, null, null, '', null, '' ],
			[ ':?#', null, null, null, null, null, ':', '', '' ],
			[ 's//', null, null, null, 's', null, '//', null, null ],
			[ 'a://', 'a', null, null, null, null, '', null, null ],
			[ '/test', null, null, null, null, null, '/test', null, null ],
			[ 'localhost:8001/test', null, null, null, 'localhost', 8001, '/test', null, null ],
			[ '//domain:123/path', '', null, null, 'domain', 123, '/path', null, null ],
			[ 'someone@somewhere/something', null, 'someone', null, 'somewhere', null, '/something', null, null ],
			[ 'file:///home/test/file.txt', 'file', null, null, null, null, '/home/test/file.txt', null, null ],
			[ 'ssh://git@github.com', 'ssh', 'git', null, 'github.com', null, '', null, null ],
			[ '/get-this?id=123&user_id=23#234', null, null, null, null, null, '/get-this', 'id=123&user_id=23', '234' ],
			[ 'https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https', 'user', 'password', 'localhost', 5000, '/path/to/script.php', 'query=true', 'fragment' ],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testFromString(string $uriString, ?string $scheme, ?string $username, ?string $password, ?string $host, ?int $port, string $path, ?string $query, ?string $fragment): void
	{
		$uri = Url::fromString($uriString);
		self::assertSame($scheme, $uri->getScheme(), "Unexpected scheme.");
		self::assertSame($username, $uri->getUsername(), "Unexpected username.");
		self::assertSame($password, $uri->getPassword(), "Unexpected password.");
		self::assertSame($host, $uri->getHost(), "Unexpected host.");
		self::assertSame($port, $uri->getPort(), "Unexpected port.");
		self::assertSame($path, $uri->getPath(), "Unexpected path.");
		self::assertSame($query, $uri->getQuery(), "Unexpected query.");
		self::assertSame($fragment, $uri->getFragment(), "Unexpected fragment.");
		self::assertSame($uriString, $uri->asString());
	}
}
