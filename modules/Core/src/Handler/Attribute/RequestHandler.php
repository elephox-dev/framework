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
use Elephox\Core\Handler\UrlTemplate;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Http\Contract\Response;
use Elephox\Http\CustomRequestMethod;
use Elephox\Http\RequestMethod;
use Exception;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestHandler extends AbstractHandler
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
	)
	{
		parent::__construct(ActionType::Request);

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

		if (!$this->methods->isEmpty() && !$this->methods->contains($context->getRequest()->getMethod())) {
			return false;
		}

		return $this->template->matches($context->getRequest()->getUrl());
	}

	/**
	 * @throws \Exception
	 */
	public function invoke(object $handler, string $method, ContextContract $context): void
	{
		if (!$context instanceof RequestContextContract) {
			throw new Exception('Invalid context type');
		}

		// TODO: extract url parameters and pass them inside the arguments

		/** @var Response|mixed $result */
		$result = $context->getContainer()->call($handler, $method, ['context' => $context]);

		if (!$result instanceof Response) {
			throw new Exception('Request handler didn\'t return a Response.');
		}

		$result->send();
	}
}
