<?php

declare(strict_types=1);

namespace Ordinary\Command;

use UnexpectedValueException as PHPUnexpectedValueException;

class UnexpectedValueException extends PHPUnexpectedValueException implements CommandException
{
}
