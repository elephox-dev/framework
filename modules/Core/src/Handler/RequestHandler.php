<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Attribute;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\ReadonlyList;
use Elephox\Http\Contract;
use Elephox\Http\CustomRequestMethod;
use Elephox\Http\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestHandler extends HandlerAttribute
{
	private UrlTemplate $template;

	/**
	 * @var GenericList<Contract\RequestMethod>
	 */
	private GenericList $methods;

	/**
	 * @param string|UrlTemplate $url
	 * @param string|Contract\RequestMethod|array<string>|array<Contract\RequestMethod>|GenericList<string|Contract\RequestMethod> $methods
	 */
	public function __construct(
		string|UrlTemplate                              $url,
		string|Contract\RequestMethod|array|GenericList $methods = RequestMethod::GET,
	)
	{
		parent::__construct(ActionType::Request);

		$this->template = $url instanceof UrlTemplate ? $url : new UrlTemplate($url);

		if (is_string($methods) || $methods instanceof Contract\RequestMethod) {
			$methods = [$methods];
		}

		$this->methods = new ArrayList();
		foreach ($methods as $method_name) {
			/** @var Contract\RequestMethod $method */
			if (!($method_name instanceof Contract\RequestMethod)) {
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
	 * @return ReadonlyList<Contract\RequestMethod>
	 */
	public function getMethods(): ReadonlyList
	{
		return $this->methods;
	}
}
