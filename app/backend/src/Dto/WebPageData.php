<?php

declare(strict_types=1);

namespace App\Dto;

final class WebPageData
{
    public string $url;
    public ?string $title;
    public ?string $content;
}
