<?php
declare(strict_types=1);

namespace Philly\Http;

use JetBrains\PhpStorm\Pure;

enum HeaderName: string implements Contract\HeaderName
{
	/* Authentication */
	case WwwAuthenticate = "WWW-Authenticate";
	case Authorization = "Authorization";
	case ProxyAuthenticate = "Proxy-Authenticate";
	case ProxyAuthorization = "Proxy-Authorization";

	/* Caching */
	case Age = "Age";
	case CacheControl = "Cache-Control";
	case ClearSizeData = "Clear-Site-Data";
	case Expires = "Expires";
	case Pragma = "Pragma";
	case Warning = "Warning";

	/* Conditionals */
	case LastModified = "Last-Modified";
	case ETag = "Etag";
	case IfMatch = "If-Match";
	case IfNoneMatch = "If-None-Match";
	case IfModifiedSince = "If-Modified-Since";
	case IfUnmodifiedSince = "If-Unmodified-Since";
	case Vary = "Vary";

	/* Connection management */
	case Connection = "Connection";
	case KeepAlive = "Keep-Alive";

	/* Content negotiation */
	case Accept = "Accept";
	case AcceptCharset = "Accept-Charset";
	case AcceptEncoding = "Accept-Encoding";
	case AcceptLanguage = "Accept-Language";

	/* Controls */
	case Expect = "Expect";

	/* Cookies */
	case Cookie = "Cookie";
	case SetCookie = "Set-Cookie";

	/* CORS */
	case AccessControlAllowOrigin = "Access-Control-Allow-Origin";
	case AccessControlAllowCredentials = "Access-Control-Allow-Credentials";
	case AccessControlAllowHeaders = "Access-Control-Allow-Headers";
	case AccessControlAllowMethod = "Access-Control-Allow-Methods";
	case AccessControlExposeHeaders = "Access-Control-Expose-Headers";
	case AccessControlMaxAge = "Access-Control-Max-Age";
	case AccessControlRequestHeaders = "Access-Control-Request-Headers";
	case AccessControlRequestMethod = "Access-Control-Request-Method";
	case Origin = "Origin";
	case TimingAllowOrigin = "Timing-Allow-Origin";

	/* Downloads */
	case ContentDisposition = "Content-Disposition";

	/* Message body information */
	case ContentLength = "Content-Length";
	case ContentType = "Content-Type";
	case ContentEncoding = "Content-Encoding";
	case ContentLanguage = "Content-Language";
	case ContentLocation = "Content-Location";

	/* Proxies */
	case Forwarded = "Forwarded";
	case Via = "Via";

	/* Redirects */
	case Location = "Location";

	/* Request context */
	case From = "From";
	case Host = "Host";
	case Referer = "Referer";
	case ReferrerPolicy = "Referrer-Policy";
	case UserAgent = "User-Agent";
	case MaxForwards = "Max-Forwards";

	/* Response context */
	case Allow = "Allow";
	case Server = "Server";

	/* Range requests */
	case AcceptRanges = "Accept-Ranges";
	case Range = "Range";
	case IfRange = "If-Range";
	case ContentRange = "Content-Range";

	/* Security */
	case CrossOriginEmbedderPolicy = "Cross-Origin-Embedder-Policy";
	case CrossOriginOpenerPolicy = "Cross-Origin-Opener-Policy";
	case CrossOriginResourcePolicy = "Cross-Origin-Resource-Policy";
	case CrossOriginSecurityPolicy = "Cross-Origin-Security-Policy";
	case CrossOriginSecurityPolicyReportOnly = "Cross-Origin-Security-Policy-Report-Only";
	case ExpectCT = "Expect-Ct";
	case FeaturePolicy = "Feature-Policy";
	case StrictTransportSecurity = "Strict-Transport-Security";
	case UpgradeInsecureRequests = "Upgrade-Insecure-Requests";

	/* Other */
	case Date = "Date";
	case RetryAfter = "Retry-After";
	case Upgrade = "Upgrade";

	#[Pure] public function canBeDuplicate(): bool
	{
		return match ($this) {
			self::SetCookie => true,
			default => false,
		};
	}

	#[Pure] public function isOnlyRequest(): bool
	{
		return match ($this) {
			self::Expect,
			self::Host,
			self::MaxForwards,
			self::Pragma,
			self::Range,
			self::IfMatch,
			self::IfNoneMatch,
			self::IfModifiedSince,
			self::IfUnmodifiedSince,
			self::IfRange,
			self::Accept,
			self::AcceptCharset,
			self::AcceptEncoding,
			self::AcceptLanguage,
			self::Authorization,
			self::ProxyAuthorization,
			self::From,
			self::Referer,
			self::Cookie,
			self::UserAgent => true,
			default => false,
		};
	}

	#[Pure] public function isOnlyResponse(): bool
	{
		return match ($this) {
			self::Age,
			self::Expires,
			self::Date,
			self::Location,
			self::RetryAfter,
			self::Vary,
			self::Warning,
			self::ETag,
			self::LastModified,
			self::WwwAuthenticate,
			self::ProxyAuthenticate,
			self::AcceptRanges,
			self::Allow,
			self::SetCookie,
			self::Server => true,
			default => false,
		};
	}

	public function getValue(): string
	{
		/** @psalm-suppress UndefinedPropertyFetch Until vimeo/psalm#6468 is fixed */
		return $this->value;
	}
}
