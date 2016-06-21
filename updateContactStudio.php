<?php
//echo 'START<br>';
//require_once('/var/www/vhosts/pages.bhgssl.com/httpdocs/mycapitalnow/config.php');
//echo 'require config.php<br>';
date_default_timezone_set('America/Los_Angeles');


$environment = "https://studio.mdl.io/"; 
$SelectedAccountID = '3029'; //accountid
$email = 'kdutta@mindfireinc.com'; // studio login
$pwd = 'MindFire2012'; // studio password
$PartnerGuid = 'Ventas Strategies'; //studio PartnerGuid
$PartnerPassword = '846a4340e6b4e'; //studio PartnerPassword

$authRequest = array
(
	"SelectedAccountID" => $SelectedAccountID,
	"Email" => $email, 
	"Password" => $pwd, 
	"PartnerGuid" => $PartnerGuid, 
	"PartnerPassword" => $PartnerPassword
);
//echo 'set authRequest';

$authResponse = callService("userservice/Authenticate", $authRequest);

//$userTicket = "b726efda0e284112b7af73380bfc29de";
$userTicket = $authResponse->{"Credentials"}->{"Ticket"};



$ErrorCode = '';
$ErrorCode = $authResponse->{"Result"}->{"ErrorCode"};
//echo 'call Authenticate error-code['.$ErrorCode.'] , user-ticket['.$userTicket.']<br>';
if ($ErrorCode == "") {	
		
} else {
	$errorMessage = "Authenticate ERROR : ".$authResponse->{"Result"}->{"ErrorMessage"};
	$userTicket = '';
}

// Get Params
$savefield = @$_GET['savefield'];
$savevalue = @$_GET['savevalue'];
$callback = $_GET['callback'];
//$frompage = $_GET['frompage'];
//echo 'get savefield ['.$savefield.']<br>';

$keyvaluelist = getKeyValueList($savefield,$savevalue,$frompage);
//echo json_encode($keyvaluelist).'<br>';
//exit();
$purl = '';

$addContact  = array // The new Contact array; I grabbed this from Dustin's email
 		(
			"Credentials" => array
			(
				"Ticket" => $userTicket
			), 
			"KeyValueList" => $keyvaluelist
		);

$updateContact = callService("contactservice/UpdateContact", $addContact);
$ErrorCode = $updateContact->{"Result"}->{"ErrorCode"};
//echo $ErrorCode.'<br>';
if ($ErrorCode == "") {
	//echo 'Update contact result:'.json_encode($updateContact).'<br>';
	$purl='';
} else {

	$errorMessage = $updateContact->{"Result"}->{"ExceptionMessage"};
	//echo 'Update contact error: $errorMessage<br>';
	$subject = "Stadium Nissan cannot save contact list to Studio";
	$saveTime = date("Y-m-d H:i:s");
	mail('mpradeep@mindfiremail.info', $subject, $subject." at ".$saveTime."\n\n\npurl=".$purl."\n\n\n<pre>".array_values($addContact)."</pre>\n\n\n".$errorMessage);

	echo $callback, '(', json_encode( array('success'=>false, 'PURL'=>'')), ')';
	exit();
}

//echo json_encode('PURL:'.$purl);
echo $callback, '(', json_encode( array('success'=>true, 'PURL'=>$purl)), ')';

function getKeyValueList($_savefield,$_savevalue) {
	$Filter = '';
	$savefield_arr = explode( '|', $_savefield );
	$savevalue_arr = explode( '|', $_savevalue );
	$field_cnt = 0;
	$rows = array();
	//echo json_encode($savefield_arr).'<br>';
	foreach ($savefield_arr as $savefield) {
		//echo $searchfield.'<br>';
		$savevalue = $savevalue_arr[$field_cnt];
		if ($savevalue == 'now') {
			$savevalue = date("m/d/Y h:m:s");
		}
		$row = array("Key" => $savefield, "Value" => $savevalue);
		$rows[] = $row;
		$field_cnt++;
	}
	
	return $rows;
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

?>
