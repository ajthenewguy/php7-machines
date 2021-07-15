<?php
declare(strict_types=1);

namespace Machines;

/**
 * @property-read string $final
 * @property-read string $label
 * @property-read array $transitions
 */
class State {

    /**
     * Flag to indicate a final state.
     * 
     * @var bool
     */
    private $final;

    /**
     * The internal label
     * 
     * @var string
     */
    private $label;

    /**
     * @var array<Transition>
     */
    private $transitions = [];

    /**
     * @param string $label
     * @param boolean $final
     */
    public function __construct(string $label, bool $final = false)
    {
        $this->label = $label;
        $this->final = $final;
    }

    /**
     * @param string $label
     * @param boolean $final
     * @return self
     */
    public static function create(string $label, bool $final = false): self
    {
        return new self($label, $final);
    }

    /**
     * @param array<Transition> $transitions
     * @return self
     */
    public function setTransitions(array $transitions = []): self
    {
        $this->transitions = [];

        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }

        return $this;
    }

    /**
     * @param Transition $transition
     * @return self
     */
    public function addTransition(Transition $transition): self
    {
        $this->transitions[] = $transition;
        return $this;
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