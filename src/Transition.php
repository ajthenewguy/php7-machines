<?php
declare(strict_types=1);

namespace Machines;

use Machines\Interfaces\iAcceptor;
use Machines\Exceptions\InvalidInputException;

/**
 * @property-read string $action
 */
class Transition {

    /**
     * @var string
     */
    private $action;

    /**
     * @var iAcceptor
     */
    private $acceptor;

    /**
     * @var State
     */
    private $nextState;

    /**
     * @var State
     */
    private $previousState;

    /**
     * @param string $action
     * @param State $previousState
     * @param State $nextState
     * @param null|iAcceptor $acceptor
     */
    public function __construct(string $action, State $previousState, State $nextState, iAcceptor $acceptor = null)
    {
        $this->action = $action;
        $this->setPreviousState($previousState);
        $this->setAcceptor($acceptor);
        $this->setNextState($nextState);
    }

    /**
     * @return State
     */
    public function previousState(): State
    {
        return $this->previousState;
    }

    /**
     * @return State
     */
    public function nextState(): State
    {
        return $this->nextState;
    }

    /**
     * @return iAcceptor
     */
    public function acceptor(): iAcceptor
    {
        return $this->acceptor;
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public function accepts($input): bool
    {
        try {
            $this->validate($input);
        } catch (InvalidInputException $e) {
            return false;
        }

        return $this->acceptor->accepting();
    }

    /**
     * @param mixed $input
     * @return array<mixed>
     */
    public function input($input = null): array
    {
        if (isset($this->acceptor)) {
            $this->acceptor->input($input);
            return $this->acceptor->output();
        }
        return [null, null];
    }

    /**
     * @param mixed $input
     * @return void
     */
    public function validate($input = null): void
    {
        if (isset($this->acceptor)) {
            $this->acceptor->input($input);
        }
    }

    /**
     * @param null|iAcceptor $acceptor
     * @return void
     */
    public function setAcceptor(iAcceptor $acceptor = null): void
    {
        if ($acceptor) {
            $this->acceptor = $acceptor;
        }
    }

    private function setNextState(State $state): void
    {
        if (isset($this->previousState) && $state->label === $this->previousState->label) {
            throw new \LogicException(sprintf('cannot transition to and from the same state'));
        }
        $this->nextState = $state;
    }

    private function setPreviousState(State $state): void
    {
        if (isset($this->nextState) && $state->label === $this->nextState->label) {
            throw new \LogicException(sprintf('cannot transition to and from the same state'));
        }
        $this->previousState = $state;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function __get($name): ?string
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }
}