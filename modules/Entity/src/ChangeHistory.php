<?php
declare(strict_types=1);

namespace Elephox\Entity;

use Elephox\Collection\ArrayList;

/**
 * @extends ArrayList<Contract\ChangeUnit>
 */
class ChangeHistory extends ArrayList implements Contract\ChangeHistory
{
}
