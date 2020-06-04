<?php
declare(strict_types=1);

namespace Machines;

use Machines\Interfaces\iAcceptor;
use Machines\Exceptions\InvalidStateException;
use Machines\Traits\HasState;

class Transducer {

    use HasState;

    /**
     * @var State
     */
    protected $state;
 
    /**
     * @var array<State>
     */
    protected $states;

    /**
     * output tape
     * 
     * @var array<mixed>
     */
    private $output;

    /**
     * @var iAcceptor
     */
    private $consumer;

    /**
     * @param iAcceptor $consumer
     * @param array<State> $states
     * @param State $initialState
     */
    public function __construct(iAcceptor $consumer, array $states, State $initialState)
    {
        $this->consumer = $consumer;
        $this->states = $states;
        $this->validateState($initialState);
        $this->state = $initialState;
        $this->output = [];
    }

    /**
     * @params string $label
     * @return Transducer
     */
    public function changeState(string $label): Transducer
    {
        if ($state = $this->getState($label)) {
            return $this->setState($state);
        }
        return $this;
    }

    /**
     * @param mixed $input
     * @return array<mixed>
     */
    public function consume($input): array
    {
        foreach (str_split($input) as $char) {
            $this->input($char);
        }
        $this->input(); // null terminate string

        return $this->output;
    }

    /**
     * @param string $label
     * @return State|null
     */
    public function getState(string $label): ?State
    {
        foreach ($this->states as $state) {
            if ($state->label === $label) {
                return $state;
            }
        }
        return null;
    }

    /**
     * Dispatch an action to affect a transition.
     * 
     * @param mixed $input
     * @return void
     */
    public function input($input = null): void
    {
        $this->consumer->input([$this, $input]);

        if ($output = $this->consumer->output()) {
            $this->output[] = $output;
        }
    }

    /**
     * @param State $state
     * @return boolean
     */
    public function isValidState(State $state): bool
    {
        if (!isset($this->states)) {
            return false;
        }
        $labels = [];
        foreach ($this->states as $state) {
            $labels[] = $state->label;
        }
        return in_array($state->label, $labels);
    }

    /**
     * Get the output tape
     * @return array<mixed>
     */
    public function output(): array
    {
        return $this->output;
    }

    private function validateState(State $state): void
    {
        if (!$this->isValidState($state)) {
            throw new InvalidStateException($state);
        }
    }
}