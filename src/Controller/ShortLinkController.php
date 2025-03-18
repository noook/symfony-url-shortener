<?php

namespace App\Controller;

use App\DTO\CreateShortLinkDTO;
use App\Service\ShortLinkService;
use App\Exceptions\ShortLinkConflictException;
use App\Exceptions\ShortLinkTooLongException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ShortLinkController extends AbstractController
{
    #[Route('/short-links', methods: ['POST'], name: 'create_short_link')]
    public function create(
        #[MapRequestPayload]
        CreateShortLinkDTO $dto,
        ShortLinkService $shortLinkService,
    ): Response
    {
        // a try block allows to catch errors that could occur during the execution of the code
        try {
            $shortLink = $shortLinkService->createShortLink($dto);
            return $this->json($shortLink);
        } catch (ShortLinkConflictException $e) {
            // Here we are catching the exception of type ShortLinkConflictException. We can handle it here.
            throw $e;
        } catch(ShortLinkTooLongException $e) {
            // Here we are catching the exception of type ShortLinkTooLongException. We can handle it here.
        } catch (\Exception $e) {
            // Here we are catching any other exception that might be thrown. We can handle it here.
            throw $e;
        }

        // Make sure to return something. It could be an empty response or an Exception.
        return $this->json([]);
    }
}
