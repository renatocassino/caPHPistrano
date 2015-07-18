<?php

/**
 * This package will make the deploy
 * 
 * @license MIT
 * @version 0.1
 * @link    https://github.com/tokenpost/caPHPistrano
 * @package 0.1
 * @author  Renato Cassino <renatocassino@gmail.com>
 */

namespace TokenPost;

use Deployer\Tool;
use Deployer\Tool\Context;
use Deployer\Utils\Local;
use Deployer\Remote\RemoteFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use TokenPost\Deploy\Methods;

/**
 * This class will be something
 */
class CaPHPistrano extends Methods
{
    private static $_caphpistrano = null;
    private static $_deployer = null;

    /**
     * Options read by ini file
     * 
     * @var Array $opts
     */
    public $opts = array();

    /**
     * Tasks to execute after another task
     * 
     * @var Array $_after
     */
    private $_after = array();

    /**
     * Tasks to execute before another task
     * 
     * @var Array $_before
     */
    private $_before = array();

    /**
     * DateFormat to change the release
     * 
     * @var string $release
     */
    public $release;

    /**
     * Environment - Ex: dev, prod, test
     *
     * @var string $environment
     */
    public $environment;

    /**
     * Args in the command line.
     * Format: ./caphp [filename.php] command --var1=value -var2=value2 var3=value3 value4
     * @var array $args
     */
    public $args;

    /**
     *  Design pattern Singleton
     */
    private function __construct() { }

    /**
     *  Design pattern Singleton
     *  Options: $caphp->opts
     */
    public static function getInstance()
    {

        if(is_null(self::$_caphpistrano))
        {
            global $argv;
            echo PHP_EOL;

            /**
             * Deployer
             */
            self::$_deployer = new Tool(
                new Application('Deployer', '0.4.2'),
                new ArgvInput(array_slice($argv,0,2)),
                new ConsoleOutput(),
                new Local(),
                new RemoteFactory()
            );

            /**
             * SingleTon Design Pattern
             */
            self::$_caphpistrano = new self;
            self::$_caphpistrano->defineArgs(array_slice($argv, 2));
            self::$_caphpistrano->release = date('YmdHis');
            self::$_caphpistrano->defineEnvironment();
            self::$_caphpistrano->defineTasks();
        }

        // Return this class
        return self::$_caphpistrano;
    }

    /**
     *  Define the args to tasks
     */
    public function defineArgs(Array $args)
    {
        /**
         * @example ./caphp [filename] deploy:setup --environment=production
         */
        foreach($args as $arg)
        {
            if(preg_match('/(-){0,2}([a-zA-Z0-9_]+)=(.+)/',$arg,$myarg))
                $this->args[$myarg[2]] = $myarg[3];
            else
                $this->args[] = $arg;
        }
    }

    /**
     * Method to define the current environment
     *
     * @example ./caphp [filename] deploy:setup --environment=production
     */
    public function defineEnvironment()
    {
        // Calling a default.ini
        if(file_exists('config/deploy/default.ini'))
            $this->opts = parse_ini_file('config/deploy/default.ini', true);

        if(isset($this->args['environment'])) {
            if(file_exists('config/deploy/environments/' . $this->args['environment'] . '.ini')) {
                $this->opts = array_replace_recursive($this->opts, parse_ini_file('config/deploy/environments/' . $this->args['environment'] . '.ini',true));
            }
            else {
                throw new \Exception('You must pass the correct environment ini file.');
            }
        }
    }

    /**
     * Method to add a task after another task
     * 
     * @param string $task
     * @param array|string $methods
     */
    public function after($task, $methods)
    {
        if(is_array($methods))
            $this->_after[$task] = $methods;
        else
            $this->_after[$task] = [$methods];
    }

    /**
     * Method to add a task before another task
     * 
     * @param string $task
     * @param array $methods
     * @param string $methods
     */
    public function before($task, $methods)
    {
        if(is_array($methods))
            $this->_before[$task] = $methods;
        else
            $this->_before[$task] = [$methods];
    }

    /**
     * 
     * @param string $task
     * @param array $methods
     * @return array
     */
    private function getMethods($task, array $methods)
    {
    	if(isset($this->_after[$task]))
    		$methods = array_merge($methods, $this->_after[$task]);

    	if(isset($this->_before[$task]))
    	    $methods = array_merge($this->_before[$task], $methods);

    	return $methods;
    }

    /**
     * Ask for a password with mask
     *
     * @return string
     */
    public function askPassword()
    {
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        $this->writeln("\n");

        return $password;
    }

    /**
     * To ask a question for the user
     *
     * @param string $question
     * @param mixed $default
     * @return string
     */
    public function ask($question, $default = null)
    {
        $this->write('<question>' . $question . ' </question>');
        if(!is_null($default))
            $this->write(" [{$default}]");

        $this->write(': ');
        $value = trim(fgets(STDIN));

        return $value == '' ? $default : $value; 
    }

    /**
     * If method does not exists, try a method from deployer
     * 
     * @param mixed $method
     * @param array $values
     */
    public function __call($method,$values) {
        if(method_exists(self::$_deployer,$method)) {
            call_user_func_array(array(&self::$_deployer,$method),$values);
        }
    }
}
