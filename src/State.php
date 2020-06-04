<?php
declare(strict_types=1);

namespace Machines;

/**
 * @property-read string $label
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

    public function __construct(string $label, bool $final = false)
    {
        $this->label = (string) $label;
        $this->final = $final;
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