<?php

declare(strict_types=1);

namespace App\Controller\Api;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\AskInput;
use App\Dto\AskOutput;
use App\State\AskProcessor;

#[ApiResource]
#[Post(
    uriTemplate: '/ask',
    normalizationContext: ['groups' => ['ask:read']],
    denormalizationContext: ['groups' => ['ask:write']],
    input: AskInput::class,
    output: AskOutput::class,
    processor: AskProcessor::class
)]
final class AskApiResource
{
}
