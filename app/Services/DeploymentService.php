<?php

namespace App\Services;

class DeploymentService
{
    protected SSHService $ssh;
    protected SFTPService $sftp;

    public function __construct()
    {
        $this->ssh = new SSHService();
        $this->sftp = new SFTPService();
    }

    public function createWebsiteFolder($domain)
    {
        $path = "/www/wwwroot/$domain";

        $this->ssh->execute("
            mkdir -p $path &&
            chmod -R 755 $path
        ");

        return $path;
    }

    public function uploadZip($domain, $localZip)
    {
        $remoteZip = "/www/wwwroot/$domain/$domain.zip";

        $this->sftp->upload(
            $localZip,
            $remoteZip
        );

        return $remoteZip;
    }

    public function extractZip($domain)
    {
        $remotePath = "/www/wwwroot/$domain";

        $command = "
            cd $remotePath &&
            unzip -o $domain.zip &&
            rm -f $domain.zip &&
            find . -type d -exec chmod 755 {} \; &&
            find . -type f -exec chmod 644 {} \;
        ";

        return $this->ssh->execute($command);
    }
}