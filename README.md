# php7-machines
Implementation of a finite state machine in PHP7.

## Usage

A turnstile abstraction:

    use Machines\StateMachine;

    $Locked = new State('Locked');
    $Unlocked = new State('Unlocked');
    $coinAcceptor = new LambdaAcceptor(function ($input) {
        return $input === 'coin';
    });

    $toLocked = new Transition('PUSH', $Unlocked, $Locked);
    $toUnlocked = new Transition('UNLOCK', $Locked, $Unlocked, $coinAcceptor);

    $machine = new StateMachine([$toLocked, $toUnlocked], $Locked);

    $machine->dispatch('UNLOCK', 'coin');

    $machine->dispatch('PUSH');
