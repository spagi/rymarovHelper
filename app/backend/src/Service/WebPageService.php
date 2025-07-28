<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\WebPageData;
use App\Entity\WebPage;
use App\Factory\WebPageFactory;
use App\Repository\WebPageRepository;
use Doctrine\ORM\EntityManagerInterface;

class WebPageService
{
    public function __construct(
        private readonly WebPageRepository $repository,
        private readonly WebPageFactory $factory,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function createOrUpdate(WebPageData $data): WebPage
    {
        $webPage = $this->repository->findOneBy(['url' => $data->url]) ?? $this->factory->create();

        $this->factory->fill($webPage, $data);

        $this->entityManager->persist($webPage);

        return $webPage;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
