<?php

namespace Startz\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use Startz\OAuth2\Client\Provider\Exception\EtsyIdentityProviderException;

class Etsy extends AbstractProvider
{

    use BearerAuthorizationTrait;

    protected $baseApiUrl = 'https://api.etsy.com/v3';

    /**
     * {@inheritDoc}
     */
    public function getBaseAuthorizationUrl() : string
    {
        return 'https://www.etsy.com/oauth/connect';
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->baseApiUrl . '/public/oauth/token' . $this->buildQueryString($params);
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token) : string
    {
        // we need to get the userId from the access token
        $tokenData = explode('.', $token->getToken());
        return $this->baseApiUrl . '/application/users/' . $tokenData[0];
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultScopes() : array
    {
        return ['email_r'];
    }

    /**
     * {@inheritDoc}
     */
    protected function checkResponse(ResponseInterface $response, $data) : void
    {
        if ($response->getStatusCode() >= 400) {
            throw EtsyIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw EtsyIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        return new EtsyResourceOwner($response, $token);
    }

// DO WE NEED THIS TO SET grant_type FOR REQUESTING REFRESH TOKENS?
//    /**
//     * {@inheritDoc}
//     */
//    protected function getAuthorizationHeaders($token = null): array
//    {
//        return ['Authorization' => 'Bearer ' . $token];
//    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultHeaders() : array
    {
        return [
            'x-api-key' => $this->clientId
        ];
    }
}
