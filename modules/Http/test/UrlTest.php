<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Http\QueryMap
 * @covers \Elephox\Http\CustomUrlScheme
 */
class UrlTest extends TestCase
{
	public function dataProvider(): array
	{
		return [
			[ '', '/', null, null, null, null, null, '', '', null ],
			[ ':', '/:', null, null, null, null, null, ':', '', null ],
			[ '?', '/?', null, null, null, null, null, '', '', null ],
			[ '#', '/#', null, null, null, null, null, '', '', '' ],
			[ ':?#', '/:?#', null, null, null, null, null, ':', '', '' ],
			[ 's//', '//s//', null, null, null, 's', null, '//', '', null ],
			[ 'a://', 'a:/', 'a', null, null, null, null, '', '', null ],
			[ '/test', '/test', null, null, null, null, null, '/test', '', null ],
			[ 'localhost:8001/test', '//localhost:8001/test', null, null, null, 'localhost', 8001, '/test', '', null ],
			[ '//domain:123/path', '//domain:123/path', null, null, null, 'domain', 123, '/path', '', null ],
			[ 'someone@somewhere/something', '//someone@somewhere/something', null, 'someone', null, 'somewhere', null, '/something', '', null ],
			[ 'file:///home/test/file.txt', 'file:/home/test/file.txt', 'file', null, null, null, null, '/home/test/file.txt', '', null ],
			[ 'ssh://git@github.com', 'ssh://git@github.com/', 'ssh', 'git', null, 'github.com', null, '', '', null ],
			[ 'mysql://root:root@localhost:3306/test', 'mysql://root:root@localhost/test', 'mysql', 'root', 'root', 'localhost', null, '/test', '', null ],
			[ 'custom://localhost/test', 'custom://localhost/test', 'custom', null, null, 'localhost', null, '/test', '', null ],
			[ '/get-this?id=123&user_id=23#234', '/get-this?id=123&user_id=23#234', null, null, null, null, null, '/get-this', 'id=123&user_id=23', '234' ],
			[ 'https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https', 'user', 'password', 'localhost', 5000, '/path/to/script.php', 'query=true', 'fragment' ],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testFromString(string $uriString, string $toString, ?string $scheme, ?string $username, ?string $password, ?string $host, ?int $port, string $path, string $query, ?string $fragment): void
	{
		$uri = Url::fromString($uriString);
		self::assertSame($scheme, $uri->scheme?->getScheme(), "Unexpected scheme.");
		self::assertSame($username, $uri->username, "Unexpected username.");
		self::assertSame($password, $uri->password, "Unexpected password.");
		self::assertSame($host, $uri->host, "Unexpected host.");
		self::assertSame($port, $uri->port, "Unexpected port.");
		self::assertSame($path, $uri->path, "Unexpected path.");
		self::assertSame($query, (string)$uri->queryMap, "Unexpected query.");
		self::assertSame($fragment, $uri->fragment, "Unexpected fragment.");
		self::assertSame($toString, (string)$uri);

		$userInfo = empty($username) ? null : ($username . (empty($password) ? null : (':' . $password)));
		$authority = empty($host) ? null : ($host . ($port === null ? null : (':' . $port)));
		self::assertEquals([
			'scheme' => $scheme !== null ? (UrlScheme::tryFrom($scheme) ?? new CustomUrlScheme($scheme)) : null,
			'username' => $username,
			'password' => $password,
			'host' => $host,
			'port' => $port,
			'path' => $path,
			'query' => $query,
			'fragment' => $fragment,
			'authority' => empty($userInfo) ? $authority : ($userInfo . '@' . $authority),
			'userInfo' => $userInfo,
		], $uri->toArray());
	}
}
