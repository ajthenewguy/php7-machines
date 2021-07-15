<?php

namespace Tests\Unit\Acceptors;

use Machines\Acceptors\MatchAcceptor;
use Machines\State;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase {

    public function testCreate()
    {
        $ReadyState = State::create('Ready');
        $DeliveredState = State::create('Delivered', true);

        $this->assertEquals('Ready', $ReadyState->label);
        $this->assertFalse($ReadyState->final);
        $this->assertEquals('Delivered', $DeliveredState->label);
        $this->assertTrue($DeliveredState->final);
    }

    public function testAddTransition()
    {
        $matchSelect = new MatchAcceptor('select');
        $matchPayment = new MatchAcceptor('payment');
        $ReadyState = State::create('Ready');
        $CheckoutState = State::create('Checkout');
        $ClosedState = State::create('Closed');

        $TransitionToCheckout = new Transition($matchSelect, $CheckoutState, 4.99);
        $TransitionToClosed = new Transition($matchPayment, $ClosedState);

        $ReadyState->addTransition($TransitionToCheckout);

        $this->assertEquals([$TransitionToCheckout], $ReadyState->transitions);

        $ReadyState->addTransition($TransitionToClosed);

        $this->assertEquals([$TransitionToCheckout, $TransitionToClosed], $ReadyState->transitions);
    }

    public function testSetTransitions()
    {
        $matchSelect = new MatchAcceptor('select');
        $matchCancel = new MatchAcceptor('cancel');
        $matchPayment = new MatchAcceptor('payment');
        $ReadyState = State::create('Ready');
        $CheckoutState = State::create('Checkout');
        $ClosedState = State::create('Closed');

        $TransitionToCheckout = new Transition($matchSelect, $CheckoutState, 4.99);
        $TransitionToClosed = new Transition($matchPayment, $ClosedState);
        $TransitionToReady = new Transition($matchCancel, $ReadyState);

        $erroneousTransitions = [$TransitionToCheckout];
        $correctTransitions = [$TransitionToReady, $TransitionToClosed];

        $CheckoutState->setTransitions($erroneousTransitions);

        $this->assertEquals($erroneousTransitions, $CheckoutState->transitions);

        $CheckoutState->setTransitions($correctTransitions);

        $this->assertEquals($correctTransitions, $CheckoutState->transitions);
    }
}