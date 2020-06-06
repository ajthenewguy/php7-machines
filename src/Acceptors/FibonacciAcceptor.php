<?php

declare(strict_types=1);

namespace Machines\Acceptors;

use Ds\Stack;
use Machines\Exceptions\InvalidInputException;
use Machines\StateMachine;

class FibonacciAcceptor extends BaseAcceptor {

    public function __construct()
    {
        $this->input = new Stack;
        $this->accepting = false;
    }

    /**
     * Evaluate the accepting status.
     */
    public function evaluate(): FibonacciAcceptor
    {
        $this->accepting = false;
        $inputCount = $this->input->count();
        if ($inputCount > 0) {
            if ($this->input->peek() !== $this->get($inputCount - 1)) {
                throw new InvalidInputException($this->input->peek());
            }
            $this->accepting = true;
        }
        return $this;
    }

    /**
     * @param mixed $input
     * @param StateMachine $machine
     * @return bool
     */
    public function input($input, StateMachine $machine = null): bool
    {
        $this->input->push($input);
        if ($machine) {
            $this->machine = $machine;
        }
        return $this->evaluate()->accepting();
    }

    /**
     * Get the fibonacci number for the provided index.
     * 
     * @param int $index
     * @return int
     */
    private function get(int $index): int
    {
        switch ($index) {
            case 0:
            case 1:
                return $index;
            default:
                return $this->get($index - 2) + $this->get($index - 1);
        }
    }
}