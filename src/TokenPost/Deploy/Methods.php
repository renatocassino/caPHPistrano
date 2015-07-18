<?php

/**
 * This package will make the deploy
 * 
 * @license MIT
 * @version v0.3
 * @link    https://github.com/tokenpost/caPHPistrano
 * @author  Renato Cassino <renatocassino@gmail.com>
 */

namespace TokenPost\Deploy;

use Deployer\Tool;
use TokenPost\CaPHPistrano;

/**
 * Class with deploy methods
 */
class Methods
{
    /**
     * 
     */
    public function defineTasks()
    {
        $caphp = CaPHPistrano::getInstance();
        $dir = dirname(__FILE__) . '/../Task';
        
        foreach(glob($dir . '/*.php') as $f) {
            $method = '\\TokenPost\\Task\\' . basename($f,'.php');
            $method::appendTask();
        }

        $caphp->task('deploy:setup','Checking the deploy.', ['_connect','_setup']);
        $caphp->task('deploy:cleanup','Remove Old releases', ['_connect','_cleanup']);
        $caphp->task('deploy:check','Checking the deploy.', ['_connect','_checks']);
        $caphp->task('deploy','Checking the deploy.', ['_connect', '_setup', '_release', '_composer', '_writable', '_shared']);

        return;

        $this->task('_git:update','Git clone the project.', function() use (&$opts) {
            $this->run("cd {$opts['ssh']['deploy_to']}/current/" . CaPHPistrano::$release . ' && git fetch --all && git reset --hard HEAD && git clean -f -d && git pull origin master');
        });

        $this->task('_composer:install','Installing composer',function() use (&$opts) {
            if($opts['composer']['use'])
                $this->run("curl -sS https://getcomposer.org/installer | php -- --install-dir={$opts['composer']['dir']} --filename={$opts['composer']['filename']};");
        });

        $this->task('deploy:update_code','Checking the deploy.', ['_connect', '_git:update','_composer']);
    }
}
