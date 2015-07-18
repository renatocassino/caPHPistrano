<?php

namespace TokenPost\Task;
use TokenPost\Interfaces\Task;
use TokenPost\CaPHPistrano;

class Composer implements Task
{
    public static function appendTask()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_composer',function() use (&$caphp) {
            if($caphp->opts['composer']['use'])
                $caphp->run("cd {$caphp->opts['ssh']['deploy_to']}/current && {$caphp->opts['composer']['command']} self-update && {$caphp->opts['composer']['command']} update --no-dev");
        });
    }
}
