<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Http\QueryMap
 * @covers \Elephox\Http\CustomUrlScheme
 * @covers \Elephox\OOR\Casing
 *
 * @internal
 */
final class UrlTest extends TestCase
{
	public function dataProvider(): array
	{
		return [
			['', '/', null, null, null, null, null, '', '', null],
			[':', '/:', null, null, null, null, null, ':', '', null],
			['?', '/', null, null, null, null, null, '', '', null],
			['#', '/', null, null, null, null, null, '', '', null],
			[':?#', '/:', null, null, null, null, null, ':', '', null],
			['s//', '/s//', null, null, null, null, null, 's//', '', null],
			['a://', 'a:/', 'a', null, null, null, null, '', '', null],
			['/test', '/test', null, null, null, null, null, '/test', '', null],
			['foo/bar', '/foo/bar', null, null, null, null, null, 'foo/bar', '', null],
			['localhost:8001/test', '//localhost:8001/test', null, null, null, 'localhost', 8001, '/test', '', null],
			['//domain:123/path', '//domain:123/path', null, null, null, 'domain', 123, '/path', '', null],
			['someone@somewhere/something', '//someone@somewhere/something', null, 'someone', null, 'somewhere', null, '/something', '', null],
			['file:///home/test/file.txt', 'file:/home/test/file.txt', 'file', null, null, null, null, 'home/test/file.txt', '', null],
			['ssh://git@github.com', 'ssh://git@github.com/', 'ssh', 'git', null, 'github.com', null, '', '', null],
			['https://example.com/path with spaces', 'https://example.com/path%20with%20spaces', 'https', null, null, 'example.com', null, '/path%20with%20spaces', '', null],
			['mysql://root:root@localhost:3306/test', 'mysql://root:root@localhost/test', 'mysql', 'root', 'root', 'localhost', null, '/test', '', null],
			['custom://localhost/test', 'custom://localhost/test', 'custom', null, null, 'localhost', null, '/test', '', null],
			['/get-this?id=123&user_id=23#234', '/get-this?id=123&user_id=23#234', null, null, null, null, null, '/get-this', 'id=123&user_id=23', '234'],
			['https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https://user:password@localhost:5000/path/to/script.php?query=true#fragment', 'https', 'user', 'password', 'localhost', 5000, '/path/to/script.php', 'query=true', 'fragment'],
			[new class implements Stringable {
				public function __toString(): string
				{
					return '';
				}
			}, '/', null, null, null, null, null, '', '', null],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testFromString(string|Stringable $uriString, string $toString, ?string $scheme, ?string $username, ?string $password, ?string $host, ?int $port, string $path, ?string $query, ?string $fragment): void
	{
		$uri = Url::fromString($uriString);
		self::assertSame($scheme, $uri->scheme?->getScheme(), "Unexpected scheme in $uriString.");
		self::assertSame($username, $uri->username, "Unexpected username in $uriString.");
		self::assertSame($password, $uri->password, "Unexpected password in $uriString.");
		self::assertSame($host, $uri->host, "Unexpected host in $uriString.");
		self::assertSame($port, $uri->port, "Unexpected port in $uriString.");
		self::assertSame($path, $uri->path, "Unexpected path in $uriString.");
		self::assertSame($query, (string) $uri->queryMap, "Unexpected query in $uriString.");
		self::assertSame($fragment, $uri->fragment, "Unexpected fragment in $uriString.");
		self::assertSame($toString, (string) $uri);

		if (empty($query)) {
			$queryMap = null;
		} else {
			$queryMap = [];
			parse_str($query, $queryMap);
		}

		$userInfo = empty($username) ? '' : ($username . (empty($password) ? '' : (':' . $password)));
		$hostAuthorityPart = empty($host) ? '' : ($host . ($port === null ? '' : (':' . $port)));
		$authority = empty($userInfo) ? $hostAuthorityPart : ($userInfo . '@' . $hostAuthorityPart);

		$array = $uri->toArray();
		self::assertSame($scheme, $array['scheme']?->getScheme());
		self::assertSame($username, $array['username']);
		self::assertSame($password, $array['password']);
		self::assertSame($host, $array['host']);
		self::assertSame($port, $array['port']);
		self::assertSame($authority, $array['authority']);
		self::assertSame($userInfo, $array['userInfo']);
		self::assertSame($path, $array['path']);
		self::assertSame($queryMap, $array['query']?->toArray());
		self::assertSame($fragment, $array['fragment']);
	}

	public function testWith(): void
	{
		$uri = Url::fromString('/');
		self::assertSame('/', (string) $uri);

		$uri = $uri->with()
			->scheme(UrlScheme::HTTPS)
			->host('example.com')
			->path('/test')
			->get()
		;
		self::assertSame('https://example.com/test', (string) $uri);

		$uri = $uri->with()
			->userInfo('user', 'password')
			->fragment('fragment')
			->get()
		;
		self::assertSame('https://user:password@example.com/test#fragment', (string) $uri);

		$query = new QueryMap();
		$query['test'] = 'true';

		$uri = $uri->with()
			->queryMap($query)
			->port(8080)
			->get()
		;
		self::assertSame('https://user:password@example.com:8080/test?test=true#fragment', (string) $uri);
	}
}
