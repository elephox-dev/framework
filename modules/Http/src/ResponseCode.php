<?php
declare(strict_types=1);

namespace Elephox\Http;

enum ResponseCode: int implements Contract\ResponseCode
{
	/* Information responses */
	case Continue_ = 100;
	case SwitchingProtocols = 101;
	case Processing = 102;
	case EarlyHints = 103;

	/* Successful responses  */
	case OK = 200;
	case Created = 201;
	case Accepted = 202;
	case NonAuthoritativeInformation = 203;
	case NoContent = 204;
	case ResetContent = 205;
	case PartialContent = 206;
	case MultiStatus = 207;
	case AlreadyReported = 208;
	case IMUsed = 226;

	/* Redirection messages */
	case MultipleChoice = 300;
	case MovedPermanently = 301;
	case Found = 302;
	case SeeOther = 303;
	case NotModified = 304;
	case TemporaryRedirect = 307;
	case PermanentRedirect = 308;

	/* Client error responses */
	case BadRequest = 400;
	case Unauthorized = 401;
	case PaymentRequired = 402;
	case Forbidden = 403;
	case NotFound = 404;
	case MethodNotAllowed = 405;
	case NotAcceptable = 406;
	case ProxyAuthenticationRequired = 407;
	case RequestTimeout = 408;
	case Conflict = 409;
	case Gone = 410;
	case LengthRequired = 411;
	case PreconditionFailed = 412;
	case PayloadTooLarge = 413;
	case RequestURITooLong = 414;
	case UnsupportedMediaType = 415;
	case RequestedRangeNotSatisfiable = 416;
	case ExpectationFailed = 417;
	case ImATeapot = 418;
	case MisdirectedRequest = 421;
	case UnprocessableEntity = 422;
	case Locked = 423;
	case FailedDependency = 424;
	case TooEarly = 425;
	case UpgradeRequired = 426;
	case PreconditionRequired = 428;
	case TooManyRequests = 429;
	case RequestHeaderFieldsTooLarge = 431;
	case UnavailableForLegalReasons = 451;

	/* Server error responses */
	case InternalServerError = 500;
	case NotImplemented = 501;
	case BadGateway = 502;
	case ServiceUnavailable = 503;
	case GatewayTimeout = 504;
	case HTTPVersionNotSupported = 505;
	case VariantAlsoNegotiates = 506;
	case InsufficientStorage = 507;
	case LoopDetected = 508;
	case NotExtended = 510;
	case NetworkAuthenticationRequired = 511;

	public function getCode(): int
	{
		/**
		 * @var int value
		 * @psalm-suppress UndefinedThisPropertyFetch Until vimeo/psalm#6468 is fixed
		 */
		return $this->value;
	}

	public function getMessage(): string
	{
		/**
		 * @var non-empty-string name
		 * @psalm-suppress UndefinedThisPropertyFetch Until vimeo/psalm#6468 is fixed
		 */
		return $this->name;
	}
}
