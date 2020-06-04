<?php
declare(strict_types=1);

namespace Machines\Acceptors;

use Machines\Exceptions\InvalidInputException;
use Machines\Interfaces\iAcceptor;

class LambdaAcceptor implements iAcceptor {

    /**
     * @var boolean
     */
    private $accepting;
    
    /**
     * @var callable
     */
    private $inputValidator;

    /**
     * @var mixed
     */
    private $input;

    public function __construct(callable $validator)
    {
        $this->inputValidator = $validator;
        $this->accepting = false;
    }

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
    public function evaluate(): LambdaAcceptor
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

    /**
     * @param mixed $input
     * @return bool
     */
    public function input($input): bool
    {
        $this->input = $input;
        return $this->accepting();
    }
}