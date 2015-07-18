<?php

require_once dirname(__FILE__) . '/../../../autoload.php';

/**
 * Classe Carro Teste
 **/
class SSHTeste extends PHPUnit_Framework_Testcase
{
    public $ssh = null;

    private function setSSH()
    {
        if(is_null($this->ssh)) {
            $this->ssh = new TokenPost\SSH2();
        }
    }

    public function testConnect()
    {
        $this->setSSH();

        $params = parse_ini_file(dirname(__FILE__) . '/params.ini');
        $this->ssh->host      = $params['host'];
        $this->ssh->username  = $params['username'];
        $this->ssh->password  = $params['password'];
        $this->ssh->port      = $params['port'];
        $this->ssh->connect();

        $this->assertTrue($this->ssh->isConnected);
        
        $this->assertEquals($this->ssh->exec('ls /var/www/',false),null);

        $this->ssh->disconnect();
        $this->assertFalse($this->ssh->isConnected);
    }
}