<?php

declare(strict_types=1);

namespace App\Controller\Api;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Dto\AskInput;
use App\Dto\AskOutput;


#[ApiResource]
#[Post(
    uriTemplate: '/ask',
    controller: AskController::class,
    input: AskInput::class,
    output: AskOutput::class,
    normalizationContext: ['groups' => ['ask:read']],
    denormalizationContext: ['groups' => ['ask:write']]
)]
final class AskApiResource
{
}
