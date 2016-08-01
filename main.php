<?php
session_start();

//echo '<pre>';

//var_dump($req_dump);


//Read the app credentials from the credentials.json file
$credentials = file_get_contents("credentials.json");
$json_a = json_decode($credentials, true);
$appToken = $json_a['App Token'];
$oauthConsumerSecret = $json_a['OAuth Consumer Secret'];
$oauthConsumerKey = $json_a['OAuth Consumer Key'];
if($appToken=='xxxxx'||$oauthConsumerKey=='xxxxx'||$oauthConsumerSecret=='xxxxx'){
	echo 'you need to fill in credentials.json with your own app\'s values';
	exit();
}

function main(){

	global $oauth;
	global $api_url;
	//this code is executed after the sucessful OAuth interaction
	$oauth->fetch($api_url."/v3/company/".$_SESSION['realmId']."/query?query=".urlencode("select * from Customer"),Array(),OAUTH_HTTP_METHOD_GET,Array('Accept'=>'application/json'));
	//var_dump($oauth->getLastResponse());
	$json = json_decode($oauth->getLastResponse());
	echo '<pre>';
	var_dump($json);
}



$req_url = 'https://oauth.intuit.com/oauth/v1/get_request_token';
$authurl = 'https://appcenter.intuit.com/Connect/Begin';
$acc_url = 'https://oauth.intuit.com/oauth/v1/get_access_token';
$api_url = 'https://sandbox-quickbooks.api.intuit.com';
$callback_url = 'http://localhost:14080/';
$conskey = $oauthConsumerKey;
$conssec = $oauthConsumerSecret;
// https://oauth.intuit.com/oauth/v1/get_request_token
if(!isset($_GET['oauth_token']) && $_SESSION['state']==1) $_SESSION['state'] = 0;
try {
	$oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
	$oauth->enableDebug();
	if(!isset($_GET['oauth_token']) && !$_SESSION['state']) {
		$request_token_info = $oauth->getRequestToken($req_url,$callback_url);
		$_SESSION['secret'] = $request_token_info['oauth_token_secret'];
		$_SESSION['state'] = 1;
		header('Location: '.$authurl.'?oauth_token='.$request_token_info['oauth_token']);
		exit;
	} else if($_SESSION['state']==1) {
		$oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
		$_SESSION['realmId'] = $_GET['realmId'];
		$access_token_info = $oauth->getAccessToken($acc_url);
		$_SESSION['state'] = 2;
		$_SESSION['token'] = $access_token_info['oauth_token'];
		$_SESSION['secret'] = $access_token_info['oauth_token_secret'];
	} 
	$oauth->setToken($_SESSION['token'],$_SESSION['secret']);
	main();
} catch(OAuthException $E) {
	echo '<pre>';
	print_r($E);
}
