<?php

namespace StartZ\OAuth2\Client\Test;

use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use Startz\OAuth2\Client\Provider\EtsyResourceOwner;

class TestEtsyResourceOwner extends TestCase
{
    /**
     * @var EtsyResourceOwner
     */
    protected $user;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    protected $userData = [
        'user_id' => 123456789,
        'login_name' => 'foostartz',
        'primary_email' => 'foo@bar.com',
        'first_name' => 'StartZ',
        'last_name' => 'Component',
        'create_timestamp' => 1485049529,
        'created_timestamp' => 1485049545,
        'is_seller' => true,
        'gender' => 'male',
        'birth_month' => 9,
        'birth_day' => 13,
        'transaction_buy_count' => 22,
        'transaction_sell_count' => 4957,
        'image_url_75x75' => 'https://i.etsystatic.com/iusa/8f91a4/98785122/iusa_75x75.98785122_cl4w.jpg?version=0',
    ];

    protected $tokenData = [
        'access_token' =>
            '123456789.qN6pM0a-rPyYia2Ev1H41EB1YLhkS7XmhuwCsEEHuRMyd643gyJg-2xmpJIKspOcDoj10_AAbYQ8da9R9SN_4I9fwp',
        'refresh_token' =>
            '123456789.9Dt-p5tebvQSb1pJ_sP70gCYFedcZs7BQbHDy74HQIVCW6RZQoux0RGbAVNabTgT1w4ZT-7U2_FHyl2tgqzL3PxkFI',
        'expires_in' => 86400
    ];

    protected function setUp(): void
    {
        $this->accessToken = new AccessToken($this->tokenData);
        $this->user = new EtsyResourceOwner($this->userData, $this->accessToken);
    }

    public function testGetId()
    {
        $this->assertEquals(
            123456789,
            $this->user->getId()
        );
    }

    public function testGetLoginName()
    {
        $this->assertEquals(
            'foostartz',
            $this->user->getLoginName()
        );
    }

    public function testGetEmail()
    {
        $this->assertEquals(
            'foo@bar.com',
            $this->user->getEmail()
        );
    }

    public function testGetFirstName()
    {
        $this->assertEquals(
            'StartZ',
            $this->user->getFirstName()
        );
    }

    public function testGetLastName()
    {
        $this->assertEquals(
            'Component',
            $this->user->getLastName()
        );
    }

    public function testGetThumbnail()
    {
        $this->assertEquals(
            'https://i.etsystatic.com/iusa/8f91a4/98785122/iusa_75x75.98785122_cl4w.jpg?version=0',
            $this->user->getThumbnail()
        );
    }

    public function testGetIsSeller()
    {
        $this->assertEquals(
            true,
            $this->user->getIsSeller()
        );
    }

    public function testGestGetCreateTimestamp()
    {
        $this->assertEquals(
            '1485049529',
            $this->user->getCreateTimestamp()
        );
    }

    public function testGetCreatedTimestamp()
    {
        $this->assertEquals(
            '1485049545',
            $this->user->getCreatedTimestamp()
        );
    }

    public function testGetGender()
    {
        $this->assertEquals('male', $this->user->getGender());
    }

    public function testGetBirthMonth()
    {
        $this->assertEquals(
            9,
            $this->user->getBirthMonth()
        );
    }

    public function testGetBirthDay()
    {
        $this->assertEquals(
            13,
            $this->user->getBirthDay()
        );
    }

    public function testGetTransactionBuyCount()
    {
        $this->assertEquals(
            22,
            $this->user->getTransactionBuyCount()
        );
    }

    public function getTransactionSoldCount()
    {
        $this->assertEquals(
            4957,
            $this->user->getTransactionSoldCount()
        );
    }

    public function testForNullKeys()
    {
        $this->assertNull($this->user->getBio());
    }

    public function testToArray()
    {
        $data = $this->user->toArray();
        $this->assertEquals($this->userData, $data);
    }
}
