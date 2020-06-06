<?php

namespace Tests\Feature;

use Machines\Acceptors\MatchAcceptor;
use Machines\State;
use Machines\StateMachine;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class StateMachineTest extends TestCase {

    public function testTurnstileMachine()
    {
        $Locked = new State('Locked');
        $Unlocked = new State('Unlocked');

        $Locked->setTransitions([
            new Transition(new MatchAcceptor('coin'), $Unlocked)
        ]);
        $Unlocked->setTransitions([
            new Transition(new MatchAcceptor('push'), $Locked)
        ]);

        $machine = new StateMachine([$Locked, $Unlocked]);

        $this->assertEquals('Locked', $machine->state()->label);

        $machine->input('coin');

        $this->assertTrue($machine->is('Unlocked'));

        $machine->input('push');

        $this->assertTrue($machine->is('Locked'));
    }
}