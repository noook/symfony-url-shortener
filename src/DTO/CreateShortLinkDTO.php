<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateShortLinkDTO
{
    public function __construct(
        #[Assert\Length(min: 4)]
        public string $shortCode,

        #[Assert\Url]
        public string $url,

        #[Assert\Positive]
        public ?int $maxVisits = null,

        #[Assert\DateTime]
        public \DateTimeImmutable $availableAt,

        // ...
    ) {}
}