<?php

namespace TokenPost;

class SSH2
{
    private $_connection;

    public $username;
    public $password;
    public $port;
    public $fingerprint;
    public $host;
    public $isConnected = false;

    private $ssh_server_fp = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    public function __construct($host = '', $username = '', $port = '', $password = '') {
        $this->host     = $host;
        $this->username = $username;
        $this->port     = $port;
        $this->password = $password;
    }

    public function connect()
    {
        if (!($this->_connection = ssh2_connect($this->host, $this->port))) {
            throw new \Exception('Cannot connect to server');
        }

        $this->fingerprint = ssh2_fingerprint($this->_connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
        if (strcmp($this->ssh_server_fp, $this->fingerprint) === 0) {
            throw new \Exception('Unable to verify server identity!');
        }

        $this->isConnected = ssh2_auth_password($this->_connection, $this->username, $this->password);
        if(!$this->isConnected)
            throw new \Exception('Autentication rejected by server');
        return true;
    }

    public function exec($command, $returnMessage = true)
    {
        if (!($stream = ssh2_exec($this->_connection, $command))) {
            throw new Exception('SSH command failed!');
        }

        if($returnMessage) {
            stream_set_blocking($stream, true);
            $data = "";
            while ($buf = fread($stream, 4096)) {
                $data .= $buf;
            }
            fclose($stream);
            return $data;
        }
    }

    public function disconnect()
    {
        $this->exec('echo "EXITING" && exit;');
        $this->connection = null;
        $this->isConnected = false;
    }

    public function __destruct() {
        $this->disconnect();
    }
}
