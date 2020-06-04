<?php
declare(strict_types=1);

namespace Machines\Interfaces;

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
     * @return bool
     */
    public function input($input): bool;
}