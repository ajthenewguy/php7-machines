<?php
declare(strict_types=1);

namespace Machines\Acceptors;

use Machines\Interfaces\iAcceptor;
use Machines\StateMachine;

abstract class BaseAcceptor implements iAcceptor {

    /**
     * @var boolean
     */
    protected $accepting;

    /**
     * @var mixed
     */
    protected $input;

    /**
     * @var StateMachine
     */
    protected $machine;

    /**
     * @return bool
     */
    public function accepting(): bool
    {
        return $this->evaluate()->accepting;
    }

    /**
     * Evaluate the accepting status.
     */
    abstract public function evaluate(): self;

    /**
     * @param mixed $input
     * @param StateMachine $machine
     * @return bool
     */
    public function input($input, StateMachine $machine = null): bool
    {
        $this->input = $input;
        if ($machine) {
            $this->machine = $machine;
        }
        return $this->accepting();
    }
}
