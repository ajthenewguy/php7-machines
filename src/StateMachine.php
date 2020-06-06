<?php
declare(strict_types=1);

namespace Machines;

use Machines\Exceptions\InvalidInputException;
use Machines\Traits\HasState;

class StateMachine {

    use HasState;

    /**
     * @param array<State> $states
     */
    public function __construct(array $states)
    {
        $this->setStates($states);
        $this->setState($states[0]);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public function input($input = null)
    {
        if ($this->state->final) {
            throw new InvalidInputException($input);
        }

        $validTransitions = $this->validTransitions();
        if (is_array($validTransitions)) {
            foreach ($validTransitions as $transition) {
                if ($transition->accepts($input, $this)) {
                    $this->transition($transition);
                }
            }
        }

        return null;
    }

    /**
     * Get an array of the next valid states based on the current state.
     * 
     * @return array<State>|null
     */
    public function nextValidStates(): ?array
    {
        $validTransitions = $this->validTransitions();
        if (is_array($validTransitions)) {
            return array_unique(array_map(function (Transition $transition) {
                return $transition->nextState();
            }, $validTransitions));
        }
        return null;
    }

    /**
     * Get an array of valid transitions based on the current state.
     * 
     * @return null|array<Transition>
     */
    public function validTransitions(): ?array
    {
        if ($this->state->final) {
            return null;
        }

        foreach ($this->states as $state) {
            if ($this->state->label === $state->label) {
                return $state->transitions;
            }
        }
   
        throw new \LogicException(sprintf('Current state "%s" is has no valid transitions and is not marked final', $this->state->label));
    }

    /**
     * Handle transition logic. Returns true if transition was successful.
     * 
     * @param Transition $transition
     * @return self
     */
    protected function transition(Transition $transition): self
    {
        return $this->setState($transition->nextState());
    }
}