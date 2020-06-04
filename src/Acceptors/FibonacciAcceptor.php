<?php

declare(strict_types=1);

namespace Machines\Acceptors;

use Ds\{Stack, Vector};
use Machines\Exceptions\InvalidInputException;
use Machines\Interfaces\iAcceptor;

class FibonacciAcceptor implements iAcceptor
{

    /**
     * @var boolean
     */
    private $accepting;

    /**
     * @var mixed
     */
    private $input;

    public function __construct()
    {
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