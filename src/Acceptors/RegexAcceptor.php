<?php
declare(strict_types=1);

namespace Machines\Acceptors;

use Machines\Exceptions\InvalidInputException;
use Machines\Regex;

class RegexAcceptor extends BaseAcceptor {

    /**
     * @var mixed
     */
    private $match;

    /**
     * @var Regex
     */
    private $regex;

    /**
     * @param string $regex
     */
    public function __construct(string $regex)
    {
        $this->regex = new Regex($regex);
        $this->accepting = false;
    }

    /**
     * Evaluate the accepting status.
     */
    public function evaluate(): self
    {
        if (isset($this->input)) {
            if (!$this->regex->test((string) $this->input)) {
                throw new InvalidInputException($this->input);
            }
            $this->accepting = true;
        }
        return $this;
    }
}