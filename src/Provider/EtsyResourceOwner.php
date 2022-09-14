<?php

namespace Startz\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class EtsyResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    public function __construct(array $response, AccessToken $accessToken)
    {
        $this->response = $response;
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        $tokenData = explode('.', $this->accessToken->getToken());
        return $tokenData[0];
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->response;
    }

    public function getLoginName()
    {
        return $this->getValueByKey($this->response, 'login_name');
    }

    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'primary_email');
    }

    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'first_name');
    }

    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'last_name');
    }

    public function getThumbnail()
    {
        return $this->getValueByKey($this->response, 'image_url_75x75');
    }

    public function getIsSeller()
    {
        return $this->getValueByKey($this->response, 'is_seller');
    }

    public function getCreateTimestamp()
    {
        return $this->getValueByKey($this->response, 'create_timestamp');
    }

    public function getCreatedTimestamp()
    {
        return $this->getValueByKey($this->response, 'created_timestamp');
    }


    public function getBio()
    {
        return $this->getValueByKey($this->response, 'bio');
    }

    public function getGender()
    {
        return $this->getValueByKey($this->response, 'gender');
    }

    public function getBirthMonth()
    {
        return $this->getValueByKey($this->response, 'birth_month');
    }

    public function getBirthDay()
    {
        return $this->getValueByKey($this->response, 'birth_day');
    }

    public function getTransactionBuyCount()
    {
        return $this->getValueByKey($this->response, 'transaction_buy_count');
    }

    public function getTransactionSoldCount()
    {
        return $this->getValueByKey($this->response, 'transaction_sold_count');
    }
}
