<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
date_default_timezone_set('America/Chicago');
include 'share.php';

$callback = $_GET['callback'];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$account = $_GET["account"];
$test = $_GET["test"];

	$success = false;
	$result = '';
	$request = array
	(			
		"Email" => $email
	);

	if ($test == 't') {
		echo "request : <br>";
		var_dump($request);
		echo "<br>";
	}

	$Response = callService("userservice/RequestForgotPassword", $request);
	if ($Response) {

		if ($test == 't') {
			echo "Response : <br>";
			var_dump($Response);
			echo "<br>";
		}
		
		$ErrorCode = $Response->{"Result"}->{"ErrorCode"};
		if ($ErrorCode == "") {
			// Success
			$result = "Success";
			$success = true;
		} else {
			// Fail
			$result = "Fail";

		}
	} else {
		$result = "Fail Response is null";
	}


	echo $callback, '(', json_encode( array('success'=>false, 'result'=>$result, 'email'=>$email)), ')';




?> 