<?php
declare(strict_types=1);

namespace Machines\Acceptors;

use Machines\Exceptions\InvalidInputException;

class MatchAcceptor extends BaseAcceptor {

    /**
     * @var mixed
     */
    private $match;

    /**
     * Convert match value to a string for transducer comparison;
     * given an input of integer 1100, transducers will split it
     * into an array of characters for character by character
     * translation.
     * 
     * @param scalar $match
     * @param boolean $toString
     */
    public function __construct($match, $toString = false)
    {
        $this->match = $toString ? (string) $match : $match;
        $this->accepting = false;
    }

    /**
     * Evaluate the accepting status.
     */
    public function evaluate(): self
    {
        if (isset($this->input)) {
            if ($this->match !== $this->input) {
                throw new InvalidInputException($this->input);
            }
            
            $this->accepting = true;
        }
        return $this;
    }
}