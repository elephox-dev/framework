<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Elephox\Http\Platform\FakeSessionPlatform;
use Elephox\Platform\Session;

Session::$implementation = FakeSessionPlatform::class;
