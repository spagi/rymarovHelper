<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\Groups;

final class AskOutput
{
    #[Groups(['ask:read'])]
    public ?string $answer = null;
}
