# Ordinary Command

## Getting Started
Install using composer.
```shell
composer require ordinary/command
```

## Examples
### Directly in executable file
Create the class.
```php
class MyCommand extends \Ordinary\Command\Command
{
    public function run() : int {
        // do something
        return 0; // int 0-255 exit status
    }
    
    public function showHelp() : void {
        fwrite($this->stdout(), <<<HELP
        My Help Content
        HELP);
    }
    
    public function beforeExecute() : ?int {
        // do stuff before help screen and before run
        return null; // return null to continue or int error status for early exit
    }
}
```
Make the executable file with execute permissions.
```php
#!/usr/bin/env php
<?php
## my-cmd.php
use Ordinary\Command\CommandExec;
use Ordinary\Command\Command;

$exec = new CommandExec();

/** @var Command $cmd */
$cmd = new MyCommand();

exit($exec->execute(
    $cmd->withArgs($_SERVER['argv'])
        ->withStreams(STDIN, STDOUT, STDERR)
));
```

Run the file
```shell
./my-cmd.php
```