<?php
declare(strict_types=1);

namespace Machines;

use Machines\Exceptions\InvalidInputException;
use Machines\Traits\HasState;

class Transducer extends StateMachine {

    use HasState;

    /**
     * @var array<string>
     */
    private $accumulator;

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
     * @param array<State> $states
     */
    public function __construct(array $states)
    {
        parent::__construct($states);
        $this->registerOutputHandlers();
    }

    /**
     * @param mixed $input
     * @return self
     */
    public function accumulate($input = null): self
    {
        if (!is_null($input)) {
            $this->accumulator[] = $input;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function collect(): ?string
    {
        $output = null;
        if (!empty($this->accumulator)) {
            $output = implode($this->accumulator);
            $this->accumulator = [];
        }
        return $output;
    }

    /**
     * @param mixed $input
     * @return null|array<mixed>
     */
    public function consume($input): ?array
    {
        $this->output = [];
        $finalState = $this->getFinalState();

        $input = $this->stringify($input);
        if (is_string($input)) {
            $input = str_split($input);
        }

        foreach ($input as $char) {
            $this->input($char);
        }

        if (!$finalState || $finalState->label === $this->state->label) {
            if (!empty($this->accumulator)) {
                $this->output[] = $this->collect();
            }

            return $this->output;
        }
        return null;
    }

    /**
     * @params string $label
     * @return StateMachine
     */
    public function to(string $label): StateMachine
    {
        if ($state = $this->getState($label)) {
            return $this->setState($state);
        }
        return $this;
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
     * @param Transition $output
     * @return void
     */
    public function onOutput($output): void
    {
        $this->output[] = $output;
        $this->output = array_filter($this->output, function ($out) {
            return $out !== null;
        });
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
                if ($transition->accepts($input, $this) === true) {
                    
                    $this->transition($transition);
    
                    return end($this->output);
                }
            }
        }

        return null;
    }

    /**
     * @return void
     */
    private function registerOutputHandlers()
    {
        foreach ($this->states as $state) {
            foreach ($state->transitions as $transition) {
                $transition->onOutput([$this, 'onOutput']);
            }
        }
    }

    /**
     * @param mixed $input
     * @return string|array<string>
     */
    private function stringify($input)
    {
        switch (gettype($input)) {
            case "string":
            break;
            case "boolean":
                $input = intval($input);
            case "integer":
            case "double":
                $input = (string) $input;
            break;
            case "array":
                foreach ($input as $key => $value) {
                    $input[$key] = $this->stringify($value);
                }
            break;
            case "object":
                $input = $this->stringify((array) $input);
            break;
            case "NULL":
                $input = '';
            break;
            default:
                throw new \InvalidArgumentException(sprintf('cannot stringify input of type "%s"', gettype($input)));
        }

        return $input;
    }
}