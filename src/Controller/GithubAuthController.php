<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubAuthController extends AbstractController
{
    #[Route('/auth/github', name: 'github_authorize', methods: ['GET'])]
    public function authorize(
        // This service is used to make HTTP requests to Github.
        HttpClientInterface $httpClient,
        // This service will allow us to check if a user already exists in our database.
        UserRepository $userRepository,
        // This service will allow us to create a custom JWT.
        JWTTokenManagerInterface $jwtManager,
        // This parameter is used to get the query parameter "code" from the request URL. It is nullable because
        // the code parameter is only present if the authentication is successful
        #[MapQueryParameter("code")] ?string $authorizationCode,
    ): Response
    {
        // Follow the following documentation to achieve an OAuth authorization flow
        // https://docs.github.com/en/apps/oauth-apps/building-oauth-apps/authorizing-oauth-apps

        // This endpoint handles both the redirect to Github Authorization URL AND the callback (code exchange).

        // If the request URL's query parameters does not container a query parameter "code", it means the user is not yet authorized.
        if (!$authorizationCode) {
            // The authorization URL requires at least the following query parameters:
            $query = http_build_query([
                // Client ID is an id that allows Github to know from which application the request is coming from.
                'client_id' => $this->getParameter('github.client_id'),
                // Redirect URI is the URL that Github will redirect the user to after the user has authorized the application.
                'redirect_uri' => $this->getParameter('github.redirect_uri'),
                // Other parameters can be added as needed, refer to the documentation.
            ]);
            // Append the query parameters to the authorization URL
            $authorizeUrl = "https://github.com/login/oauth/authorize?" . $query;
    
            // Redirect the user to Github's authorization URL
            return $this->redirect($authorizeUrl);
        }

        // If the request URL's query parameters contains a query parameter "code", it means the user has authorized the application.

        // Exchange the authorization code for an access token
        // An authorization code can only be used once.
        $tokenResponse = $httpClient->request('POST', 'https://github.com/login/oauth/access_token', [
            'body' => [
                'client_id' => $this->getParameter('github.client_id'),
                'client_secret' => $this->getParameter('github.client_secret'),
                'redirect_uri' => $this->getParameter('github.redirect_uri'),
                'code' => $authorizationCode,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        // https://symfony.com/doc/current/http_client.html#processing-responses
        // Use ->toArray() or ->getContent() to get the response content. ->toArray() is recommended as it tries to parse the response content as JSON.
        // ['access_token'] means we access the 'access_token' key in the response content.
        $token = $tokenResponse->toArray()['access_token'];
        
        // Github's access tokens are not JWTs. We need to request the user's data using the access token.
        $userData = $httpClient->request('GET', 'https://api.github.com/user', [
            'headers' => [
                // The access token is used as a bearer token in the Authorization header.
                'Authorization' => "Bearer $token",
            ],
        ])->toArray();

        // Make sure that the user exists in the database.
        $user = $userRepository->findOneBy(['email' => $userData['email']]);

        if (!$user) {
            throw new UnauthorizedHttpException('User does not exist.');
            // Maybe we could create a user with no password if it doesn't exist ?
        }

        // Create a custom JWT with the user's data
        $jwt = $jwtManager->createFromPayload($user, [
            'avatar_url' => $userData['avatar_url'],
            'company' => $userData['company'],
            'location' => $userData['location'],
        ]);

        return $this->json(['token' => $jwt]);
    }
}