<?php
declare(strict_types=1);

namespace Machines;

use Machines\Exceptions\InvalidInputException;
use Machines\Exceptions\InvalidStateException;
use Machines\Traits\HasState;

class StateMachine {

    use HasState;

    /**
     * @var array<Transition>
     */
    protected $transitions;

    /**
     * @param array<Transition> $transitions
     * @param State $initialState
     */
    public function __construct(array $transitions = [], State $initialState)
    {
        $this->setTransitions($transitions);
        $this->setState($initialState);
    }

    /**
     * Dispatch an action to affect a transition.
     * 
     * @param string $action
     * @param mixed $input
     * @return StateMachine
     */
    public function dispatch($action, $input = null): StateMachine
    {
        if ($this->state->final) {
            throw new InvalidInputException($input);
        }
        foreach ($this->validTransitions() as $transition) {
            if ($transition->action === $action) {
                $transition->validate($input);
                return $this->transition($transition);
            }
        }
        return $this;
    }

    /**
     * @param State $state
     * @return boolean
     */
    public function isValidState(State $state): bool
    {
        if (!isset($this->transitions)) {
            return true;
        }
        $labels = [];
        foreach ($this->transitions as $transition) {
            $labels[] = $transition->previousState()->label;
        }
        return in_array($state->label, $labels);
    }

    /**
     * Get an array of the next valid states based on the current state.
     * 
     * @return array<State>
     */
    public function nextValidStates(): array
    {
        return array_map(function (Transition $transition) {
            return $transition->nextState();
        }, $this->validTransitions());
    }

    /**
     * Get an array of valid transitions based on the current state.
     * 
     * @return array<Transition>
     */
    public function validTransitions(): array
    {
        return array_filter($this->transitions, function (Transition $transition) {
            return $transition->previousState()->label === $this->state->label;
        });
    }

    /**
     * Set the state machine transitions.
     * 
     * @param array<Transition> $transitions
     * @return StateMachine
     */
    private function setTransitions(array $transitions): StateMachine
    {
        $this->transitions = $transitions;

        return $this;
    }

    /**
     * Handle transition logic. Returns true if transition was successful.
     * 
     * @param Transition $transition
     * @return StateMachine
     */
    private function transition(Transition $transition): self
    {
        return $this->setState($transition->nextState());
    }

    private function validateState(State $state): void
    {
        if (!$this->isValidState($state)) {
            throw new InvalidStateException($state);
        }
    }
}