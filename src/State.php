<?php
declare(strict_types=1);

namespace Machines;

/**
 * @property-read string $label
 */
class State {

    /**
     * The internal label
     * 
     * @var string
     */
    private $label;

    public function __construct(string $label)
    {
        $this->label = $label;
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