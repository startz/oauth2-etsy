<?php

namespace StartZ\OAuth2\Client\Test;

use http\Exception\UnexpectedValueException;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Startz\OAuth2\Client\Provider\Exception\EtsyIdentityProviderException;
use Startz\OAuth2\Client\Provider\Etsy;

use Mockery as m;

class TestEtsy extends EtsyCore
{
    use QueryBuilderTrait;

    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Etsy([
           'clientId'     => 'mock_client_id',
           'clientSecret' => 'mock_secret',
           'redirectUri'  => 'none',
           'responseType' => 'token',
        ]);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/connect', $uri['path']);
    }

    public function testGetAutorizationUrlWithParams()
    {
        $url = $this->provider->getAuthorizationUrl([
            'code_challenge' => $this->provider->getPKCE('prechallenge'),
            'code_challenge_method' => 'S256',
        ]);
        $uri = parse_url($url);
        $queryArray = explode('&', $uri['query']);
        foreach ($queryArray as $queryItem) {
            $query = explode('=', $queryItem);
            if ($query[0] == 'code_challenge') {
                $this->assertEquals('l9gfJd1F1vELfLjEvQhoCYD8w7dV_QGDZCn-Hif7miM', $query[1]);
            }
            if ($query[0] == 'code_challenge_method') {
                $this->assertEquals('S256', $query[1]);
            }
        }
    }

    public function testGetBaseAccessTokenUrlPath()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/v3/public/oauth/token', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('openapi.etsy.com', $uri['host']);
    }

    public function testGetBaseAccessTokenUrlParams()
    {
        $params = $this->params();

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $queryArray = explode('&', $uri['query']);
        foreach ($queryArray as $queryItem) {
            $query = explode('=', $queryItem);
            if ($query[0] == 'code') {
                $this->assertEquals('mock_authorization_code', $query[1]);
            }
            if ($query[0] == 'code_verifier') {
                $this->assertEquals('mock_code_verifier', $query[1]);
            }
        }
    }

    public function testGetAccessToken()
    {
        $this->expectException(\UnexpectedValueException::class);
        // is this test even useful?
        $stream = $this->getStream();
        $response = $this->getResponse($stream, 200);

        $client = $this->getGuzzle($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', $this->params());

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $this->expectException(\UnexpectedValueException::class);
        $createTimeStamp = time();
        $userData = $this->getUserData($createTimeStamp);
        $streamResp = $this->getStream(json_encode($userData));
        $userResponse = $this->getResponse($streamResp);
        $client = $this->getGuzzle($userResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', $this->params());
        $this->provider->getResourceOwner($token);
    }

    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $this->expectException(\TypeError::class);

        $postResponse = $this->getResponse('{"message": "Validation Failed","errors": 
            [{"resource": "Issue","field": "title","code": "missing_field"}]}', rand(400, 600));

        $client = $this->getGuzzle($postResponse);

        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenOAuthErrorReceived()
    {
        $this->expectException(\UnexpectedValueException::class);
        $stream = $this->getStream('{"error": "bad_verification_code",
            "error_description": "The code passed is incorrect or expired.",
            "error_uri": "https://developer.github.com/v3/oauth/#bad-verification-code"}');
        $postResponse = $this->getResponse($stream);
        $client = $this->getGuzzle($postResponse);
        $this->provider->setHttpClient($client);
        $this->provider->getAccessToken('authorization_code', $this->params());
    }

    public function testGetPreChallenge()
    {
        $this->assertIsString($this->provider->getPreChallenge());
    }

    public function testGetPkce()
    {
        $this->assertEquals('l9gfJd1F1vELfLjEvQhoCYD8w7dV_QGDZCn-Hif7miM', $this->provider->getPKCE('prechallenge'));
    }

    public function testGetPkceHasError()
    {
        $this->expectException(\ArgumentCountError::class);
        $this->provider->getPKCE();
    }
}