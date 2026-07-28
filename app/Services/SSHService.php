<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use Exception;

class SSHService
{
    protected SSH2 $ssh;

    public function connect()
    {
        $this->ssh = new SSH2(env('SSH_HOST'), env('SSH_PORT'));

        if (!$this->ssh->login(
            env('SSH_USERNAME'),
            env('SSH_PASSWORD')
        )) {
            throw new Exception('SSH Login Failed.');
        }

        return $this->ssh;
    }

    public function execute($command)
    {
        $this->connect();

        return $this->ssh->exec($command);
    }
}