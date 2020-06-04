<?php

namespace Tests\Unit\Acceptors;

use Machines\Acceptors\SequenceAcceptor;
use PHPUnit\Framework\TestCase;

class SequenceAcceptorTest extends TestCase {

    public function testAccepting()
    {
        $acceptor = new SequenceAcceptor([]);

        $this->assertFalse($acceptor->accepting());
    }

    public function testEvaluate()
    {
        $acceptor = new SequenceAcceptor([]);

        $this->assertFalse($acceptor->accepting());

        $out = $acceptor->evaluate();

        $this->assertInstanceOf(SequenceAcceptor::class, $out);
    }

    public function testInput()
    {
        $acceptor = new SequenceAcceptor([1]);

        $this->assertFalse($acceptor->accepting());

        $accepting = $acceptor->input(1);

        $this->assertTrue($accepting);
    }
}