<?php
	require_once 'config.php';
	$client->revokeToken();
	session_destroy();
	header("Location:index.php"); 
	exit;
?>