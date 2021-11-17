<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HttpAdapter;

class Client implements Contract\Client
{
	public function __construct(
		private HttpAdapter $adapter
	)
	{
	}

	public function execute(Contract\Request $request): Contract\Response
	{
		$url = $request->getUrl()->toString();

		/** @psalm-suppress UndefinedPropertyFetch Until vimeo/psalm#6468 is fixed */
		$headers = $request
			->getHeaders()
			->reduce(static fn(array $values, string $name) => "$name: $values[0]")
			->asArray();

		$method = $request->getMethod()->getValue();

		try {
			$this->adapter
				->prepare()
				->setUrl($url)
				->setMethod($method)
				->setHeaders($headers)
				->setBody($request->getBody());

			if (!$this->adapter->send()) {
				throw new ClientException("Failed to send request: {$this->adapter->getLastError()}");
			}

			/** @var string $output */
			$output = $this->adapter->getResponse();

			return Response::fromString($output);
		} finally {
			$this->adapter->cleanup();
		}
	}
}
