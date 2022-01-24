<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Collection\ArrayMap;
use Elephox\Http\Url;
use InvalidArgumentException;

class MatchedUrlTemplate extends ArrayMap implements Contract\MatchedUrlTemplate
{
	public function __construct(
		protected Url $url,
		protected Contract\UrlTemplate $template
	) {
		if (!$this->template->matches($url)) {
			throw new InvalidArgumentException('Url does not match template');
		}

		parent::__construct($this->template->getValues($url)->toArray());
	}

	public function getTemplateSource(): string
	{
		return $this->template->getSource();
	}

	public function getUrl(): Url
	{
		return $this->url;
	}
}
