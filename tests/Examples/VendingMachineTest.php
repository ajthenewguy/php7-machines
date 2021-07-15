<?php

namespace Tests\Examples;

use Machines\Acceptors\MatchAcceptor;
use Machines\State;
use Machines\StateMachine;
use Machines\Transition;
use PHPUnit\Framework\TestCase;

class VendingMachineTest extends TestCase
{

    public function testVendingMachine()
    {
        $init = new State('INIT');
        $wait = new State('WAIT');
        $nickel = new State('NICKEL');
        $dime = new State('DIME');
        $quarter = new State('QUARTER');
        $returnChange = new State('RETURN_CHANGE');
        $dispenseItem = new State('DISPENSE_ITEM');
        $exit = new State('EXIT');

        $init->setTransitions([
            new Transition(new MatchAcceptor('selection'), $wait)
        ]);
        $wait->setTransitions([
            new Transition(new MatchAcceptor('nickel'), $nickel),
            new Transition(new MatchAcceptor('dime'), $dime),
            new Transition(new MatchAcceptor('quarter'), $quarter),
            new Transition(new MatchAcceptor('return_change'), $returnChange)
        ]);
        $nickel->setTransitions([
            new Transition(new MatchAcceptor('wait'), $wait),
            new Transition(new MatchAcceptor('dispense_item'), $dispenseItem)
        ]);
        $dime->setTransitions([
            new Transition(new MatchAcceptor('wait'), $wait),
            new Transition(new MatchAcceptor('dispense_item'), $dispenseItem)
        ]);
        $quarter->setTransitions([
            new Transition(new MatchAcceptor('wait'), $wait),
            new Transition(new MatchAcceptor('dispense_item'), $dispenseItem)
        ]);
        $returnChange->setTransitions([
            new Transition(new MatchAcceptor('exit'), $exit)
        ]);
        $dispenseItem->setTransitions([
            new Transition(new MatchAcceptor('return_change'), $returnChange),
            new Transition(new MatchAcceptor('exit'), $exit)
        ]);

        $states = [
            $init,
            $wait,
            $nickel,
            $dime,
            $quarter,
            $returnChange,
            $dispenseItem,
            $exit
        ];

        $machine = new class ($states) extends StateMachine
        {
            public $items = [
                '0.35' => 'candy',
                '1.00' => 'coke',
                '0.75' => 'water'
            ];

            public $coins = [
                '0.05' => 'nickel',
                '0.10' => 'dime',
                '0.25' => 'quarter'
            ];

            public $change = [
                'nickel' => 0,
                'dime' => 0,
                'quarter' => 0,
                'penny' => 0
            ];

            public $money = 0.00;

            public $coin = null;

            public $selection = null;

            public $dispensed = null;


            public function select($selection)
            {
                $this->selection = $selection;

                $this->input('selection');

                return $this->price();
            }

            public function wait()
            {
                if ($this->money >= $this->price()) {
                    $this->input('dispense_item');
                } else {
                    $key = array_rand($this->coins);
                    $coin = $this->coins[$key];
                    $this->coin = $coin;
                    $this->input($this->coin);

                    return $coin;
                }
                return null;
            }

            public function insertCoin()
            {
                $value = floatval(array_search($this->coin, $this->coins));
                $this->addMoney($value);
                $this->coin = null;

                if ($this->money >= $this->price()) {
                    $this->input('dispense_item');
                } else {
                    $this->input('wait');
                }

                return $value;
            }

            public function dispenseItem()
            {
                $this->dispensed = $this->selection;
                $this->money = bcsub($this->money, $this->price());

                if ($this->money > 0.00) {
                    $this->input('return_change');
                } else {
                    $this->input('exit');
                }

                return $this->dispensed;
            }

            public function dispenseChange()
            {
                $i = 0;
                while ($this->money && $i < 250) {
                    if ($this->money >= 0.25) {
                        $this->subtractMoney(0.25);
                        $this->change['quarter']++;
                    } elseif ($this->money >= 0.10) {
                        $this->subtractMoney(0.10);
                        $this->change['dime']++;
                    } elseif ($this->money >= 0.05) {
                        $this->subtractMoney(0.05);
                        $this->change['nickel']++;
                    } elseif ($this->money >= 0.01) {
                        $this->subtractMoney(0.01);
                        $this->change['penny']++;
                    }
                    $i++;
                }

                $this->input('exit');

                return $this->change;
            }

            public function price()
            {
                $key = array_search($this->selection, $this->items);
                return floatval($key);
            }

            private function addMoney($amount)
            {
                $this->money += $amount;
            }

            private function subtractMoney($amount)
            {
                $this->money = bcsub($this->money, $amount);
            }
        };

        $this->assertTrue($machine->is('INIT'));

        $price = 0.00;
        $input = [
            'nickel' => 0,
            'dime' => 0,
            'quarter' => 0
        ];
        $change = [];
        $selection = null;
        $dispensed = null;

        while (!$machine->is('EXIT')) {

            if ($machine->money > 5.00) {
                print "\nERROR: too much money\n";
                $machine->input('return_change');
            }

            switch ($machine->state()->label) {
                case 'INIT':
                    $key = array_rand($machine->items);
                    $selection = $machine->items[$key];
                    $price = $machine->select($selection);

                    $this->assertTrue($price > 0.00);
                    break;
                case 'WAIT':
                    $this->assertTrue($machine->price() > 0.00);

                    if ($out = $machine->wait()) {
                        $input[$out]++;
                    }
                    break;
                case 'NICKEL':
                case 'DIME':
                case 'QUARTER':
                    $value = $machine->insertCoin();

                    $this->assertTrue($value > 0.00);
                    break;
                case 'DISPENSE_ITEM':
                    $dispensed = $machine->dispenseItem();

                    $this->assertNotNull($dispensed);
                    break;
                case 'RETURN_CHANGE':
                    $change = $machine->dispenseChange();

                    $this->assertNotNull($change);
                    break;
                default:
                    print('ERROR: invalid state ' . $machine->state()->label);
                    $machine->input('exit');
                    break;
            }
        }

        $this->assertNotNull($selection);
        $this->assertTrue($machine->money == 0.00);
        $this->assertTrue($machine->is('EXIT'));
    }
}
