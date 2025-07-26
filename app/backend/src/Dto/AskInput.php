<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

final class AskInput
{
    #[Groups(['ask:write'])]
    #[Assert\NotBlank(message: 'Pole "question" nesmí být prázdné.')]
    #[Assert\Length(min: 5, minMessage: 'Otázka musí mít alespoň {{ limit }} znaků.')]
    #[SerializedName('question')]
    public ?string $question = null;
}
