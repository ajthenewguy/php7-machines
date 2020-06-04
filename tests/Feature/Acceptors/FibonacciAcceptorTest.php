<?php

namespace Tests\Feature\Acceptors;

use Machines\Acceptors\FibonacciAcceptor;
use Machines\Exceptions\InvalidInputException;
use PHPUnit\Framework\TestCase;

class FibonacciAcceptorTest extends TestCase {

    public function testAcceptor()
    {
        $acceptor = new FibonacciAcceptor();

        $this->assertFalse($acceptor->accepting());

        $acceptor->input(0);
        $acceptor->input(1);
        $acceptor->input(1);
        $acceptor->input(2);
        $acceptor->input(3);
        $acceptor->input(5);
        $acceptor->input(8);
        $acceptor->input(13);

        $this->assertTrue($acceptor->accepting());
    }

    public function testAcceptorFails()
    {
        $acceptor = new FibonacciAcceptor();

        $this->expectException(InvalidInputException::class);

        $acceptor->input(0);
        $acceptor->input(1);
        $acceptor->input(2);
    }
}
