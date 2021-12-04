<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Attribute;
use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Core\Context\Contract\Context as ContextContract;
use Elephox\Core\Context\Contract\RequestContext as RequestContextContract;
use Elephox\Core\Handler\ActionType;
use Elephox\Core\Handler\InvalidContextException;
use Elephox\Core\Handler\InvalidResultException;
use Elephox\Core\Handler\UrlTemplate;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Http\Contract\Response;
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
				/**
				 * @var RequestMethod|null $method
				 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
				 */
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

		if (!$this->methods->isEmpty() && !$this->methods->contains($context->getRequest()->getRequestMethod())) {
			return false;
		}

		return $this->template->matches($context->getRequest()->getUri());
	}

	public function invoke(Closure $callback, ContextContract $context): void
	{
		if (!$context instanceof RequestContextContract) {
			throw new InvalidContextException($context, RequestContextContract::class);
		}

		$parameters = $this->template->getValues($context->getRequest()->getUri());

		/** @var Response|mixed $result */
		$result = $context->getContainer()->callback($callback, ['context' => $context, ...$parameters]);

		if (!$result instanceof Response) {
			throw new InvalidResultException($result, Response::class);
		}

		$result->send();
	}
}
