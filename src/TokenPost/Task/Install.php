<?php

namespace TokenPost\Task;
use TokenPost\Interfaces\Task;
use TokenPost\CaPHPistrano;

class Install implements Task
{
    public static function appendTask()
    {
        $caphp = CaPHPistrano::getInstance();

        /**
         *  Tasks default
        */
        $caphp->task('install', 'Installing the CaPHPistrano.', function() use(&$caphp) {

            if(file_exists('deploy.php')) {
                if($caphp->ask('Do you want to overwrite the file deploy.php?','no') == 'yes'){
                    $caphp->writeln('<info>Create</info> deploy.php');
                    \copy(dirname(dirname(__DIR__)) . '/deploy.php', 'deploy.php');
                }
            }
        
            if(!is_dir('config/deploy/environments')) {
                echo 'Run mkdir -p config/deploy/environments' . PHP_EOL;
                mkdir('config/deploy/environments',0755,true);
            }
        
            if(file_exists('config/deploy/default.ini')) {
                if($caphp->ask('Do you want to overwrite the file default.ini?','yes') == 'yes'){
                    $caphp->writeln('<info>Create</info> ./config/default.ini');
                    \copy(dirname(dirname(__DIR__)) . '/default.ini', 'config/deploy/default.ini');
                }
            } else {
                $caphp->writeln('<info>Create</info> ./config/default.ini');
                \copy(dirname(dirname(__DIR__)) . '/default.ini', 'config/deploy/default.ini');
            }
        
            if(file_exists('config/deploy/environments/development.ini')) {
                if($caphp->ask('Do you want to overwrite the file development.ini?','yes') == 'yes'){
                    $caphp->writeln('<info>Create</info> ./config/deploy/environments/development.ini');
                    \copy(dirname(dirname(__DIR__)) . '/development.ini', 'config/deploy/environments/development.ini');
                }
            } else {
                $caphp->writeln('<info>Create</info> ./config/deploy/environments/development.ini');
                \copy(dirname(dirname(__DIR__)) . '/development.ini', 'config/deploy/environments/development.ini');
            }
        
            if(file_exists('config/deploy/environments/production.ini')) {
                if($caphp->ask('Do you want to overwrite the file production.ini?','yes') == 'yes'){
                    $caphp->writeln('<info>Create</info> ./config/deploy/environments/production.ini');
                    \copy(dirname(dirname(__DIR__)) . '/production.ini', 'config/deploy/environments/production.ini');
                }
            } else {
                $caphp->writeln('<info>Create</info> ./config/deploy/environments/production.ini');
                \copy(dirname(dirname(__DIR__)) . '/production.ini', 'config/deploy/environments/production.ini');
            }
        
            echo PHP_EOL .
            './deploy.php' . PHP_EOL .
            './config/deploy' . PHP_EOL .
            "   ├─ default.ini" . PHP_EOL .
            "   └─ environments" . PHP_EOL .
            "      ├─ development.ini" . PHP_EOL .
            "      └─ production.ini" . str_repeat(PHP_EOL,3);
        });
    }
}