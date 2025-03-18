<?php

namespace App\Service;

use App\DTO\CreateUserDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $bag,
        private HttpClientInterface $httpClient,
    ) {}

    public function registerUser(CreateUserDTO $dto): User
    {
        $user = new User();
        $user
            ->setEmail($dto->email)
            ->setDisplayName($dto->displayName)
            ->setPassword($this->passwordHasher->hashPassword($user, $dto->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getGithubAuthorizationUrl(): string
    {
        $query = http_build_query([
            'client_id' => $this->bag->get('github.client_id'),
            'redirect_uri' => $this->bag->get('github.redirect_uri'),
        ]);

        return "https://github.com/login/oauth/authorize?" . $query;
    }

    public function getGithubAccessToken(string $authorizationCode): string
    {
        $tokenResponse = $this->httpClient->request('POST', 'https://github.com/login/oauth/access_token', [
            'body' => [
                'client_id' => $this->bag->get('github.client_id'),
                'client_secret' => $this->bag->get('github.client_secret'),
                'redirect_uri' => $this->bag->get('github.redirect_uri'),
                'code' => $authorizationCode,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return $tokenResponse->toArray()['access_token'];
    }

    public function getGithubUserData(string $token): array
    {
        return $this->httpClient->request('GET', 'https://api.github.com/user', [
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
        ])->toArray();
    }
}