<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use Exception;

class SSHService
{
    protected ?SSH2 $ssh = null;

    /**
     * Reuses the existing connection if we already have one instead
     * of opening a brand-new SSH session (with a full login
     * handshake) on every single command. A DeploymentService
     * instance lives for one request, so this connection gets
     * reused across every ssh->execute() call in that request.
     */
    public function connect()
    {
        if ($this->ssh !== null) {
            return $this->ssh;
        }

        $this->ssh = new SSH2(env('SSH_HOST'), env('SSH_PORT'));

        if (!$this->ssh->login(
            env('SSH_USERNAME'),
            env('SSH_PASSWORD')
        )) {
            $this->ssh = null;
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

        // Defensive: strip Windows-style carriage returns.
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