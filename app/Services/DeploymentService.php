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

    /**
     * Writes the standard URL rewrite rules (pretty URLs -> .php
     * pages) that every deployed site needs, and reloads Nginx so
     * they take effect immediately. Content is sent base64-encoded
     * over SSH so shell metacharacters like $ and ^ in the regex
     * rules can't be misinterpreted.
     */
    public function applyRewriteRules($domain)
    {
        $rewriteDir = '/www/server/panel/vhost/rewrite';
        $rewritePath = "{$rewriteDir}/{$domain}.conf";

        $rules = <<<'CONF'
        rewrite ^/blog/([^/]+)/?$ /blog.php?id=$1 break;
        rewrite ^/about-us/?$ /about-us.php break;
        rewrite ^/contact-us/?$ /contact-us.php break;
        rewrite ^/privacy-policy/?$ /privacy-policy.php break;
        rewrite ^/terms-of-service/?$ /terms-of-service.php break;
        CONF;

        $encoded = base64_encode($rules);

        $command = "mkdir -p {$rewriteDir} && echo '{$encoded}' | base64 -d > {$rewritePath} && nginx -t && nginx -s reload";

        return $this->ssh->execute($command);
    }
}