<?php

namespace Tests\Feature\Acceptors;

use Machines\Acceptors\LambdaAcceptor;
use Machines\Exceptions\InvalidInputException;
use PHPUnit\Framework\TestCase;

class LambdaAcceptorTest extends TestCase {

    public function testAcceptor()
    {
        $acceptor = new LambdaAcceptor(function ($input) {
            return $input % 2 === 0;
        });

        $this->assertFalse($acceptor->accepting());
        $this->assertTrue($acceptor->input(0));
        $this->assertTrue($acceptor->input(2));

        $acceptor = new LambdaAcceptor(function ($input) {
            return $input === 'potato';
        });

        $this->assertFalse($acceptor->accepting());
        $this->assertTrue($acceptor->input('potato'));
    }

    public function testAcceptorFails()
    {
        $acceptor = new LambdaAcceptor(function ($input) {
            return $input === 'potato';
        });

        $this->expectException(InvalidInputException::class);

        $this->assertTrue($acceptor->input('potatoes'));
    }
}