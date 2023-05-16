<?php
declare(strict_types=1);

namespace Elephox\Logging;

enum SinkCapability
{
	case AnsiFormatting;
	case ElephoxFormatting;
	case MessageTemplates;
}
