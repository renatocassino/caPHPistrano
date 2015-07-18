<?php

namespace TokenPost\Task;
use TokenPost\Interfaces\Task;
use TokenPost\CaPHPistrano;

class Deploy implements Task
{
    public static function appendTask()
    {
        self::setup();
        self::cleanUp();
        self::checks();
        self::writable();
        self::release();
        self::shared();
    }

    private static function setup()
    {
        $caphp = CaPHPistrano::getInstance();

        /**
         * Prepares one or more servers for deployment.
         * Before you can use any of the Capistrano deployment tasks with your project,
         * you will need to make sure all of your servers have been prepared with `php deploy.php deploy:setup'.
         */
        $caphp->task('_setup', function() use (&$caphp) {
            $caphp->run(
                    "mkdir -p {$caphp->opts['ssh']['deploy_to']} {$caphp->opts['ssh']['deploy_to']}/releases {$caphp->opts['ssh']['deploy_to']}/shared {$caphp->opts['ssh']['deploy_to']}/shared/cache-copy; " .
                    "chmod g+w {$caphp->opts['ssh']['deploy_to']} {$caphp->opts['ssh']['deploy_to']}/releases {$caphp->opts['ssh']['deploy_to']}/shared {$caphp->opts['ssh']['deploy_to']}/shared/cache-copy"
            );
        });
    }

    private static function cleanUp()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_cleanup','Remove old deploys',function() use (&$caphp) {
            $caphp->run(
                    'ls -1dt '.$caphp->opts['ssh']['deploy_to'].'/releases/* | tail -n +' . ( ((int) $caphp->opts['ssh']['keep_releases']) + 1 ) . ' |  xargs rm -rf'
            );
        });
    }

    private static function checks()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_checks',function() use(&$caphp) {

            $caphp->writeln(PHP_EOL . '<comment>Verifying the root directory.</comment> (' . $caphp->opts['ssh']['deploy_to'] . ')' . PHP_EOL);
            $caphp->run("if [ ! -d {$caphp->opts['ssh']['deploy_to']} ]; then mkdir -p {$caphp->opts['ssh']['deploy_to']}; fi;");

            $caphp->writeln(PHP_EOL . '<comment>Verifying the PHP installation...</comment>' . PHP_EOL);
            $caphp->run('if ! type -p php &> /dev/null; then echo "You must install PHP5. Please, run $ apt-get install php5 and try again."; exit 1; fi;');

            if($caphp->opts['composer']['use']) {

                $caphp->writeln(PHP_EOL . '<comment>Checking composer...</comment>' . PHP_EOL);
                $caphp->run(
                    "if ! type -p {$caphp->opts['composer']['command']}; then
                        echo \"The composer ins't installed on the current folder.\";
                        if [ {$caphp->opts['composer']['installOnDeploy']} ]; then
                            cd {$caphp->opts['ssh']['deploy_to']} && curl -sS https://getcomposer.org/installer | php;
                        fi;
                    fi"
                );
            }
        });
    }

    private static function writable()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_writable', function () use (&$caphp) {
            if(isset($caphp->opts['writable']['dir']))

                foreach($caphp->opts['writable']['dir'] as $writable) {
                    $caphp->run("if [ ! -d {$caphp->opts['ssh']['deploy_to']}/current/{$writable} ]; then mkdir -p {$caphp->opts['ssh']['deploy_to']}/current/{$writable}; fi; chmod 777 -R {$caphp->opts['ssh']['deploy_to']}/current/{$writable}");
                }
        });
    }

    private static function release()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_release', 'Generating the release', function () use (&$caphp) {
            $releaseDir = "{$caphp->opts['ssh']['deploy_to']}/releases/" . $caphp->release;

            $caphp->run("mkdir -p {$releaseDir}; cd {$releaseDir} && git init && git remote add origin {$caphp->opts['git']['repository']} && git pull origin {$caphp->opts['git']['branch']}");
            $caphp->run("ln -s {$releaseDir} {$caphp->opts['ssh']['deploy_to']}/current");
        });
    }

    private static function shared()
    {
        $caphp = CaPHPistrano::getInstance();

        $caphp->task('_shared','Creating symlinks for Shared folder.',function() use(&$caphp) {

            if(isset($caphp->opts['shared']['dir'])) {
                foreach($caphp->opts['shared']['dir'] as $share) {

                    $caphp->run("
                        if [ ! -d {$caphp->opts['ssh']['deploy_to']}/shared/{$share} ]; then
                            if [ -d {$caphp->opts['ssh']['deploy_to']}/current/{$share} ]; then
                                mv {$caphp->opts['ssh']['deploy_to']}/current/{$share} {$caphp->opts['ssh']['deploy_to']}/shared/{$share};
                            else
                                mkdir -p {$caphp->opts['ssh']['deploy_to']}/shared/{$share} 
                            fi;
                        fi;

                        if [ -d {$caphp->opts['ssh']['deploy_to']}/current/{$share} ]; then
                            rm -r {$caphp->opts['ssh']['deploy_to']}/current/{$share};
                        fi;

                        ln -s {$caphp->opts['ssh']['deploy_to']}/shared/{$share} {$caphp->opts['ssh']['deploy_to']}/current/{$share}
                    ");
                }
            }

            if(isset($caphp->opts['shared']['files'])) {
                foreach($caphp->opts['shared']['files'] as $share) {

                    $caphp->run("
                        mkdir -p {$caphp->opts['ssh']['deploy_to']}/shared/" . dirname($share) . " &&
                        if [ ! -f {$caphp->opts['ssh']['deploy_to']}/shared/{$share} ]; then
                            mv {$caphp->opts['ssh']['deploy_to']}/current/{$share} {$caphp->opts['ssh']['deploy_to']}/shared/{$share};
                        fi;
                        if [ -f {$caphp->opts['ssh']['deploy_to']}/current/{$share} ]; then
                            rm {$caphp->opts['ssh']['deploy_to']}/current/{$share};
                        fi;
                        ln -s {$caphp->opts['ssh']['deploy_to']}/shared/{$share} {$caphp->opts['ssh']['deploy_to']}/current/{$share}
                    ");
                }
            }
        });
    }
}
