<?php

header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
date_default_timezone_set('America/Chicago');
require_once('setup.php');

$callback = $_GET['callback'];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$firstName = $_GET["firstName"];
$lastName = $_GET["lastName"];
$emailData = $_GET["emailData"];
$phone = $_GET["phone"];
$address = $_GET["address"];
$city = $_GET["city"];
$state = $_GET["state"];
$zip = $_GET["zip"];
$account = $_GET["account"];
$mode = $_GET["mode"];


if ($mode) {

} else {
	$mode = $_POST["mode"];
}


$isFound = 1;



if ($account) {
	$SelectedAccountID = $account;
} else {
	$SelectedAccountID = 228;
}

$importName = $_GET["importName"];


function echoCallbackString($callback, $mpArray, $outBoundArray, $file){
	
		echo $callback, '(',
        json_encode( array(
            'success'   => true,
			'mps'=>$mpArray,
			'outBoundArray'=>$outBoundArray,
			'importFile'=>$file
        )), ')';
			
}


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







	
	
$authRequest = array
(
	"SelectedAccountID" => $SelectedAccountID,
	"Email" => $email, 
	"Password" => $pwd, 
	//"PartnerGuid" => "BrandspringAPIUser",
	//"PartnerPassword" => "91a4618da8f44aaeac1b0026e5210118"
	"PartnerGuid" => "CampaignLauncherAPIUser", 
	"PartnerPassword" => "4e98af380d523688c0504e98af3="
);

$authResponse = callService("userservice/Authenticate", $authRequest);

$userTicket = $authResponse->{"Credentials"}->{"Ticket"};

$ErrorCode = $authResponse->{"Result"}->{"ErrorCode"};
if ($ErrorCode == "") {	

	$addContact  = array // The new Contact array; I grabbed this from Dustin's email
	(
		"Credentials" => array
		(
			"Ticket" => $userTicket       
		), 
		"KeyValueList" => array(
			array("Key" => "firstName", "Value" => $firstName),
			array("Key" => "lastName", "Value" => $lastName),
			array("Key" => "email", "Value" => $emailData),
			array("Key" => "phone", "Value" => $phone),
			array("Key" => "address1", "Value" => $address),
			array("Key" => "city", "Value" => $city),
			array("Key" => "state", "Value" => $state),
			array("Key" => "zip", "Value" => $zip)
		)
	);

	//print_r(json_encode($addContact)); // Take a look at our array

	if (mode == 'add') {
		$endpoint = "contactservice/CreateContact";
	} else {
		$endpoint = "contactservice/UpdateContact";
	}

	$newContact = callService($endpoint, $addContact); // Add our new Contact; remember to handle the case if the Contact exists 
	$ErrorCode = $newContact->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {			
		$isFound = 2;
	} else {
		$errorMessage = "CreateContact ERROR : ".$newContact->{"Result"}->{"ExceptionMessage"};
	}

} else {
	$errorMessage = "Authenticate ERROR : ".$authResponse->{"Result"}->{"ErrorMessage"};
}




if( $isFound == 1) {
    echo $callback, '(', json_encode( array('success'=>false, 'message'=>$errorMessage)), ')';
    exit;
} else if( $isFound == 2 ) {
	echoCallbackString( $callback, $mpArray, $outBoundArray, $importFile);
}


?> 