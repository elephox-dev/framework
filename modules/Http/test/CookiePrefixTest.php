<?php

namespace Philly\Http;

use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Philly\Http\Contract\Cookie;

/**
 * @covers \Philly\Http\CookiePrefix
 */
class CookiePrefixTest extends MockeryTestCase
{
	public function testIsCompliantHost(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->twice()
			->andReturn("__Host-test")
		;
		$cookieMock
			->expects("isSecure")
			->withNoArgs()
			->once()
			->andReturn(true)
		;
		$cookieMock
			->expects("getDomain")
			->withNoArgs()
			->once()
			->andReturn(null)
		;
		$cookieMock
			->expects("getPath")
			->withNoArgs()
			->once()
			->andReturn("/")
		;

		self::assertTrue(CookiePrefix::Host->isCompliant($cookieMock));
		self::assertFalse(CookiePrefix::Secure->isCompliant($cookieMock));
	}

	public function testIsNotCompliantHostName(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->once()
			->andReturn("test")
		;

		self::assertFalse(CookiePrefix::Host->isCompliant($cookieMock));
	}

	public function testIsNotCompliantHostSecure(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->once()
			->andReturn("__Host-test")
		;
		$cookieMock
			->expects("isSecure")
			->withNoArgs()
			->once()
			->andReturn(false)
		;

		self::assertFalse(CookiePrefix::Host->isCompliant($cookieMock));
	}

	public function testIsNotCompliantHostDomain(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->once()
			->andReturn("__Host-test")
		;
		$cookieMock
			->expects("isSecure")
			->withNoArgs()
			->once()
			->andReturn(true)
		;
		$cookieMock
			->expects("getDomain")
			->withNoArgs()
			->once()
			->andReturn("localhost")
		;

		self::assertFalse(CookiePrefix::Host->isCompliant($cookieMock));
	}

	public function testIsNotCompliantHostPath(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->once()
			->andReturn("__Host-test")
		;
		$cookieMock
			->expects("isSecure")
			->withNoArgs()
			->once()
			->andReturn(true)
		;
		$cookieMock
			->expects("getDomain")
			->withNoArgs()
			->once()
			->andReturn(null)
		;
		$cookieMock
			->expects("getPath")
			->withNoArgs()
			->once()
			->andReturn("/test")
		;

		self::assertFalse(CookiePrefix::Host->isCompliant($cookieMock));
	}

	public function testIsCompliantSecure(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->twice()
			->andReturn("__Secure-test")
		;
		$cookieMock
			->expects("isSecure")
			->withNoArgs()
			->once()
			->andReturn(true)
		;

		self::assertTrue(CookiePrefix::Secure->isCompliant($cookieMock));
		self::assertFalse(CookiePrefix::Host->isCompliant($cookieMock));
	}

	public function testIsNotCompliantSecureName(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->once()
			->andReturn("test")
		;

		self::assertFalse(CookiePrefix::Secure->isCompliant($cookieMock));
	}

	public function testIsNotCompliantSecureSecure(): void
	{
		$cookieMock = M::mock(Cookie::class);

		$cookieMock
			->expects("getName")
			->withNoArgs()
			->once()
			->andReturn("__Secure-test")
		;
		$cookieMock
			->expects("isSecure")
			->withNoArgs()
			->once()
			->andReturn(false)
		;

		self::assertFalse(CookiePrefix::Secure->isCompliant($cookieMock));
	}
}
