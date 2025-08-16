<?php

namespace App\Services;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class UpworkProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public function __construct(array $options = [], array $collaborators = [])
    {
        $defaults = [
            'clientId' => config('services.upwork.client_id'),
            'clientSecret' => config('services.upwork.client_secret'),
            'redirectUri' => url(config('services.upwork.redirect_uri')),
        ];
        $options = array_merge($defaults, $options);

        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return 'https://www.upwork.com/ab/account-security/oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://www.upwork.com/api/v3/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://www.upwork.com/api/v3/profile/me';
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new \Exception($data['error_description']);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return $response;
    }

    /**
     * Create an authenticated GraphQL request
     */
    public function createGraphQLRequest(array $query, string $accessToken): \Psr\Http\Message\RequestInterface
    {
        return $this->getAuthenticatedRequest(
            'POST',
            'https://api.upwork.com/graphql',
            $accessToken,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($query)
            ]
        );
    }
}
