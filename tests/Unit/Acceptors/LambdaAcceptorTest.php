<?php

namespace Tests\Unit\Acceptors;

use Machines\Acceptors\LambdaAcceptor;
use Machines\Exceptions\InvalidInputException;
use PHPUnit\Framework\TestCase;

class LambdaAcceptorTest extends TestCase {

    public function testAccepting()
    {
        $acceptor = new LambdaAcceptor(function () {});

        $this->assertFalse($acceptor->accepting());
    }

    public function testEvaluate()
    {
        $acceptor = new LambdaAcceptor(function () {
            return false;
        });

        $this->assertFalse($acceptor->accepting());

        $out = $acceptor->evaluate();

        $this->assertInstanceOf(LambdaAcceptor::class, $out);
    }

    public function testInput()
    {
        $acceptor = new LambdaAcceptor(function ($input) {
            return $input === 1;
        });

        $this->assertFalse($acceptor->accepting());

        $accepting = $acceptor->input(1);

        $this->assertTrue($accepting);
    }
}