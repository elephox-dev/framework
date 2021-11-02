<?php

namespace Philly\Http;

enum HeaderName: string
{
	/* Authentication */
	case WwwAuthenticate = "www-authenticate";
	case Authorization = "authorization";
	case ProxyAuthenticate = "proxy-authenticate";
	case ProxyAuthorization = "proxy-authorization";

	/* Caching */
	case Age = "age";
	case CacheControl = "cache-control";
	case ClearSizeData = "clear-site-data";
	case Expires = "expires";
	case Pragma = "pragma";
	case Warning = "warning";

	/* Conditionals */
	case LastModified = "last-modified";
	case ETag = "etag";
	case IfMatch = "if-match";
	case IfNoneMatch = "if-none-match";
	case IfModifiedSince = "if-modified-since";
	case IfUnmodifiedSince = "if-unmodified-since";
	case Vary = "vary";

	/* Connection management */
	case Connection = "connection";
	case KeepAlive = "keep-alive";

	/* Content negotiation */
	case Accept = "accept";
	case AcceptEncoding = "accept-encoding";
	case AcceptLanguage = "accept-language";

	/* Controls */
	case Expect = "expect";

	/* Cookies */
	case Cookie = "cookie";
	case SetCookie = "set-cookie";

	/* CORS */
	case AccessControlAllowOrigin = "access-control-allow-origin";
	case AccessControlAllowCredentials = "access-control-allow-credentials";
	case AccessControlAllowHeaders = "access-control-allow-headers";
	case AccessControlAllowMethod = "access-control-allow-methods";
	case AccessControlExposeHeaders = "access-control-expose-headers";
	case AccessControlMaxAge = "access-control-max-age";
	case AccessControlRequestHeaders = "access-control-request-headers";
	case AccessControlRequestMethod = "access-control-request-method";
	case Origin = "origin";
	case TimingAllowOrigin = "timing-allow-origin";

	/* Downloads */
	case ContentDisposition = "content-disposition";

	/* Message body information */
	case ContentLength = "content-length";
	case ContentType = "content-type";
	case ContentEncoding = "content-encoding";
	case ContentLanguage = "content-language";
	case ContentLocation = "content-location";

	/* Proxies */
	case Forwarded = "forwarded";
	case Via = "via";

	/* Redirects */
	case Location = "location";

	/* Request context */
	case From = "from";
	case Host = "host";
	case Referer = "referer";
	case ReferrerPolicy = "referrer-policy";
	case UserAgent = "user-agent";

	/* Response context */
	case Allow = "allow";
	case Server = "server";

	/* Range requests */
	case AcceptRanges = "accept-ranges";
	case Range = "range";
	case IfRange = "if-range";
	case ContentRange = "content-range";

	/* Security */
	case CrossOriginEmbedderPolicy = "cross-origin-embedder-policy";
	case CrossOriginOpenerPolicy = "cross-origin-opener-policy";
	case CrossOriginResourcePolicy = "cross-origin-resource-policy";
	case CrossOriginSecurityPolicy = "cross-origin-security-policy";
	case CrossOriginSecurityPolicyReportOnly = "cross-origin-security-policy-report-only";
	case ExpectCT = "expect-ct";
	case FeaturePolicy = "feature-policy";
	case StrictTransportSecurity = "strict-transport-security";
	case UpgradeInsecureRequests = "upgrade-insecure-requests";

	/* Other */
	case Date = "date";
	case RetryAfter = "retry-after";
	case Upgrade = "upgrade";
}
