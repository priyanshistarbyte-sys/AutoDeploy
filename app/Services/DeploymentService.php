<?php

namespace App\Services;

class DeploymentService
{
    protected SSHService $ssh;
    protected SFTPService $sftp;
    protected AapanelService $aapanel;

    public function __construct()
    {
        $this->ssh = new SSHService();
        $this->sftp = new SFTPService();
        $this->aapanel = new AapanelService();
    }

    public function registerSite($domain)
    {
        return $this->aapanel->addSite($domain);
    }

    public function applySsl($domain)
    {
        return $this->aapanel->applySsl($domain);
    }

    public function createWebsiteFolder($domain)
    {
        $path = "/www/wwwroot/$domain";

        $this->ssh->execute("mkdir -p $path && find $path -mindepth 1 ! -name '.user.ini' -exec chmod 755 {} \\;");

        return $path;
    }

    public function uploadZip($domain, $localZip)
    {
        if (!file_exists($localZip)) {
            throw new \Exception("Local ZIP file not found: " . $localZip);
        }

        $remoteZip = "/www/wwwroot/$domain/$domain.zip";

        $this->sftp->upload($localZip, $remoteZip);

        return $remoteZip;
    }

    public function extractZip($domain)
    {
        $remotePath = "/www/wwwroot/$domain";

        $this->ssh->execute("which unzip");

        $command = "cd $remotePath && unzip -o $domain.zip -x '.user.ini' && rm -f $domain.zip && find . -type d ! -name '.user.ini' -exec chmod 755 {} \\; && find . -type f ! -name '.user.ini' -exec chmod 644 {} \\;";

        return $this->ssh->execute($command);
    }
}