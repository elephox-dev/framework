<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;

interface ConfigurationManager extends ConfigurationBuilder, ConfigurationRoot
{
}
