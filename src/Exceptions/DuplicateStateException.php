<?php
declare(strict_types=1);

namespace Machines\Exceptions;

use Machines\State;

class DuplicateStateException extends \Exception {

    public function __construct(State $state, \Throwable $previous = null)
    {
        $message = sprintf('machine cannot assign duplicate state "%s"',
            $state->label
        );
        parent::__construct($message, 0, $previous);
    }
}