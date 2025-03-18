<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class GithubAuthController extends AbstractController
{
    #[Route('/auth/github', name: 'github_authorize', methods: ['GET'])]
    public function authorize(
        AuthService $authService,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtManager,
        #[MapQueryParameter("code")] ?string $authorizationCode,
    ): Response
    {
        if (!$authorizationCode) {
            $authorizeUrl = $authService->getGithubAuthorizationUrl();
            return $this->redirect($authorizeUrl);
        }

        $token = $authService->getGithubAccessToken($authorizationCode);
        $userData = $authService->getGithubUserData($token);
        $user = $userRepository->findOneBy(['email' => $userData['email']]);

        if (!$user) {
            throw new UnauthorizedHttpException('User does not exist.');
            // Maybe we could create a user with no password if it doesn't exist ?
        }

        $jwt = $jwtManager->createFromPayload($user, [
            'avatar_url' => $userData['avatar_url'],
            'company' => $userData['company'],
            'location' => $userData['location'],
        ]);

        return $this->json(['token' => $jwt]);
    }
}