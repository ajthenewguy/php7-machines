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

    /**
     * @var mixed
     */
    private $output;

    public function __construct(callable $validator)
    {
        $this->inputValidator = $validator;
        $this->accepting = false;
        $this->output = [];
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
        $this->output = [];

        if (isset($this->input)) {
            $output = $validator($this->input);

            switch (true) {
                case $output === null:
                break;
                case $output === false:
                    throw new InvalidInputException($this->input);
                default:
                    $this->output = $output;
                break;
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

    /**
     * Get the output tape
     * 
     * @return mixed
     */
    public function output()
    {
        return $this->output;
    }
}