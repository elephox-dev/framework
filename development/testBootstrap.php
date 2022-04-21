<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Elephox\Http\Platform\FakeSessionPlatform;
use Elephox\Platform\Contract\SessionPlatform;
use Elephox\Platform\PlatformManager;

PlatformManager::$services[SessionPlatform::class] = FakeSessionPlatform::class;
