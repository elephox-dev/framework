<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Http\Contract\RequestMethod as RequestMethodContract;
use Elephox\Http\CustomRequestMethod;
use Elephox\Http\RequestMethod;
use Elephox\Web\Routing\Attribute\Contract\ActionAttribute;
use InvalidArgumentException;
use JetBrains\PhpStorm\ExpectedValues;
use LogicException;

abstract class AbstractActionAttribute extends AbstractRoutingAttribute implements ActionAttribute
{
	/**
	 * @var ArrayList<RequestMethodContract> $methods
	 */
	private ArrayList $methods;

	/**
	 * @param null|string $path
	 * @param non-empty-string|RequestMethodContract|iterable<mixed, non-empty-string|RequestMethodContract> $methods
	 */
	public function __construct(
		?string $path = self::DEFAULT_PATH,
		#[ExpectedValues([
			RequestMethod::GET,
			RequestMethod::POST,
			RequestMethod::PUT,
			RequestMethod::DELETE,
			RequestMethod::PATCH,
			RequestMethod::HEAD,
			RequestMethod::OPTIONS,
			'GET',
			'POST',
			'PUT',
			'DELETE',
			'PATCH',
			'HEAD',
			'OPTIONS',
		])]
		string|RequestMethodContract|iterable $methods = [],
	) {
		parent::__construct($path);

		/** @var ArrayList<RequestMethodContract> */
		$this->methods = new ArrayList();

		if (is_string($methods) || $methods instanceof RequestMethodContract) {
			$methods = [$methods];
		}

		foreach ($methods as $method) {
			if (is_string($method)) {
				$requestMethod = RequestMethod::tryFrom($method);
				$requestMethod ??= new CustomRequestMethod($method);
			} elseif ($method instanceof RequestMethod) {
				$requestMethod = $method;
			} else {
				throw new InvalidArgumentException('Invalid request method type: ' . get_debug_type($method));
			}

			$this->methods->add($requestMethod);
		}

		if ($this->methods->isEmpty()) {
			throw new LogicException('Actions need to handle at least one HTTP verb');
		}
	}

	public function getRequestMethods(): GenericList
	{
		return $this->methods;
	}
}
