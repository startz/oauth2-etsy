<?php

namespace StartZ\OAuth2\Client\Test;

use Mockery as m;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class EtsyCore extends TestCase
{

    public function testCore()
    {
        $this->assertEquals(1, 1);
    }

    protected function getStream($data = null) : StreamInterface
    {
        if (!$data) {
            $data = [
                'access_token' => 'mock_access_token',
                'token_type' => 'bearer'
            ];
            $data = json_encode($data);
        }
        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('getContents')->andReturn($data);
        return $stream;
    }

    protected function params() : array
    {
        return [
            'code' => 'mock_authorization_code',
            'code_verifier' => 'mock_code_verifier'
        ];
    }

    protected function getResponse($body, $status = 200, $contentType = ['application/json'])
    {
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn($body);
        $postResponse->shouldReceive('getHeader')->andReturn($contentType);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);
        return $postResponse;
    }

    protected function getGuzzle($return, $recieve = 'send', $times = 1)
    {
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive($recieve)
            ->times($times)
            ->andReturn($return);
        return $client;
    }

    protected function getUserData($createTimeStamp) : array
    {
        return [
        'userId' => rand(1000, 9999),
        'loginName' => uniqid(),
        'email'  => uniqid(),
        'firstName'  => uniqid(),
        'lastName' => uniqid(),
        'createTimeStamp' => rand(946684800, 946690000),
        'createdTimeStamp' => rand($createTimeStamp, $createTimeStamp+5000),
        'gender' => 'female',
        'birthMonth' => rand(1, 12),
        'birthDay' => rand(1, 27),
        'buyCount' => rand(1, 27),
        'sellCount' => rand(1, 45848),
        'imageUrl' => 'http://cdn.github.fake/image.jpg'
        ];
    }
}