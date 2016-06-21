<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
date_default_timezone_set('America/Chicago');
require_once('setup.php');
include 'share.php';

$data = file_get_contents('php://input');
$obj = json_decode($data);

//var_dump($obj);
$loginEmail = $obj->{'loginEmail'};
$loginPassword = $obj->{'loginPassword'};
$account = $obj->{'loginAccount'};
$mode = $obj->{'mode'};
$PartnerGuid = $obj->{'PartnerGuid'};
$PartnerPassword = $obj->{'PartnerPassword'};

$KeyValueList = array();
$atime = ''; 

foreach ($obj as $key => $value) {    
	if ( ($key == 'loginEmail') || ($key == 'loginPassword') || ($key == 'loginAccount') || ($key == 'mode') || ($key == 'PartnerGuid') || ($key == 'PartnerPassword') ) {

	} else {

		//reset label > DB fieldName
		$DbName = getResetHeader($key); 
		$key = $DbName; 

		if($key == 'Appointment_Date'){	   
			/*
			$value = str_replace("9:00 am","9:00:00",$value);
			$value = str_replace("9:30 pm","9:30:00",$value);
			$value = str_replace("10:00 am","10:00:00",$value);
			$value = str_replace("10:30 pm","10:30:00",$value);
			$value = str_replace("11:00 am","11:00:00",$value);
			$value = str_replace("11:30 pm","11:30:00",$value);
			$value = str_replace("12:00 am","12:00:00",$value);
			$value = str_replace("12:30 pm","12:30:00",$value);
			$value = str_replace("1:00 pm","13:00:00",$value);
			$value = str_replace("1:30 pm","13:30:00",$value);
			$value = str_replace("2:00 pm","14:00:00",$value);
			$value = str_replace("2:30 pm","14:30:00",$value);
			$value = str_replace("3:00 pm","15:00:00",$value);
			$value = str_replace("3:30 pm","15:30:00",$value);
			$value = str_replace("4:00 pm","16:00:00",$value);
			$value = str_replace("4:30 pm","16:30:00",$value);
			$value = str_replace("5:00 pm","17:00:00",$value);
			$value = str_replace("5:30 pm","17:30:00",$value);
			$value = str_replace("6:00 pm","18:00:00",$value);
			$value = str_replace("6:30 pm","18:30:00",$value);
			$value = str_replace("7:00 pm","19:00:00",$value);
			$value = str_replace("7:30 pm","19:30:00",$value);
			*/ 
			$adate = substr($value,0,10); 
			$atime = substr($value,11); 
			//$value = $adate; 

			$KeyValue = array("Key" => 'Appointment_Date', "Value" => $value);
			$KeyValueList[] = $KeyValue;

			$KeyValue = array("Key" => 'Appointment_Time', "Value" => $atime);
			$KeyValueList[] = $KeyValue;		
		}


		$KeyValue = array("Key" => $key, "Value" => $value);
		$KeyValueList[] = $KeyValue;
	}
}


$isFound = 1;
if ($account) {
	$SelectedAccountID = $account;
} else {
	$SelectedAccountID = 228;
}


$userTicket = getTicket($SelectedAccountID, $loginEmail, $loginPassword, $PartnerGuid, $PartnerPassword);


$addContact  = array // The new Contact array; I grabbed this from Dustin's email
(
	"Credentials" => array
	(
		"Ticket" => $userTicket       
	), 
	"KeyValueList" => $KeyValueList
);


if ($mode == 'add') {
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




if ($errorMessage == '') {
	$errorMessage = 'OK';
} else {
}

echo $errorMessage;

?> 