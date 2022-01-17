<?php
declare(strict_types=1);

namespace Elephox\Http;

use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Cookie
 * @covers \Elephox\Http\CookieSameSite
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
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

	public function getterSetterProvider(): iterable
	{
		$timestamp = new DateTime();

		yield ['setName', 'getName', 'test', 'test='];
		yield ['setValue', 'getValue', 'test', 'name1=test'];
		yield ['setValue', 'getValue', null, 'name1='];
		yield ['setExpires', 'getExpires', $timestamp, 'name1=; Expires=' . $timestamp->format(Cookie::ExpiresFormat)];
		yield ['setExpires', 'getExpires', null, 'name1='];
		yield ['setPath', 'getPath', '/test', 'name1=; Path=/test'];
		yield ['setPath', 'getPath', null, 'name1='];
		yield ['setDomain', 'getDomain', 'localhost', 'name1=; Domain=localhost'];
		yield ['setDomain', 'getDomain', null, 'name1='];
		yield ['setSecure', 'isSecure', true, 'name1=; Secure'];
		yield ['setSecure', 'isSecure', false, 'name1='];
		yield ['setHttpOnly', 'isHttpOnly', true, 'name1=; HttpOnly'];
		yield ['setHttpOnly', 'isHttpOnly', false, 'name1='];
		yield ['setSameSite', 'getSameSite', CookieSameSite::None, 'name1=; SameSite=None'];
		yield ['setSameSite', 'getSameSite', CookieSameSite::Lax, 'name1=; SameSite=Lax'];
		yield ['setSameSite', 'getSameSite', CookieSameSite::Strict, 'name1=; SameSite=Strict'];
		yield ['setSameSite', 'getSameSite', null, 'name1='];
		yield ['setMaxAge', 'getMaxAge', 1234, 'name1=; Max-Age=1234'];
		yield ['setMaxAge', 'getMaxAge', null, 'name1='];
	}

	/**
	 * @dataProvider getterSetterProvider
	 */
	public function testGettersAndSetters(string $setter, string $getter, mixed $value, string $cookieString): void
	{
		$cookie = new Cookie('name1');

		$cookie->{$setter}($value);

		self::assertSame($value, $cookie->{$getter}());
		self::assertEquals($cookieString, $cookie->__toString());
	}

	public function arrayKeyProvider(): iterable
	{
		yield ['name', 'name1'];
		yield ['value', 'value1'];
		yield ['expires', new DateTime('+1 day')];
		yield ['path', '/'];
		yield ['domain', 'example.com'];
		yield ['secure', true];
		yield ['httpOnly', true];
		yield ['sameSite', CookieSameSite::None];
		yield ['maxAge', 1234];
	}

	/**
	 * @dataProvider arrayKeyProvider
	 */
	public function testArrayKey(string $key, mixed $value): void
	{
		$cookie = new Cookie('name1');

		$cookie[$key] = $value;

		self::assertSame($value, $cookie[$key]);
	}

	public function testOffsetExists(): void
	{
		$cookie = new Cookie('name1');

		self::assertTrue(isset($cookie['name']));
		self::assertTrue(isset($cookie['value']));
		self::assertTrue(isset($cookie['secure']));
		self::assertTrue(isset($cookie['httpOnly']));
		self::assertFalse(isset($cookie['expires']));
		self::assertFalse(isset($cookie['path']));
		self::assertFalse(isset($cookie['domain']));
		self::assertFalse(isset($cookie['sameSite']));
		self::assertFalse(isset($cookie['maxAge']));
		self::assertFalse(isset($cookie['test']));

		$cookie['expires'] = new DateTime('+1 day');
		self::assertTrue(isset($cookie['expires']));

		$cookie['path'] = '/';
		self::assertTrue(isset($cookie['path']));

		$cookie['domain'] = 'example.com';
		self::assertTrue(isset($cookie['domain']));

		$cookie['sameSite'] = CookieSameSite::None;
		self::assertTrue(isset($cookie['sameSite']));

		$cookie['maxAge'] = 1234;
		self::assertTrue(isset($cookie['maxAge']));
	}

	public function testInvalidOffsetGet(): void
	{
		$cookie = new Cookie('name1');

		self::assertFalse(isset($cookie['test']));

		$this->expectException(InvalidArgumentException::class);
		$cookie['test'];
	}

	public function testInvalidOffsetSetType(): void
	{
		$cookie = new Cookie('name1');

		$this->expectException(InvalidArgumentException::class);
		$cookie[0] = true;
	}

	public function testInvalidOffsetSetName(): void
	{
		$cookie = new Cookie('name1');

		$this->expectException(InvalidArgumentException::class);
		$cookie['test'] = true;
	}

	public function testOffsetUnset(): void
	{
		$cookie = new Cookie('name1', 'value1', new DateTime('+1 day'), '/', 'example.com', true, true, CookieSameSite::None, 1234);

		self::assertTrue(isset($cookie['name']));
		self::assertTrue(isset($cookie['value']));
		self::assertTrue(isset($cookie['secure']));
		self::assertTrue(isset($cookie['httpOnly']));
		self::assertTrue(isset($cookie['expires']));
		self::assertTrue(isset($cookie['path']));
		self::assertTrue(isset($cookie['domain']));
		self::assertTrue(isset($cookie['sameSite']));
		self::assertTrue(isset($cookie['maxAge']));

		unset($cookie['value']);
		unset($cookie['secure']);
		unset($cookie['httpOnly']);
		unset($cookie['expires']);
		unset($cookie['path']);
		unset($cookie['domain']);
		unset($cookie['sameSite']);
		unset($cookie['maxAge']);

		self::assertFalse(isset($cookie['expires']));
		self::assertFalse(isset($cookie['path']));
		self::assertFalse(isset($cookie['domain']));
		self::assertFalse(isset($cookie['sameSite']));
		self::assertFalse(isset($cookie['maxAge']));
	}

	public function testOffsetUnsetInvalidType(): void
	{
		$cookie = new Cookie('name1');

		$this->expectException(InvalidArgumentException::class);
		unset($cookie[0]);
	}

	public function testOffsetUnsetInvalidName(): void
	{
		$cookie = new Cookie('name1');

		$this->expectException(InvalidArgumentException::class);
		unset($cookie['test']);
	}

	public function testToArray(): void
	{
		$timestamp = new DateTime('+1 day');
		$cookie = new Cookie('name1', 'value1', $timestamp, '/', 'example.com', true, true, CookieSameSite::None, 1234);

		self::assertSame(
			[
				'name' => 'name1',
				'value' => 'value1',
				'expires' => $timestamp,
				'path' => '/',
				'domain' => 'example.com',
				'secure' => true,
				'httpOnly' => true,
				'sameSite' => CookieSameSite::None,
				'maxAge' => 1234,
			],
			$cookie->toArray()
		);
	}
}
