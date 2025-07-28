<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\WebPageData;
use App\Entity\WebPage;

class WebPageFactory
{
    public function create(): WebPage
    {
        return new WebPage();
    }

    public function fill(WebPage $webPage, WebPageData $data): WebPage
    {
        $webPage->setUrl($data->url);
        $webPage->setTitle($data->title);
        $webPage->setContent($data->content);
        $webPage->setCrawledAt(new \DateTimeImmutable());

        return $webPage;
    }
}
