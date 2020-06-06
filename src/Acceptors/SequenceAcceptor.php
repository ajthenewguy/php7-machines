<?php

declare(strict_types=1);

namespace Machines\Acceptors;

use Ds\{Stack, Vector};
use Machines\Exceptions\InvalidInputException;
use Machines\StateMachine;

class SequenceAcceptor extends BaseAcceptor {

     /**
     * @var Vector<mixed>
     */
    private $sequence;

    /**
     * @param array<mixed> $sequence
     */
    public function __construct(array $sequence)
    {
        $this->sequence = new Vector($sequence);
        $this->input = new Stack;
        $this->accepting = false;
    }

    /**
     * Evaluate the accepting status.
     */
    public function evaluate(): SequenceAcceptor
    {
        $this->accepting = false;
        $inputCount = $this->input->count();
        if ($inputCount > 0 && $inputCount <= $this->sequence->count()) {
            if ($this->input->peek() !== $this->sequence->get($inputCount - 1)) {
                throw new InvalidInputException($this->input->peek());
            }
            $this->accepting = $inputCount === $this->sequence->count();
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
}
