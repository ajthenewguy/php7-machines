<?php
declare(strict_types=1);

namespace Machines\Traits;

use Machines\State;
use Machines\Exceptions\InvalidStateException;

trait HasState {

    /**
     * @var State
     */
    protected $state;

    /**
     * @param string $label
     * @return boolean
     */
    public function is(string $label): bool
    {
        return $this->state->label === $label;
    }

    /**
     * Get the current state.
     *
     * @return State
     */
    public function state(): State
    {
        return $this->state;
    }

    /**
     * @param State $previousState
     * @return void
     */
    protected function onTransition(State $previousState): void
    {
        // Override to react to transitions
    }

    /**
     * @param string $string
     * @return string
     */
    private static function camelCase(string $string): string
    {
        $str = strtolower($string);
        if ($_str = preg_replace('/^a-z0-9]+/', ' ', $str)) {
            $str = lcfirst(str_replace(' ', '', ucwords($_str)));
        }
        return $str;
    }

    /**
     * Call post-transition handlers
     * @return void
     */
    private function callPostTransitionHandlers(State $previousState): void
    {
        $fromHandler = 'from' . ucfirst(self::camelCase($previousState->label));
        $toHandler = 'on' . ucfirst(self::camelCase($this->state->label));

        $this->invokeCustomHandler($fromHandler, $previousState);
        $this->invokeCustomHandler($toHandler, $previousState);
    }

    /**
     * Call pre-transition handlers
     * @return void
     */
    private function callPreTransitionHandlers(State $nextState): void
    {
        $beforeHandler = 'before' . ucfirst(self::camelCase($nextState->label));

        $this->invokeCustomHandler($beforeHandler, $nextState);
    }

    /**
     * Handle transition logic.
     * 
     * @param State $state
     * @return self
     */
    private function setState(State $state): self
    {
        if (!$this->isValidState($state)) {
            throw new InvalidStateException($state);
        }

        if (!isset($this->state)) {
            $this->state = $state;
        } elseif ($state->label !== $this->state->label) {
            // Call pre-transition handlers
            try {
                $this->callPreTransitionHandlers($state);
            } catch (\Throwable $e) {
                return $this;
            }

            $previousState = $this->state;
            $this->state = $state;

            // Call post-transition handlers
            try {
                $this->callPostTransitionHandlers($previousState);
                $this->onTransition($previousState);
            } catch (\Throwable $e) {
                $this->state = $previousState;
                return $this;
            }
        }

        return $this;
    }

    /**
     * Call custom transition handler.
     * For example transtion from "LOCKED" to "UNLOCKED", the following handlers, if defined,
     * will be called by $this->transition():
     *  $this->fromLocked($nextState)
     *  $this->onUnlocked($previousState)
     * 
     * @param string $method_name
     * @param State $passedState
     * @return void
     */
    private function invokeCustomHandler($method_name, State $passedState): void
    {
        if (method_exists($this, $method_name)) {
            $method = [$this, $method_name];
            if (is_callable($method)) {
                call_user_func($method, $passedState);
            }
        }
    }
}