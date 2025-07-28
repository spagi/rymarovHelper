<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\CrawlConfig;

class CrawlConfigFactory
{
    public function create(string $startUrl, int $limit = 200): CrawlConfig
    {
        $config = new CrawlConfig();
        $config->startUrl = $startUrl;
        $config->limit = $limit;

        $host = parse_url($startUrl, PHP_URL_HOST);

        if (false === $host || null === $host) {
            throw new \InvalidArgumentException("Invalid start URL provided: {$startUrl}");
        }

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        $config->domain = $host;

        return $config;
    }
}
