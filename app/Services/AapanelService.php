<?php

namespace App\Services;

use AzozzALFiras\AAPanelAPI\AaPanel;
use AzozzALFiras\AAPanelAPI\Exceptions\AaPanelException;
use Exception;

class AapanelService
{
    protected AaPanel $panel;
    protected string $phpVersion;

    public function __construct()
    {
        $this->panel = new AaPanel(
            env('BT_PANEL_KEY'),
            env('BT_PANEL_URL'),
            [
                'verify_ssl' => false,
            ]
        );

        $this->phpVersion = env('BT_PANEL_PHP_VERSION', '74');
    }

    public function addSite(string $domain): array
    {
        try {
            return $this->panel->website()->php()->create(
                $domain,
                "/www/wwwroot/{$domain}",
                $domain,
                [],
                0,
                $this->phpVersion,
                80,
                false, '', '',
                false, '', ''
            );
        } catch (AaPanelException $e) {
            if (str_contains(strtolower($e->getMessage()), 'exist')) {
                return ['msg' => $e->getMessage(), 'status' => true];
            }

            throw new Exception('aaPanel AddSite failed: ' . $e->getMessage());
        }
    }

    /**
     * Look up the numeric site ID aaPanel assigned to this domain -
     * needed for SSL and other per-site API calls.
     */
    public function getSiteId(string $domain): ?int
    {
        $result = $this->panel->website()->php()->getList(10, 1, $domain);

        $data = $result['data'] ?? [];

        foreach ($data as $site) {
            if (($site['name'] ?? null) === $domain) {
                return (int) $site['id'];
            }
        }

        return null;
    }

    /**
     * Applies a free Let's Encrypt certificate and deploys it to
     * the site in one step. Requires the domain's DNS to already
     * point at this server, since Let's Encrypt verifies ownership
     * over HTTP.
     */
    public function applySsl(string $domain): array
    {
        $siteId = $this->getSiteId($domain);

        if (!$siteId) {
            throw new Exception("Could not find aaPanel site ID for {$domain} to apply SSL.");
        }

        return $this->panel->ssl()->applyAndDeploy($domain, $siteId);
    }
}