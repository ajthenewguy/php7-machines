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
     * @param array<Transition> $transitions
     * @param boolean $final
     */
    public function __construct(string $label, array $transitions = [], bool $final = false)
    {
        $this->label = (string) $label;
        $this->setTransitions($transitions);
        $this->final = $final;
    }

    /**
     * @param array<Transition> $transitions
     * @return self
     */
    public function setTransitions(array $transitions = []): self
    {
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