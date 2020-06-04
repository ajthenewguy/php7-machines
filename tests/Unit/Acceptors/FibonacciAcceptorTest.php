<?php

namespace Tests\Unit\Acceptors;

use Machines\Acceptors\FibonacciAcceptor;
use PHPUnit\Framework\TestCase;

class FibonacciAcceptorTest extends TestCase {

    public function testAccepting()
    {
        $acceptor = new FibonacciAcceptor();

        $this->assertFalse($acceptor->accepting());
    }

    public function testEvaluate()
    {
        $acceptor = new FibonacciAcceptor();

        $this->assertFalse($acceptor->accepting());

        $out = $acceptor->evaluate();

        $this->assertInstanceOf(FibonacciAcceptor::class, $out);
    }

    public function testInput()
    {
        $acceptor = new FibonacciAcceptor();

        $this->assertFalse($acceptor->accepting());

        $accepting = $acceptor->input(0);

        $this->assertTrue($accepting);
    }
}