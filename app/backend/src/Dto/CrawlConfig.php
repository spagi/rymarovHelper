<?php

declare(strict_types=1);

namespace App\Dto;

class CrawlConfig
{
    public string $startUrl;
    public int $limit;
    public ?string $domain = null;
}
