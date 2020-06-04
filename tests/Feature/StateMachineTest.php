<?php

namespace Tests\Feature;

use Machines\Acceptors\LambdaAcceptor;
use Machines\State;
use Machines\StateMachine;
use Machines\Transducer;
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

    public function testTransducer()
    {
        $Quoted = new State('Quoted');
        $Unquoted = new State('Unquoted');

        $parser = new LambdaAcceptor(function ($input) {
            static $current_word = '';
            list($machine, $char) = $input;
            $output = null;

            if ($machine->state()->label === 'Quoted') {
                if ($char === "'") {
                    $output = $current_word;
                    $current_word = '';
                    $machine->changeState('Unquoted');
                } else {
                    $current_word .= $char;
                }
            } elseif ($machine->state()->label === 'Unquoted') {
                if ($char === "'") {
                    $machine->changeState('Quoted');
                } elseif ($char === ' ') {
                    if ($current_word) {
                        $output = $current_word;
                    }
                    $current_word = '';
                } else {
                    $current_word .= $char;
                }
            }

            // null terminator
            if ($char === null) {
                $output = $current_word;
                $current_word = '';
            }
            
            return $output;
        });


        $machine = new Transducer($parser, [$Quoted, $Unquoted], $Unquoted);
        $input = "ls -la 'My Documents' /home /etc";

        $this->assertEquals('Unquoted', $machine->state()->label);

        $output = $machine->consume($input);

        $this->assertEquals(['ls', '-la', 'My Documents', '/home', '/etc'], $machine->output());
        $this->assertEquals($output, $machine->output());
    }
}