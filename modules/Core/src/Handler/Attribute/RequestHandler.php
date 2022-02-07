<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Core\ActionType;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Context\Contract\RequestContext as RequestContextContract;
use Elephox\Core\Handler\Contract\HandledRequest;
use Elephox\Core\Handler\Contract\UrlTemplate as UrlTemplateContract;
use Elephox\Core\Handler\HandledRequestBuilder;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\MatchedUrlTemplate;
use Elephox\Core\Handler\UrlTemplate;
use Elephox\DI\InstanceLifetime;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ServerRequest;
use Elephox\Http\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequestHandler extends AbstractHandlerAttribute
{
	private UrlTemplateContract $template;

	/**
	 * @var GenericList<RequestMethod>
	 */
	private GenericList $methods;

	/**
	 * @param string|UrlTemplateContract $url
	 * @param null|non-empty-string|RequestMethod|array<non-empty-string|RequestMethod>|GenericList<non-empty-string|RequestMethod> $methods
	 * @param int $weight
	 */
	public function __construct(
		string|UrlTemplateContract                  $url = '',
		null|string|RequestMethod|array|GenericList $methods = null,
		int                                         $weight = 0,
	) {
		parent::__construct(ActionType::Request, $weight);

		$this->template = $url instanceof UrlTemplateContract ? $url : new UrlTemplate($url);

		if ($methods === null) {
			$methods = [];
		} else if (is_string($methods) || $methods instanceof RequestMethod) {
			$methods = [$methods];
		}

		/**
		 * @var ArrayList<RequestMethod>
		 */
		$this->methods = new ArrayList();

		/**
		 * @var non-empty-string|RequestMethod $method_name
		 */
		foreach ($methods as $method_name) {
			/** @var RequestMethod $method */
			if (!$method_name instanceof RequestMethod) {
				$method = RequestMethod::from($method_name);
			} else {
				$method = $method_name;
			}

			$this->methods->add($method);
		}
	}

	public function handles(Context $context): bool
	{
		if (!$context instanceof RequestContextContract) {
			return false;
		}

		$request = $context->getRequest();
		$requestMethod = $request->getMethod();

		if (!$this->methods->isEmpty() && !$this->methods->contains($requestMethod)) {
			return false;
		}

		return $this->template->matches($context->getRequest()->getUrl());
	}

	public function getHandlerParams(Context $context): iterable
	{
		if (!$context instanceof RequestContextContract) {
			throw new InvalidContextException($context, RequestContextContract::class);
		}

		$handledRequest = HandledRequestBuilder::fromRequest($context->getRequest())
			->matchedTemplate(new MatchedUrlTemplate($context->getRequest()->getUrl(), $this->template))
			->get();

		$context->getContainer()->register($handledRequest::class, $handledRequest, InstanceLifetime::Singleton, 'request', Request::class, ServerRequest::class, HandledRequest::class);

		yield from $handledRequest->getMatchedTemplate()->whereKey(fn($x) => is_string($x));
		yield 'template' => $this->template;
		yield 'templateValues' => $handledRequest->getMatchedTemplate()->toArray();
	}
}
