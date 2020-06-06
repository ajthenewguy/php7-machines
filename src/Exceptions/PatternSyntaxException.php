<?php

declare(strict_types=1);

namespace Machines\Exceptions;

use Machines\Regex;

class PatternSyntaxException extends \InvalidArgumentException
{

    public function __construct(?string $message, string $regex, \Throwable $previous = null)
    {
        $message = sprintf(
            ($message ?: 'unexpected regular expression error with pattern') . ' "%s"',
            $regex
        );
        parent::__construct($message, 0, $previous);
    }
}
