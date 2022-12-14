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
class UrlTest extends TestCase
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
		static::assertSame($scheme, $uri->scheme?->getScheme(), "Unexpected scheme in $uriString.");
		static::assertSame($username, $uri->username, "Unexpected username in $uriString.");
		static::assertSame($password, $uri->password, "Unexpected password in $uriString.");
		static::assertSame($host, $uri->host, "Unexpected host in $uriString.");
		static::assertSame($port, $uri->port, "Unexpected port in $uriString.");
		static::assertSame($path, $uri->path, "Unexpected path in $uriString.");
		static::assertSame($query, (string) $uri->queryMap, "Unexpected query in $uriString.");
		static::assertSame($fragment, $uri->fragment, "Unexpected fragment in $uriString.");
		static::assertSame($toString, (string) $uri);

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
		static::assertSame($scheme, $array['scheme']?->getScheme());
		static::assertSame($username, $array['username']);
		static::assertSame($password, $array['password']);
		static::assertSame($host, $array['host']);
		static::assertSame($port, $array['port']);
		static::assertSame($authority, $array['authority']);
		static::assertSame($userInfo, $array['userInfo']);
		static::assertSame($path, $array['path']);
		static::assertSame($queryMap, $array['query']?->toArray());
		static::assertSame($fragment, $array['fragment']);
	}

	public function testWith(): void
	{
		$uri = Url::fromString('/');
		static::assertSame('/', (string) $uri);

		$uri = $uri->with()
			->scheme(UrlScheme::HTTPS)
			->host('example.com')
			->path('/test')
			->get()
		;
		static::assertSame('https://example.com/test', (string) $uri);

		$uri = $uri->with()
			->userInfo('user', 'password')
			->fragment('fragment')
			->get()
		;
		static::assertSame('https://user:password@example.com/test#fragment', (string) $uri);

		$query = new QueryMap();
		$query['test'] = 'true';

		$uri = $uri->with()
			->queryMap($query)
			->port(8080)
			->get()
		;
		static::assertSame('https://user:password@example.com:8080/test?test=true#fragment', (string) $uri);
	}
}
