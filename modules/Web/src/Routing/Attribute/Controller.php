<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Attribute;

use Attribute;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericList;
use Elephox\Http\RequestMethod;
use Elephox\Web\Routing\Attribute\Contract\ControllerAttribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Controller implements ControllerAttribute
{
	public const DEFAULT_PATH = '';
	public const DEFAULT_WEIGHT = 0;

	/**
	 * @var ArrayList<RequestMethod> $methods
	 */
	private ArrayList $methods;

	/**
	 * @param null|string $path
	 * @param int $weight
	 * @param non-empty-string|RequestMethod|iterable<non-empty-string|RequestMethod> $methods
	 */
	public function __construct(
		private readonly ?string $path = self::DEFAULT_PATH,
		private readonly int $weight = self::DEFAULT_WEIGHT,
		string|RequestMethod|iterable $methods = [],
	) {
		/** @var ArrayList<RequestMethod> */
		$this->methods = new ArrayList();

		if (is_string($methods) || $methods instanceof RequestMethod) {
			$methods = [$methods];
		}

		foreach ($methods as $method) {
			if (is_string($method)) {
				$method = RequestMethod::tryFrom($method) ?? throw new InvalidArgumentException("Invalid request method: $method");
			} elseif (!$method instanceof RequestMethod) {
				throw new InvalidArgumentException('Invalid request method type: ' . get_debug_type($method));
			}

			$this->methods->add($method);
		}
	}

	public function getWeight(): int
	{
		return $this->weight;
	}

	public function getPath(): ?string
	{
		return $this->path;
	}

	public function getRequestMethods(): GenericList
	{
		return $this->methods;
	}
}
