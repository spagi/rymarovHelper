<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\WebPageData;

class WebPageDataFactory
{
    public function create(): WebPageData
    {
        return new WebPageData();
    }
}
