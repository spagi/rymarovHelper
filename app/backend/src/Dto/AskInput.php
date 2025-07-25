<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

final class AskInput
{
    #[Groups(['ask:write'])]
    #[Assert\NotBlank]
    #[SerializedName('question')]
    public ?string $question = null;
}
