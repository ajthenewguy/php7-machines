<?php

namespace Tests\Feature;

use Machines\Acceptors\MatchAcceptor;
use Machines\Acceptors\RegexAcceptor;
use Machines\State;
use Machines\Transducer;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class TransducerTest extends TestCase {

    public function testTokenizerMachine()
    {
        $Quoted = new State('Quoted');
        $Unquoted = new State('Unquoted');

        $Unquoted->setTransitions([
            new Transition(new MatchAcceptor("'"), $Quoted),
            new Transition(new MatchAcceptor(" "), $Unquoted, function ($input, $transducer) {
                return $transducer->collect();
            }),
            new Transition(new RegexAcceptor("/[^\s\']/"), $Unquoted, function ($input, $transducer) {
                $transducer->accumulate($input);
            })
        ]);
        $Quoted->setTransitions([
            new Transition(new MatchAcceptor("'"), $Unquoted, function ($input, $transducer) {
                return $transducer->collect();
            }),
            new Transition(new RegexAcceptor("/[^\']/"), $Quoted, function ($input, $transducer) {
                $transducer->accumulate($input);
            })
        ]);

        $machine = new Transducer([$Unquoted, $Quoted]);
        $input = "ls -la 'My Documents' /home /etc";
        $array = explode(' ', $input);

        $this->assertEquals('Unquoted', $machine->state()->label);

        $output = $machine->consume($input);

        $this->assertEquals(['ls', '-la', 'My Documents', '/home', '/etc'], $machine->output());
        $this->assertEquals($output, $machine->output());
    }

    public function testBinaryTransducerStrings()
    {
        $state0 = new State(0);
        $state1 = new State(1);
        $state2 = new State(2);
        $match0 = new MatchAcceptor('0');
        $match1 = new MatchAcceptor('1');

        $state0->setTransitions([
            new Transition($match0, $state0, '0'),
            new Transition($match1, $state1, '0')
        ]);
        $state1->setTransitions([
            new Transition($match0, $state2, '0'),
            new Transition($match1, $state0, '1')
        ]);
        $state2->setTransitions([
            new Transition($match0, $state1, '1'),
            new Transition($match1, $state2, '1')
        ]);
 
        $machine = new Transducer([$state0, $state1, $state2]);

        $this->assertEquals('0', $machine->state()->label);
        $this->assertEquals(['0'], $machine->consume('0'));
        $this->assertEquals(['0', '1'], $machine->consume('11'));
        $this->assertEquals(['0', '1', '0'], $machine->consume('110'));
        $this->assertEquals(['0', '0', '1', '1'], $machine->consume('1001'));
        $this->assertEquals(['0', '1', '0', '0'], $machine->consume('1100'));
        $this->assertEquals(['0', '1', '0', '1'], $machine->consume('1111'));
        $this->assertEquals(['0', '0', '1', '1', '0'], $machine->consume('10010'));
    }

    public function testBinaryTransducerIntegers()
    {
        $state0 = new State(0);
        $state1 = new State(1);
        $state2 = new State(2);
        $match0 = new MatchAcceptor(0, true);
        $match1 = new MatchAcceptor(1, true);

        $state0->setTransitions([
            new Transition($match0, $state0, 0),
            new Transition($match1, $state1, 0)
        ]);
        $state1->setTransitions([
            new Transition($match0, $state2, 0),
            new Transition($match1, $state0, 1)
        ]);
        $state2->setTransitions([
            new Transition($match0, $state1, 1),
            new Transition($match1, $state2, 1)
        ]);

        $machine = new Transducer([$state0, $state1, $state2]);

        $this->assertEquals(0, $machine->state()->label);
        $this->assertEquals([0], $machine->consume(0));
        $this->assertEquals([0, 1], $machine->consume(11));
        $this->assertEquals([0, 1, 0], $machine->consume(110));
        $this->assertEquals([0, 0, 1, 1], $machine->consume(1001));
        $this->assertEquals([0, 1, 0, 0], $machine->consume(1100));
        $this->assertEquals([0, 1, 0, 1], $machine->consume(1111));
        $this->assertEquals([0, 0, 1, 1, 0], $machine->consume(10010));
    }
}