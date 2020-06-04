<?php
declare(strict_types=1);

namespace Machines\Exceptions;

use Machines\Interfaces\iAcceptor;

class InvalidInputException extends \Exception {

    /**
     * @param mixed $input
     * @param \Throwable $previous
     */
    public function __construct($input, \Throwable $previous = null)
    {
        if (!is_scalar($input)) {
            if (is_object($input)) {
                $input = get_class($input);
            } else {
                $input = gettype($input);
            }
        }
        $message = sprintf('invalid input "%s"', $input);
        parent::__construct($message, 0, $previous);
    }
}