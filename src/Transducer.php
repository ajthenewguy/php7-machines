<?php
declare(strict_types=1);

namespace Machines;

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
     * @var callable
     */
    private $consumer;

    /**
     * @param callable $consumer
     * @param array<State> $states
     */
    public function __construct(callable $consumer, array $states)
    {
        $this->consumer = $consumer;
        $this->setStates($states);
        $this->state = $states[0];
    }

    /**
     * @params string $label
     * @return Transducer
     */
    public function to(string $label): Transducer
    {
        if ($state = $this->getState($label)) {
            return $this->setState($state);
        }
        return $this;
    }

    /**
     * @param string $input
     * @return array<mixed>
     */
    public function consume(string $input): array
    {
        $this->output = [];
        $finalState = $this->getFinalState();

        foreach (str_split($input) as $char) {
            $this->input($char);
        }
        $this->input(); // null terminate string

        if (!$finalState || $finalState->label === $this->state->label) {
            return $this->output;
        }
        
    }

    /**
     * Find and return the final state
     * 
     * @return State|null
     */
    public function getFinalState(): ?State
    {
        foreach ($this->states as $state) {
            if ($state->final) {
                return $state;
            }
        }
        return null;
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
     * Check if the machine has a final state
     * 
     * @return boolean
     */
    public function hasFinalState(): bool
    {
        return !!$this->getFinalState();
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

    /**
     * Dispatch an action to affect a transition.
     * 
     * @param mixed $input
     * @return void
     */
    private function input($input = null): void
    {
        $consumer = $this->consumer;
        $output = $consumer($input, $this);

        switch (true) {
            case !is_null($output):
            case is_string($output) && strlen($output):
            case is_array($output) && !empty($output):
                $this->output[] = $output;
            break;
        }
    }

    /**
     * @param array<State>
     * @return Transducer
     */
    private function setStates(array $states): self
    {
        $has_final = false;
        foreach ($states as $state) {
            if ($state->final) {
                if ($has_final) {
                    throw new \InvalidArgumentException('Transducers may only have one final State');
                } else {
                    $has_final = true;
                }
            }
        }
        $this->states = $states;

        return $this;
    }

    /**
     * @param State $state
     * @return void
     */
    private function validateState(State $state): void
    {
        if (!$this->isValidState($state)) {
            throw new InvalidStateException($state);
        }
    }
}