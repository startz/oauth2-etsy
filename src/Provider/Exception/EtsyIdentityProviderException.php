<?php

namespace Startz\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class EtsyIdentityProviderException extends IdentityProviderException
{

    /**
     * Creates client exception from response.
     *
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     *
     * @return IdentityProviderException
     */
    public static function clientException(ResponseInterface $response, array $data): IdentityProviderException
    {
        return static::fromResponse(
            $response,
            $data['message'] ?? $response->getReasonPhrase()
        );
    }

    /**
     * Creates oauth exception from response.
     *
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     *
     * @return IdentityProviderException
     */
    public static function oauthException(ResponseInterface $response, array $data): IdentityProviderException
    {
        return static::fromResponse(
            $response,
            $data['error'] ?? $response->getReasonPhrase()
        );
    }

    /**
     * Creates identity exception from response.
     *
     * @param ResponseInterface $response
     * @param string $message
     *
     * @return IdentityProviderException
     */
    protected static function fromResponse(ResponseInterface $response, string $message = ''): IdentityProviderException
    {
        return new static($message, $response->getStatusCode(), (string)$response->getBody());
    }
}