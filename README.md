# php7-machines
Implementation of a finite state machine in PHP7.

## Usage

A turnstile abstraction:

    use Machines\State;
    use Machines\StateMachine;
    use Machines\Transition;

    $Locked = new State('Locked');
    $Unlocked = new State('Unlocked');

    $Locked->setTransitions([
        new Transition(new MatchAcceptor('coin'), $Unlocked)
    ]);
    $Unlocked->setTransitions([
        new Transition(new MatchAcceptor('push'), $Locked)
    ]);

    $machine = new StateMachine([$Locked, $Unlocked]);

    $machine->input('coin');

    $machine->input('push');
