<?php
declare(strict_types=1);

namespace Machines\Acceptors;

use Machines\Exceptions\InvalidInputException;

class LambdaAcceptor extends BaseAcceptor {

    /**
     * @var callable
     */
    private $inputValidator;

    /**
     * @param callable $validator
     */
    public function __construct(callable $validator)
    {
        $this->inputValidator = $validator;
        $this->accepting = false;
    }

    /**
     * Evaluate the accepting status.
     */
    public function evaluate(): self
    {
        $validator = $this->inputValidator;

        if (isset($this->input)) {
            $output = $validator($this->input);

            if (true !== $output) {
                throw new InvalidInputException($this->input);
            }
            
            $this->accepting = true;
        }
        return $this;
    }
}