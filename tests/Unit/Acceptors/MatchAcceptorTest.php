<?php

namespace Tests\Unit\Acceptors;

use Machines\Acceptors\MatchAcceptor;
use PHPUnit\Framework\TestCase;

class MatchAcceptorTest extends TestCase
{

    public function testAccepting()
    {
        $acceptor = new MatchAcceptor('');

        $this->assertFalse($acceptor->accepting());
    }

    public function testEvaluate()
    {
        $acceptor = new MatchAcceptor(0);

        $this->assertFalse($acceptor->accepting());

        $out = $acceptor->evaluate();

        $this->assertInstanceOf(MatchAcceptor::class, $out);
    }

    public function testInput()
    {
        $acceptor = new MatchAcceptor(1);

        $this->assertFalse($acceptor->accepting());

        $accepting = $acceptor->input(1);

        $this->assertTrue($accepting);
    }
}
