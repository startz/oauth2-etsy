# Etsy Provider for OAuth 2.0 Client
[![GitHub tag](https://img.shields.io/github/tag/startz/oauth2-etsy.svg)](https://github.com/startz/oauth2-etsy/blob/main/tags)
[![GitHub license](https://img.shields.io/github/license/startz/oauth2-etsy.svg)](https://github.com/startz/oauth2-etsy/blob/main/LICENSE)
[![build](https://github.com/startz/oauth2-etsy/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/startz/oauth2-etsy/actions/workflows/php.yml)
[![codecov](https://codecov.io/gh/startz/oauth2-etsy/branch/master/graph/badge.svg)](https://codecov.io/gh/startz/oauth2-etsy)

This package provides Etsy OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Requirements

The following versions of PHP are supported.

* PHP 7.3
* PHP 7.4
* PHP 8.0

## Installation

To install, use composer:

```
composer require startz/oauth2-etsy
```

Or add the following to your `composer.json` file.

```json
{
    "require": {
        "startz/oauth2-etsy": "^0.0.1"
    }
}
```

## Usage

Usage is the same as The League's OAuth client, using `\StartZ\OAuth2\Client\Provider\Etsy` as the provider.

Please refer to your [Etsy Developer Account](https://www.etsy.com/developers/your-apps) for the necessary settings.
### Authorization Code Flow

```php
<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

$provider = new Startz\OAuth2\Client\Provider\Etsy([
    'clientId'     => '{etsy-apikey-keystring}',
    'clientSecret' => '{etsy-apikey-shared-secret}',
    'redirectUri'  => 'https://example.com/callback-url',
    'responseType' => 'code',
]);

if ( ! isset($_GET['code'])) 
{
    // If we don't have an authorization code then get one
    $authUrl                 = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);
        printf('Hello %s!', $user->getName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Error...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Testing

Unit Tests
``` bash
$ ./vendor/bin/phpunit
```

Code Sniff
```bash
$ ./vendor/bin/phpcs src --standard=psr2 -sp
```