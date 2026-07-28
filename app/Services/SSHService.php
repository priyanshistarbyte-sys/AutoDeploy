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

    /**
     * Execute a remote command and THROW if it fails, instead of
     * silently returning whatever text the shell printed.
     *
     * @throws Exception with the real stderr/stdout from the server
     */
    public function execute($command)
    {
        $this->connect();

        // Defensive: strip Windows-style carriage returns. If this file
        // (or any command string built elsewhere) is ever saved with
        // CRLF line endings, the stray \r characters get sent to the
        // remote bash shell and break command parsing (e.g.
        // "bash: line 1: $'\r': command not found", "find: missing
        // argument to -exec"), even though the earlier parts of the
        // chain (like unzip) may have already run successfully.
        $command = str_replace("\r", '', $command);

        $output = $this->ssh->exec($command);
        $exitStatus = $this->ssh->getExitStatus();

        if ($exitStatus !== 0) {
            throw new Exception(
                "Remote command failed (exit code {$exitStatus}).\n" .
                "Command: {$command}\n" .
                "Output: {$output}"
            );
        }

        return $output;
    }
}