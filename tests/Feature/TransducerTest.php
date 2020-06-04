<?php

namespace Tests\Feature;

use Machines\Acceptors\LambdaAcceptor;
use Machines\State;
use Machines\Transducer;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class TransducerTest extends TestCase {

    public function testTokenizerMachine()
    {
        $Quoted = new State('Quoted');
        $Unquoted = new State('Unquoted');

        $parser = function ($char, $machine) {
            static $current_word = '';
            $output = null;

            // null terminator
            if ($char === null) {
                return $current_word;
            }

            if ($machine->is('Quoted')) {
                if ($char === "'") {
                    $output = $current_word;
                    $current_word = '';
                    $machine->to('Unquoted');
                } else {
                    $current_word .= $char;
                }
            } elseif ($machine->is('Unquoted')) {
                if ($char === "'") {
                    $machine->to('Quoted');
                } elseif ($char === ' ') {
                    if ($current_word) {
                        $output = $current_word;
                    }
                    $current_word = '';
                } else {
                    $current_word .= $char;
                }
            }
            
            return $output;
        };


        $machine = new Transducer($parser, [$Unquoted, $Quoted]);
        $input = "ls -la 'My Documents' /home /etc";

        $this->assertEquals('Unquoted', $machine->state()->label);

        $output = $machine->consume($input);

        $this->assertEquals(['ls', '-la', 'My Documents', '/home', '/etc'], $machine->output());
        $this->assertEquals($output, $machine->output());
    }

    public function testBinaryTransducer()
    {
        $state0 = new State(0);
        $state1 = new State(1);
        $state2 = new State(2);

        // $is0to1 = new Transition('1', $state0, $state1, new LambdaAcceptor(function ($char) {
        //     if ($char === '1') {
        //         return [true, '0'];
        //     }
        //     return [false, '0'];
        // }));

        $parser = function ($char, $machine) {
            // null terminator
            if ($char === null) {
                return null;
            }

            if ($machine->is('0')) {
                if ($char === '1') {
                    $machine->to('1');
                }
                return '0';
            } elseif ($machine->is('1')) {
                if ($char === '0') {
                    $machine->to('2');
                    return '0';
                } elseif ($char === '1') {
                    $machine->to('0');
                    return '1';
                }
            } elseif ($machine->is('2')) {
                if ($char === '0') {
                    $machine->to('1');
                    return '1';
                } elseif ($char === '1') {
                    return '1';
                }
            }

            return null;
        };

        $machine = new Transducer($parser, [$state0, $state1, $state2]);

        $this->assertEquals('0', $machine->state()->label);
        $this->assertEquals(['0'], $machine->consume('0'));
        $this->assertEquals(['0', '1'], $machine->consume('11'));
        $this->assertEquals(['0', '1', '0'], $machine->consume('110'));
        $this->assertEquals(['0', '0', '1', '1'], $machine->consume('1001'));
        $this->assertEquals(['0', '1', '0', '0'], $machine->consume('1100'));
        $this->assertEquals(['0', '1', '0', '1'], $machine->consume('1111'));
        $this->assertEquals(['0', '0', '1', '1', '0'], $machine->consume('10010'));
    }
}