<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Core\Context\Contract\Context as ContextContract;
use Elephox\Core\Context\Contract\RequestContext as RequestContextContract;
use Elephox\Core\Handler\ActionType;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\UrlTemplate;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Http\CustomRequestMethod;
use Elephox\Http\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequestHandler extends AbstractHandlerAttribute
{
	private UrlTemplate $template;

	/**
	 * @var GenericList<RequestMethodContract>
	 */
	private GenericList $methods;

	/**
	 * @param string|UrlTemplate $url
	 * @param null|non-empty-string|RequestMethodContract|array<non-empty-string|RequestMethodContract>|GenericList<non-empty-string|RequestMethodContract> $methods
	 * @param int $weight
	 */
	public function __construct(
		string|UrlTemplate                                  $url,
		null|string|RequestMethodContract|array|GenericList $methods = null,
		int                                                 $weight = 0,
	)
	{
		parent::__construct(ActionType::Request, $weight);

		$this->template = $url instanceof UrlTemplate ? $url : new UrlTemplate($url);

		if ($methods === null) {
			$methods = [];
		} else if (is_string($methods) || $methods instanceof RequestMethodContract) {
			$methods = [$methods];
		}

		$this->methods = new ArrayList();
		/**
		 * @var non-empty-string|RequestMethodContract $method_name
		 */
		foreach ($methods as $method_name) {
			/** @var RequestMethodContract $method */
			if (!$method_name instanceof RequestMethodContract) {
				$method = RequestMethod::tryFrom($method_name);

				if ($method === null) {
					$method = new CustomRequestMethod($method_name);
				}
			} else {
				$method = $method_name;
			}

			$this->methods->add($method);
		}
	}

	public function getTemplate(): UrlTemplate
	{
		return $this->template;
	}

	/**
	 * @return ReadonlyList<RequestMethodContract>
	 */
	public function getMethods(): ReadonlyList
	{
		return $this->methods;
	}

	public function handles(ContextContract $context): bool
	{
		if (!$context instanceof RequestContextContract) {
			return false;
		}

		$request = $context->getRequest();
		$method = $request->getMethod();
		if ($request instanceof RequestContract) {
			$requestMethod = $request->getRequestMethod();
		} else if (!empty($method)) {
			$requestMethod = RequestMethod::tryFrom($method);
			$requestMethod ??= new CustomRequestMethod($method);
		} else {
			return false;
		}

		if (!$this->methods->isEmpty() && !$this->methods->contains($requestMethod)) {
			return false;
		}

		return $this->template->matches($context->getRequest()->getUri());
	}

	public function getHandlerParams(ContextContract $context): array
	{
		if (!$context instanceof RequestContextContract) {
			throw new InvalidContextException($context, RequestContextContract::class);
		}

		return $this->template->getValues($context->getRequest()->getUri());
	}
}
