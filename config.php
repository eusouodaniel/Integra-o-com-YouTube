<?php
	$oauthClientID = 'clientIdAqui';
	$oauthClientSecret = 'secretIdAqui';
	$baseURL = 'baseUrlAqui';
	$redirectURL = 'redirectUrlAqui';

	define('OAUTH_CLIENT_ID',$oauthClientID);
	define('OAUTH_CLIENT_SECRET',$oauthClientSecret);
	define('REDIRECT_URL',$redirectURL);
	define('BASE_URL',$baseURL);

	require_once 'google-api-php/autoload.php'; 
	require_once 'google-api-php/Client.php';
	require_once 'google-api-php/Service/YouTube.php';

	session_start();

	$client = new Google_Client();
	$client->setClientId(OAUTH_CLIENT_ID);
	$client->setClientSecret(OAUTH_CLIENT_SECRET);
	$client->setScopes('https://www.googleapis.com/auth/youtube');
	$client->setRedirectUri(REDIRECT_URL);

	$youtube = new Google_Service_YouTube($client);  
?>