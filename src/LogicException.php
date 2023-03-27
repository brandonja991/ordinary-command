<?php

declare(strict_types=1);

namespace Ordinary\Command;

use LogicException as PHPLogicException;

class LogicException extends PHPLogicException implements CommandException
{
}
