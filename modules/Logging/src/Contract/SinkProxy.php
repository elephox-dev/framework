<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

interface SinkProxy
{
	public function getInnerSink(): Sink;
}
