<?php

namespace Tests\Feature;

use Machines\Acceptors\LambdaAcceptor;
use Machines\State;
use Machines\StateMachine;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class StateMachineTest extends TestCase {

    public function testTurnstileMachine()
    {
        $Locked = new State('Locked');
        $Unlocked = new State('Unlocked');
        $coinAcceptor = new LambdaAcceptor(function ($input) {
            return $input === 'coin';
        });

        $toLocked = new Transition('PUSH', $Unlocked, $Locked);
        $toUnlocked = new Transition('UNLOCK', $Locked, $Unlocked, $coinAcceptor);

        $machine = new StateMachine([$toLocked, $toUnlocked], $Locked);

        $this->assertEquals('Locked', $machine->state()->label);

        $machine->dispatch('UNLOCK', 'coin');

        $this->assertEquals('Unlocked', $machine->state()->label);

        $machine->dispatch('PUSH');

        $this->assertEquals('Locked', $machine->state()->label);
    }
}