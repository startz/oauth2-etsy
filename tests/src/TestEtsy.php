<?php

namespace StartZ\OAuth2\Client\Test;

use League\OAuth2\Client\Tool\QueryBuilderTrait;
use PHPUnit\Framework\TestCase;
use Startz\OAuth2\Client\Provider\Exception\EtsyIdentityProviderException;
use Startz\OAuth2\Client\Provider\Etsy;

use Mockery as m;

class TestEtsy extends TestCase
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
        // 'l9gfJd1F1vELfLjEvQhoCYD8w7dV_QGDZCn-Hif7miM'
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
        $params = [
            'code' => 'mock_authorization_code',
            'code_verifier' => 'mock_code_verifier'
        ];

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
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token":"mock_access_token", "token_type":"bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $authParams = [
            'code' => 'mock_authorization_code',
            'code_verifier' => 'mock_code_verifier'
        ];
        $token = $this->provider->getAccessToken('authorization_code', $authParams);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $userId = rand(1000, 9999);
        $loginName = uniqid();
        $email  = uniqid();
        $firstName  = uniqid();
        $lastName  = uniqid();
        $createTimeStamp = rand(946684800, 946690000);
        $createdTimeStamp = rand($createTimeStamp, $createTimeStamp+5000);
        $gender = 'female';
        $birthMonth = rand(1, 12);
        $birthDay = rand(1, 27);
        $buyCount = rand(1, 27);
        $sellCount = rand(1, 45848);
        $imageUrl = 'http://cdn.github.fake/image.jpg';


        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')
            ->andReturn('access_token=' . $userId . '.mock_access_token&expires=3600&'
                        .'refresh_token=mock_refresh_token&otherKey={1234}');
        $postResponse->shouldReceive('getHeader')
            ->andReturn(['content-type' => 'application/x-www-form-urlencoded']);
        $postResponse->shouldReceive('getStatusCode')
            ->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('
        {
            "user_id": ' . $userId . ',
            "login_name": "' . $loginName . '",
            "primary_email": "' . $email . '",
            "first_name": "' . $firstName . '",
            "last_name": "' . $lastName . '",
            "create_timestamp": ' . $createTimeStamp . ',
            "created_timestamp": ' . $createdTimeStamp . ',
            "is_seller": true,
            "gender": "' . $gender . '",
            "birth_month": "' . $birthMonth . '",
            "birth_day": "' . $birthDay . '",
            "transaction_buy_count": ' . $buyCount . ',
            "transaction_sold_count": ' . $sellCount . ',
            "image_url_75x75": "' . $imageUrl . '"
        }
        ');

        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user  = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['user_id']);

        $this->assertEquals($loginName, $user->getLoginName());
        $this->assertEquals($loginName, $user->toArray()['login_name']);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['primary_email']);

        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($firstName, $user->toArray()['first_name']);

        $this->assertEquals($lastName, $user->getLastName());
        $this->assertEquals($lastName, $user->toArray()['last_name']);

        $this->assertEquals($createTimeStamp, $user->getCreateTimeStamp());
        $this->assertEquals($createTimeStamp, $user->toArray()['create_timestamp']);

        $this->assertEquals($createdTimeStamp, $user->getCreatedTimeStamp());
        $this->assertEquals($createdTimeStamp, $user->toArray()['created_timestamp']);

        $this->assertEquals(true, $user->getIsSeller());
        $this->assertEquals(true, $user->toArray()['is_seller']);

        $this->assertEquals($gender, $user->getGender());
        $this->assertEquals($gender, $user->toArray()['gender']);

        $this->assertEquals($birthMonth, $user->getBirthMonth());
        $this->assertEquals($birthMonth, $user->toArray()['birth_month']);

        $this->assertEquals($birthDay, $user->getBirthDay());
        $this->assertEquals($birthDay, $user->toArray()['birth_day']);

        $this->assertEquals($buyCount, $user->getTransactionBuyCount());
        $this->assertEquals($buyCount, $user->toArray()['transaction_buy_count']);

        $this->assertEquals($sellCount, $user->getTransactionSoldCount());
        $this->assertEquals($sellCount, $user->toArray()['transaction_sold_count']);

        $this->assertEquals($imageUrl, $user->getThumbnail());
        $this->assertEquals($imageUrl, $user->toArray()['image_url_75x75']);
    }

    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $this->expectException(EtsyIdentityProviderException::class);
        $status       = rand(400, 600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"message": "Validation Failed","errors": 
            [{"resource": "Issue","field": "title","code": "missing_field"}]}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    public function testExceptionThrownWhenOAuthErrorReceived()
    {
        $this->expectException(EtsyIdentityProviderException::class);
        $status       = 200;
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error": "bad_verification_code",
            "error_description": "The code passed is incorrect or expired.",
            "error_uri": "https://developer.github.com/v3/oauth/#bad-verification-code"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
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
        $this->expectError();
        $this->provider->getPKCE();
    }
}
