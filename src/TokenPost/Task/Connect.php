<?php

namespace TokenPost\Task;
use TokenPost\Interfaces\Task;
use TokenPost\CaPHPistrano;

class Connect implements Task
{
    public static function appendTask()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_connect', function() use(&$caphp) {

            $caphp->writeln("Server: <comment>[{$caphp->opts['ssh']['server']}]</comment>");
            if(!isset($caphp->opts['ssh']['password'])) {
                $caphp->write('Password: ');
                $caphp->opts['ssh']['password'] = $caphp->askPassword();
            }

            $caphp->connect($caphp->opts['ssh']['server'], $caphp->opts['ssh']['user'], $caphp->opts['ssh']['password']);
            $caphp->writeln('<fg=green;options=bold>Connected xD.</fg=green;options=bold>' . PHP_EOL);
        });
    }
}

