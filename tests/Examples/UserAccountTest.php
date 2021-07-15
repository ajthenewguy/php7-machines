<?php

namespace Tests\Examples;

use Machines\Acceptors\LambdaAcceptor;
use Machines\Acceptors\MatchAcceptor;
use Machines\State;
use Machines\StateMachine;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class UserAccountTest extends TestCase
{

    public $user;


    public function setUp(): void
    {
        $this->user = $this->makeUser([]);
    }

    public function testRegistrationFlow()
    {
        $email = 'testuser@website.com';
        $password = '123';

        $this->assertTrue($this->user->is('ANONYMOUS'));

        $code = $this->user->register([
            'email' => $email,
            'password' => $password
        ]);

        $this->assertTrue($this->user->is('REGISTERED'));

        $this->user->verify($code);

        $this->assertTrue($this->user->is('VERIFIED'));
    }

    public function testCloseAccount()
    {
        $user = $this->makeUser(['email' => 'test@example.com'], 'VERIFIED');

        $this->assertTrue($user->is('VERIFIED'));

        $user->close();

        $this->assertTrue($user->is('CLOSED'));
    }

    public function testInviteUser()
    {
        $email = 'test@example.com';
        $user = $this->user::create(['email' => $email]);
        $code = $user->invite();

        $this->assertTrue($user->is('INVITED'));

        $user->verify($code);

        $this->assertTrue($user->is('VERIFIED'));
    }

    public function makeUser(array $data = [], string $initialState = null)
    {
        $user = new class ($data, $initialState)
        {
            private $data;

            private $stateMachine;

            public function __construct(array $data, string $initialState = null)
            {
                $this->data = $data;

                $states = [
                    'ANONYMOUS' => new State('ANONYMOUS'),
                    'REGISTERED' => new State('REGISTERED'),
                    'INVITED' => new State('INVITED'),
                    'VERIFIED' => new State('VERIFIED'),
                    'CLOSED' => new State('CLOSED', true)
                ];

                $registerTransition = new Transition(new MatchAcceptor('REGISTER'), $states['REGISTERED']);
                $inviteTransition   = new Transition(new MatchAcceptor('INVITE'), $states['INVITED']);
                $verifyTransition   = new Transition(new LambdaAcceptor(function ($code) {
                    return $code === $this->data['code'];
                }), $states['VERIFIED']);
                $closeTransition    = new Transition(new MatchAcceptor('CLOSE'), $states['CLOSED']);

                $states['ANONYMOUS']->setTransitions([
                    $registerTransition,
                    $inviteTransition
                ]);
                $states['REGISTERED']->addTransition($verifyTransition);
                $states['INVITED']->addTransition($verifyTransition);
                $states['VERIFIED']->addTransition($closeTransition);

                $this->stateMachine = new StateMachine(array_values($states), $initialState ? $states[$initialState] : null);
            }

            public static function create(array $data, string $initialState = null): self
            {
                return new self($data, $initialState);
            }

            public function register(array $data)
            {
                $data['code'] = static::code();
                $this->data = $data;
                $this->stateMachine->input('REGISTER');
                return $data['code'];
            }

            public function invite()
            {
                $this->data['code'] = static::code();
                $this->stateMachine->input('INVITE');

                return $this->data['code'];
            }

            public function is(string $state)
            {
                return $this->stateMachine->is($state);
            }

            public function verify(string $code)
            {
                $this->stateMachine->input($code);
            }

            public function close()
            {
                $this->stateMachine->input('CLOSE');
            }

            protected static function code()
            {
                return md5(rand(00000, 99999));
            }
        };

        return $user;
    }
}