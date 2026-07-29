<?php

namespace App\Services;

use phpseclib3\Net\SFTP;
use Exception;

class SFTPService
{
    protected ?SFTP $sftp = null;

    public function connect()
    {
        if ($this->sftp !== null) {
            return $this->sftp;
        }

        $this->sftp = new SFTP(env('SSH_HOST'), env('SSH_PORT'));

        if (!$this->sftp->login(
            env('SSH_USERNAME'),
            env('SSH_PASSWORD')
        )) {
            $this->sftp = null;
            throw new Exception('SFTP Login Failed');
        }

        return $this->sftp;
    }

    public function upload($localFile, $remoteFile)
    {
        $this->connect();

        $result = $this->sftp->put(
            $remoteFile,
            $localFile,
            SFTP::SOURCE_LOCAL_FILE
        );

        if (!$result) {
            throw new Exception(
                'SFTP upload failed. SFTP error: ' . $this->sftp->getLastSFTPError()
            );
        }

        $stat = $this->sftp->stat($remoteFile);
        $remoteSize = $stat['size'] ?? false;
        if ($remoteSize === false || $remoteSize === 0) {
            throw new Exception("SFTP upload verification failed: {$remoteFile} is missing or empty on the server.");
        }

        return $result;
    }
}