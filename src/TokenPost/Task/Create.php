<?php

namespace TokenPost\Task;
use TokenPost\Interfaces\Task;
use TokenPost\CaPHPistrano;

class Create implements Task
{
    public static function appendTask()
    {
        $caphp = CaPHPistrano::getInstance();
        $caphp->task('create:environment','Create new environment',function() use($caphp) {
            if(isset($caphp->args['name']))
                $newEnvironment = $caphp->args['name'];
            else
                $newEnvironment = $caphp->ask('Write the name of your new environment.', 'test');

            \copy(dirname(dirname(__DIR__)) . '/development.ini', 'config/deploy/environments/' . $newEnvironment. '.ini');
            $caphp->writeln(PHP_EOL . '<info>Create successful!</info> New file is <comment>config/deploy/envinronments/' . $newEnvironment . '.ini</comment>. ' . PHP_EOL);
        });
    }
}
