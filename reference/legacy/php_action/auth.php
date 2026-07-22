<?php

    require 'vendor/autoload.php';
    
    session_start();

    use TheNetworg\OAuth2\Client\Provider\Azure;

    $provider = new Azure([
        'clientId'     => 'YOUR_CLIENT_ID',
        'clientSecret' => 'YOUR_CLIENT_SECRET',
        'redirectUri'  => 'https://your-app-url.com/callback',
        'tenant'       => 'YOUR_TENANT_ID'
    ]);

    if (!isset($_GET['code'])) {
        // Step 1. Get authorization code
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authUrl);
        exit;
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        // State is invalid, possible CSRF attack
        unset($_SESSION['oauth2state']);
        exit('Invalid state');
    } else {
        // Step 2. Get access token using the authorization code
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Use the access token to make API requests
            $resourceOwner = $provider->getResourceOwner($accessToken);
            $user = $resourceOwner->toArray();

            // Save the user information in the session or database
            $_SESSION['user'] = $user;
            echo 'Hello, ' . $user['displayName'];

        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details
            exit($e->getMessage());
        }
    }

?>