<?php
date_default_timezone_set('America/Los_Angeles');

session_start();

define("DOMAIN", "http://ventas.com-ext.com/");

function callService($endpoint, $request)
{
    $request_string = json_encode($request); 

    $service = curl_init('http://studio.mdl.io/REST/'.$endpoint);                                                                      
    curl_setopt($service, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($service, CURLOPT_POSTFIELDS, $request_string);                                                                  
    curl_setopt($service, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($service, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($request_string))                                                                       
    );                                                                                                                   
    $response_string = curl_exec($service);

    $response = json_decode($response_string);
    return($response);
}

if( @$_REQUEST['logout'] == '1' ) {
    
    session_unset();
    $_SESSION['isLoggedIn'] = false;
    define('IS_LOGGED_IN', false);
    
} elseif( @$_SESSION['isLoggedIn'] ) {

	define('IS_LOGGED_IN', true);
	
} elseif( @$_POST['username'] && @$_POST['password'] ) {

	
	$authRequest = array
	(
		"SelectedAccountID" => $SelectedAccountID,
		"Email" => $email, 
		"Password" => $pwd, 
		"PartnerGuid" => "CampaignLauncherAPIUser", 
		"PartnerPassword" => "4e98af380d523688c0504e98af3="
	);

	$authResponse = callService("userservice/Authenticate", $authRequest);

	$userTicket = $authResponse->{"Credentials"}->{"Ticket"};
	//echo "userTicket=$userTicket<br>";
	$ErrorCode = $authResponse->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {
		define('IS_LOGGED_IN', true);
	} else {
		$_SESSION['isLoggedIn'] = false;
		define('IS_LOGGED_IN', false);
	}
	
	
	
	
} else {

	$_SESSION['isLoggedIn'] = false;
	define('IS_LOGGED_IN', false);
	
}

