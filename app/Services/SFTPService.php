<?php

namespace App\Services;

use phpseclib3\Net\SFTP;
use Exception;

class SFTPService
{
    protected $sftp;

    public function connect()
    {
        $this->sftp = new SFTP(env('SSH_HOST'), env('SSH_PORT'));

        if (!$this->sftp->login(
            env('SSH_USERNAME'),
            env('SSH_PASSWORD')
        )) {
            throw new Exception('SFTP Login Failed');
        }

        return $this->sftp;
    }

    public function upload($localFile, $remoteFile)
    {
        $this->connect();

        return $this->sftp->put(
            $remoteFile,
            $localFile,
            SFTP::SOURCE_LOCAL_FILE
        );
    }
}