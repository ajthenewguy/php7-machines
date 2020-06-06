<?php

namespace Tests\Feature\Acceptors;

use Machines\Acceptors\RegexAcceptor;
use Machines\Exceptions\InvalidInputException;
use PHPUnit\Framework\TestCase;

class RegexAcceptorTest extends TestCase
{

    public function testAcceptor()
    {
        $acceptor = new RegexAcceptor('/[0-9]/');

        $this->assertFalse($acceptor->accepting());
        $this->assertTrue($acceptor->input(0));
        $this->assertTrue($acceptor->input(2));

        $acceptor = new RegexAcceptor('/tato$/');

        $this->assertFalse($acceptor->accepting());
        $this->assertTrue($acceptor->input('potato'));
    }

    public function testAcceptorFails()
    {
        $acceptor = new RegexAcceptor('/tato$/');

        $this->expectException(InvalidInputException::class);

        $this->assertTrue($acceptor->input('potatoes'));
    }
}
