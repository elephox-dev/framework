<?php

namespace Philly\Http;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Http\Cookie
 * @covers \Philly\Http\CookieSameSite
 * @covers \Philly\Collection\ArrayList
 * @covers \Philly\Collection\ArrayMap
 * @covers \Philly\Collection\KeyValuePair
 */
class CookieTest extends TestCase
{
	public function testFullToString(): void
	{
		$timestamp = new DateTime('+1 day');
		$cookie = new Cookie(
			'name',
			'value',
			$timestamp,
			'/',
			'example.com',
			true,
			true,
			CookieSameSite::None,
			1234,
		);

		self::assertEquals(
			'name=value; Expires=' . $timestamp->format(Cookie::ExpiresFormat) . '; Path=/; Domain=example.com; Secure; HttpOnly; SameSite=None; Max-Age=1234',
			(string)$cookie
		);
	}

	public function testFromResponseString(): void
	{
		$timestamp = (new DateTime('+1 day'))->format(Cookie::ExpiresFormat);
		$cookie = Cookie::fromResponseString('XSRF_Token=asfdkhjaeo83r; Expires=' . $timestamp . '; Path=/; Domain=example.com; Secure; HttpOnly; SameSite=None; Max-Age=1234');

		self::assertEquals('XSRF_Token', $cookie->getName());
		self::assertEquals('asfdkhjaeo83r', $cookie->getValue());
		self::assertEquals(new DateTime($timestamp), $cookie->getExpires());
		self::assertEquals('/', $cookie->getPath());
		self::assertEquals('example.com', $cookie->getDomain());
		self::assertTrue($cookie->isSecure());
		self::assertTrue($cookie->isHttpOnly());
		self::assertEquals(CookieSameSite::None, $cookie->getSameSite());
		self::assertEquals(1234, $cookie->getMaxAge());
	}

	public function testFromRequestString(): void
	{
		$cookies = Cookie::fromRequestString("asdsdf=serser; rsg324=234213; 2sefs3f=");

		self::assertEquals(3, $cookies->count());
		self::assertEquals('asdsdf', $cookies->get(0)->getName());
		self::assertEquals('serser', $cookies->get(0)->getValue());
	}

	public function dataProvider(): array
	{
		$timestamp = new DateTime();

		return [
			[ 'setName', 'getName', 'test', 'test=' ],
			[ 'setValue', 'getValue', 'test', 'name1=test' ],
			[ 'setValue', 'getValue', null, 'name1=' ],
			[ 'setExpires', 'getExpires', $timestamp, 'name1=; Expires=' . $timestamp->format(Cookie::ExpiresFormat) ],
			[ 'setExpires', 'getExpires', null, 'name1=' ],
			[ 'setPath', 'getPath', '/test', 'name1=; Path=/test' ],
			[ 'setPath', 'getPath', null, 'name1=' ],
			[ 'setDomain', 'getDomain', 'localhost', 'name1=; Domain=localhost' ],
			[ 'setDomain', 'getDomain', null, 'name1=' ],
			[ 'setSecure', 'isSecure', true, 'name1=; Secure' ],
			[ 'setSecure', 'isSecure', false, 'name1=' ],
			[ 'setHttpOnly', 'isHttpOnly', true, 'name1=; HttpOnly' ],
			[ 'setHttpOnly', 'isHttpOnly', false, 'name1=' ],
			[ 'setSameSite', 'getSameSite', CookieSameSite::None, 'name1=; SameSite=None' ],
			[ 'setSameSite', 'getSameSite', CookieSameSite::Lax, 'name1=; SameSite=Lax' ],
			[ 'setSameSite', 'getSameSite', CookieSameSite::Strict, 'name1=; SameSite=Strict' ],
			[ 'setSameSite', 'getSameSite', null, 'name1=' ],
            [ 'setMaxAge', 'getMaxAge', 1234, 'name1=; Max-Age=1234' ],
            [ 'setMaxAge', 'getMaxAge', null, 'name1=' ],
		];
	}

	/** @dataProvider dataProvider */
	public function testGettersAndSetters(string $setter, string $getter, mixed $value, string $cookieString): void
	{
		$cookie = new Cookie('name1');

		$cookie->{$setter}($value);

		self::assertSame($value, $cookie->{$getter}());
		self::assertEquals($cookieString, $cookie->asString());
	}
}
