<?php

namespace App\Service;

use Faker;
use App\DTO\CreateShortLinkDTO;
use App\Entity\ShortLink;
use App\Exceptions\ShortLinkConflictException;
use App\Exceptions\ShortLinkTooLongException;
use App\Repository\ShortLinkRepository;
use Doctrine\ORM\EntityManagerInterface;

class ShortLinkService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShortLinkRepository $shortLinkRepository,
    ) {}

    public function createShortLink(CreateShortLinkDTO $dto): ShortLink {
        $shortLink = new ShortLink();
        $shortLink->setUrl($dto->url);
        if ($dto->shortCode === null) {
            $shortLink->setShortCode($this->generateShortLink());
        } else {
            // In this code we'll try to throw different errors.
            if (strlen($dto->shortCode) > 10) {
                throw new ShortLinkTooLongException(); // Will throw HTTP 422 error
            }
            // Make sure it doesn't already exist, otherwise throw 409
            $oldShortUrl = $this->shortLinkRepository->findOneBy(['shortCode' => $dto->shortCode]);
            if ($oldShortUrl !== null) {
                throw new ShortLinkConflictException($dto->shortCode); // Will throw HTTP 409 error
            }
            $shortLink->setShortCode($dto->shortCode);
        }

        $shortLink
            ->setMaxVisits($dto->maxVisits)
            ->setValidOn($dto->validOn)
            ->setExpiresAt($dto->expiresAt);

        $this->em->persist($shortLink);
        $this->em->flush();

        return $shortLink;
    }

    public function generateShortLink(): string {
        $faker = Faker\Factory::create();
        $code = $faker->regexify('[A-Za-z0-9]{8}');
        // Check that code doesn't already exist
        // If it does, generate a new one
        return $code;
    }
}