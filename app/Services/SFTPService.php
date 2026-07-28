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

        $result = $this->sftp->put(
            $remoteFile,
            $localFile,
            SFTP::SOURCE_LOCAL_FILE
        );

        if (!$result) {
            // phpseclib usually fails silently here if the remote
            // parent directory doesn't exist yet - surface that.
            throw new Exception(
                'SFTP upload failed. SFTP error: ' . $this->sftp->getLastSFTPError()
            );
        }

        // Confirm the file is actually there and has content, not just
        // that put() returned true.
        $stat = $this->sftp->stat($remoteFile);
        $remoteSize = $stat['size'] ?? false;
        if ($remoteSize === false || $remoteSize === 0) {
            throw new Exception("SFTP upload verification failed: {$remoteFile} is missing or empty on the server.");
        }

        return $result;
    }
}