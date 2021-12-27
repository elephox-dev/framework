<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 * @uses \Elephox\Http\Contract\Url
 */
class UrlTest extends TestCase
{
	public function dataProvider(): array
	{
		return [
			[ '', '/', '', '', '', '', null, '', '', '' ],
			[ ':', '/:', '', '', '', '', null, ':', '', '' ],
			[ '?', '/?', '', '', '', '', null, '', '', '' ],
			[ '#', '/#', '', '', '', '', null, '', '', '' ],
			[ ':?#', '/:?#', '', '', '', '', null, ':', '', '' ],
			[ 's//', '//s//', '', '', '', 's', null, '//', '', '' ],
			[ 'a://', 'a:/', 'a', '', '', '', null, '', '', '' ],
			[ '/test', '/test', '', '', '', '', null, '/test', '', '' ],
			[ 'localhost:8001/test', '//localhost:8001/test', '', '', '', 'localhost', 8001, '/test', '', '' ],
			[ '//domain:123/path', '://domain:123/path', '', '', '', 'domain', 123, '/path', '', '' ],
			[ 'someone@somewhere/something', '//someone@somewhere/something', '', 'someone', '', 'somewhere', null, '/something', '', '' ],
			[ 'file:///home/test/file.txt', 'file:/home/test/file.txt', 'file', '', '', '', null, '/home/test/file.txt', '', '' ],
			[ 'ssh://git@github.com', 'ssh://git@github.com/', 'ssh', 'git', '', 'github.com', null, '', '', '' ],
			[ 'mysql://root:root@localhost:3306/test', 'mysql://root:root@localhost/test', 'mysql', 'root', 'root', 'localhost', null, '/test', '', '' ],
			[ 'custom://localhost/test', 'custom://localhost/test', 'custom', '', '', 'localhost', null, '/test', '', '' ],
			[ '/get-this?id=123&user_id=23#234', '/get-this?id=123&user_id=23#234', '', '', '', '', null, '/get-this', 'id=123&user_id=23', '234' ],
			[ 'https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https', 'user', 'password', 'localhost', 5000, '/path/to/script.php', 'query=true', 'fragment' ],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testFromString(string $uriString, string $toString, ?string $scheme, ?string $username, ?string $password, ?string $host, ?int $port, string $path, ?string $query, ?string $fragment): void
	{
		$uri = Url::fromString($uriString);
		self::assertSame($scheme, $uri->getScheme(), "Unexpected scheme.");
		self::assertSame($scheme !== null ? UrlScheme::tryFrom($scheme) : null, $uri->getUrlScheme(), "Unexpected scheme.");
		self::assertSame($username, $uri->getUsername(), "Unexpected username.");
		self::assertSame($password, $uri->getPassword(), "Unexpected password.");
		self::assertSame($host, $uri->getHost(), "Unexpected host.");
		self::assertSame($port, $uri->getPort(), "Unexpected port.");
		self::assertSame($path, $uri->getPath(), "Unexpected path.");
		self::assertSame($query, $uri->getQuery(), "Unexpected query.");
		self::assertSame($fragment, $uri->getFragment(), "Unexpected fragment.");
		self::assertSame($toString, (string)$uri);

		$userInfo = empty($username) ? '' : ($username . (empty($password) ? '' : (':' . $password)));
		$authority = empty($host) ? '' : ($host . ($port === null ? '' : (':' . $port)));
		self::assertEquals([
			'scheme' => $scheme,
			'username' => $username,
			'password' => $password,
			'host' => $host,
			'port' => $port,
			'path' => $path,
			'query' => $query,
			'fragment' => $fragment,
			'authority' => empty($userInfo) ? $authority : ($userInfo . '@' . $authority),
			'userInfo' => $userInfo,
		], $uri->asArray());
	}

	public function testWithUrlScheme(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withScheme(UrlScheme::HTTPS);
		self::assertSame('https://localhost/test', (string)$uri);
	}

	public function testWithNullUrlScheme(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withScheme(null);
		self::assertSame('//localhost/test', (string)$uri);
	}

	public function testWithUsername(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withUserInfo('user');
		self::assertSame('https://user@localhost/test', (string)$uri);
	}

	public function testWithPassword(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withUserInfo('user', 'password');
		self::assertSame('https://user:password@localhost/test', (string)$uri);
	}

	public function testWithHost(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withHost('example.com');
		self::assertSame('https://example.com/test', (string)$uri);
	}

	public function testWithPort(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withPort(8080);
		self::assertSame('https://localhost:8080/test', (string)$uri);
	}

	public function testWithPath(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withPath('/path/to/script.php');
		self::assertSame('https://localhost/path/to/script.php', (string)$uri);
	}

	public function testWithQuery(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withQuery('query=true');
		self::assertSame('https://localhost/test?query=true', (string)$uri);
	}

	public function testWithFragment(): void
	{
		$uri = Url::fromString('https://localhost/test');
		$uri = $uri->withFragment('fragment');
		self::assertSame('https://localhost/test#fragment', (string)$uri);
	}
}
