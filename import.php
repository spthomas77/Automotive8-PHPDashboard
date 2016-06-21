<?php

header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('setup.php');
include 'share.php';

$callback = $_GET['callback'];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$importFile = $_GET["importFile"];
$account = $_GET["account"];
$mode = $_GET["mode"];
$PartnerGuid = $_GET{'PartnerGuid'};
$PartnerPassword = $_GET{'PartnerPassword'};

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


function uploadFile() {
	if ($_FILES["file"]["error"] > 0) {
		echo "Error: " . $_FILES["file"]["error"] . "<br>";
		return "";
	} else {
		$filename = "temp/".$_FILES["file"]["name"];
		move_uploaded_file($_FILES["file"]["tmp_name"],$filename);

		return $filename;
	}

}



if ($mode == "upload") {
	$filename = uploadFile();
	echo "$filename";
	if ($filename != '') {
		$isFound = 0;
		
		$server = $_SERVER['SERVER_NAME'];          
		$path = $_SERVER['PHP_SELF'];
		$path = substr($path, 0, -10);


		$location = 'Location: http://'.$server.$path.'scheduleBlank.html?filename='.$filename;
		echo $location;
		header( $location ) ;
	}

} else if ($mode == "import") {

	$filename = uploadFile();
	
	$userTicket = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);

	
	$file = file_get_contents($importFile);
	$byteArr = str_split($file);
	foreach ($byteArr as $key=>$val) { 
		$byteArr[$key] = ord($val); 
	}
	
	$rows = file($importFile);
	$str = $rows[0];
	$arr1 = explode("\t", $str); 
	$arr2 = explode(",", $str); 
	
	
	if (sizeOf($arr1) > 1) {
		$delimiter = "\t";
		$arr = $arr1;
	} else {
		$delimiter = ",";
		$arr = $arr2;
	}
	$Mapping = "";
	
	//echo sizeOf($arr).'<br>';
	for ($i = 0; $i < sizeOf($arr); $i++) {
		$strTemp = trim($arr[$i]);
		$Mapping = $Mapping . "[" . $strTemp . "][" . $strTemp . "];";
	}
	$Mapping = substr($Mapping, 0, -1);
	$FileName = basename($importFile); 

	//echo "FileName = $FileName,Mapping = $Mapping,importName = $importName,userTicket = $userTicket<br>";
	//var_dump($byteArr);

	$ImportRequest   = array
	(
		"FileName" => $FileName,
		"Filterable" => true,
		"Mapping" => $Mapping,
		"Mode" => 1,
		"Name" => $importName,
		"NotificationEmail" => $email,
		"CSV" => $byteArr,
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
		"CsvFormat" => 1
	);
	$ImportResponse = callService("contactservice/ImportContacts", $ImportRequest);
	$ErrorCode = $ImportResponse->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {
		

		unlink($importFile);
		$isFound = 2;
	} else {	
		$errorMessage = "ImportContacts ERROR : ".$ImportResponse->{"Result"}->{"ExceptionMessage"};
	}

	
}




if( $isFound == 1) {
    echo $callback, '(', json_encode( array('success'=>false, 'message'=>$errorMessage)), ')';
    exit;
} else if( $isFound == 2 ) {
	echoCallbackString( $callback, $mpArray, $outBoundArray, $importFile);
}


?> 