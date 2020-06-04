<?php

namespace Tests\Feature\Acceptors;

use Machines\Acceptors\SequenceAcceptor;
use Machines\Exceptions\InvalidInputException;
use PHPUnit\Framework\TestCase;

class SequenceAcceptorTest extends TestCase {

    public function testAcceptor()
    {
        $acceptor = new SequenceAcceptor([1, 2, 3]);

        $this->assertFalse($acceptor->accepting());
        $this->assertFalse($acceptor->input(1));
        $this->assertFalse($acceptor->input(2));
        $this->assertTrue($acceptor->input(3));

        $acceptor = new SequenceAcceptor(str_split('test'));

        $this->assertFalse($acceptor->accepting());
        $this->assertFalse($acceptor->input('t'));
        $this->assertFalse($acceptor->input('e'));
        $this->assertFalse($acceptor->input('s'));
        $this->assertTrue($acceptor->input('t'));

        $acceptor = new SequenceAcceptor(['the', 'quick', 'brown', 'fox', 'jumps', 'over', 'the', 'lazy', 'dog']);

        $this->assertFalse($acceptor->accepting());
        $this->assertFalse($acceptor->input('the'));
        $this->assertFalse($acceptor->input('quick'));
        $this->assertFalse($acceptor->input('brown'));
        $this->assertFalse($acceptor->input('fox'));
        $this->assertFalse($acceptor->input('jumps'));
        $this->assertFalse($acceptor->input('over'));
        $this->assertFalse($acceptor->input('the'));
        $this->assertFalse($acceptor->input('lazy'));
        $this->assertTrue($acceptor->input('dog'));
    }

    public function testAcceptorFails()
    {
        $acceptor = new SequenceAcceptor([1, 1, 2, 3, 5, 8, 13, 21]);

        $this->expectException(InvalidInputException::class);

        $acceptor->input(1);
        $acceptor->input(2);
    }
}