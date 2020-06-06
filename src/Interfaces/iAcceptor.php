<?php
declare(strict_types=1);

namespace Machines\Interfaces;

use Machines\StateMachine;

interface iAcceptor {

    /**
     * @return bool
     */
    public function accepting(): bool;

    /**
     * @return iAcceptor
     */
    public function evaluate(): iAcceptor;

    /**
     * @param mixed $input
     * @param StateMachine $machine
     * @return bool
     */
    public function input($input, StateMachine $machine = null): bool;
}