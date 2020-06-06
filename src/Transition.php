<?php
declare(strict_types=1);

namespace Machines;

use Machines\Interfaces\iAcceptor;
use Machines\Exceptions\InvalidInputException;

/**
 * @property-read string $output
 */
class Transition {

    /**
     * @var iAcceptor
     */
    private $acceptor;

    /**
     * @var State
     */
    private $nextState;

    /**
     * @var callable
     */
    private $onOutput;

    /**
     * @var string
     */
    private $output;

    /**
     * @var boolean
     */
    private $shouldTransition;


    /**
     * @param iAcceptor $acceptor
     * @param State $nextState
     * @param mixed $output
     */
    public function __construct(iAcceptor $acceptor, State $nextState, $output = null)
    {
        $this->acceptor = $acceptor;
        $this->nextState = $nextState;
        $this->output = $output;
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public function accepts($input, StateMachine $machine): bool
    {
        $this->shouldTransition = false;

        try {
            $this->acceptor->input($input, $machine);
            $this->shouldTransition = true;
            $this->emit($this->output($input, $machine));
        } catch (InvalidInputException $e) {
            // var_dump($e->getMessage()."\n".$e->getTraceAsString());
        }

        return $this->shouldTransition;
    }

    /**
     * @return iAcceptor
     */
    public function acceptor(): iAcceptor
    {
        return $this->acceptor;
    }

    /**
     * @param mixed $output
     * @return void
     */
    private function emit($output = null)
    {
        if (isset($this->onOutput) && !is_null($output)) {
            call_user_func($this->onOutput, $output);
        }
    }

    /**
     * @return State
     */
    public function nextState(): State
    {
        return $this->nextState;
    }

    /**
     * @param callable $function
     * @return void
     */
    public function onOutput(callable $function)
    {
        if (!is_callable($function)) {
            throw new \InvalidArgumentException('out callback must be a callable');
        }
        $this->onOutput = $function;
    }

    /**
     * @param mixed $input
     * @param StateMachine $machine
     * @return mixed
     */
    public function output($input = null, StateMachine $machine = null)
    {
        if (is_callable($this->output)) {
            $out = $this->output;
            return $out($input, $machine);
        } else {
            return $this->output;
        }
    }

    /**
     * @return boolean
     */
    public function shouldTransition(): bool
    {
        return $this->shouldTransition;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return null;
    }
}