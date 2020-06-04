<?php

declare(strict_types=1);

namespace Machines\Acceptors;

use Ds\{Stack, Vector};
use Machines\Exceptions\InvalidInputException;
use Machines\Interfaces\iAcceptor;

class SequenceAcceptor implements iAcceptor {

    /**
     * @var boolean
     */
    private $accepting;

    /**
     * @var Vector<mixed>
     */
    private $sequence;

    /**
     * @var mixed
     */
    private $input;

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
     * @param mixed $input
     * @return bool
     */
    public function input($input): bool
    {
        $this->input->push($input);
        return $this->evaluate()->accepting();
    }

    /**
     * Get the output tape
     * @return mixed
     */
    public function output()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function accepting(): bool
    {
        return $this->accepting;
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
}
