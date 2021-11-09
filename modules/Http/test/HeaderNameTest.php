<?php
declare(strict_types=1);

namespace Philly\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Http\HeaderName
 */
class HeaderNameTest extends TestCase
{
	public function dataProvider(): array
	{
		return [
			[ HeaderName::Expect, false, true, false ],
			[ HeaderName::Host, false, true, false ],
			[ HeaderName::MaxForwards, false, true, false ],
			[ HeaderName::Pragma, false, true, false ],
			[ HeaderName::Range, false, true, false ],
			[ HeaderName::IfMatch, false, true, false ],
			[ HeaderName::IfNoneMatch, false, true, false ],
			[ HeaderName::IfModifiedSince, false, true, false ],
			[ HeaderName::IfUnmodifiedSince, false, true, false ],
			[ HeaderName::IfRange, false, true, false ],
			[ HeaderName::Accept, false, true, false ],
			[ HeaderName::AcceptCharset, false, true, false ],
			[ HeaderName::AcceptEncoding, false, true, false ],
			[ HeaderName::AcceptLanguage, false, true, false ],
			[ HeaderName::Authorization, false, true, false ],
			[ HeaderName::ProxyAuthorization, false, true, false ],
			[ HeaderName::From, false, true, false ],
			[ HeaderName::Referer, false, true, false ],
			[ HeaderName::Cookie, false, true, false ],
			[ HeaderName::UserAgent, false, true, false ],
			[ HeaderName::Age, false, false, true ],
			[ HeaderName::Expires, false, false, true ],
			[ HeaderName::Date, false, false, true ],
			[ HeaderName::Location, false, false, true ],
			[ HeaderName::RetryAfter, false, false, true ],
			[ HeaderName::Vary, false, false, true ],
			[ HeaderName::Warning, false, false, true ],
			[ HeaderName::ETag, false, false, true ],
			[ HeaderName::LastModified, false, false, true ],
			[ HeaderName::WwwAuthenticate, false, false, true ],
			[ HeaderName::ProxyAuthenticate, false, false, true ],
			[ HeaderName::AcceptRanges, false, false, true ],
			[ HeaderName::Allow, false, false, true ],
			[ HeaderName::SetCookie, true, false, true ],
			[ HeaderName::Server, false, false, true ],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testProperties(HeaderName $name, bool $canBeDuplicate, bool $isOnlyRequest, bool $isOnlyResponse): void
	{
		self::assertEquals($canBeDuplicate, $name->canBeDuplicate(), $name->name . ' header should ' . ($canBeDuplicate ? 'not ' : '') . 'be able to be duplicate');
		self::assertEquals($isOnlyRequest, $name->isOnlyRequest(), $name->name . ' header should ' . ($isOnlyRequest ? 'not ' : '') . 'be only request');
		self::assertEquals($isOnlyResponse, $name->isOnlyResponse(), $name->name . ' header should ' . ($isOnlyResponse ? 'not ' : '') . 'be only response');
	}
}
