<?php
	
	function callService($environment, $endpoint, $request) {
		$request_string = json_encode($request); 
		$service = curl_init($environment.$endpoint);                                                                     
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
	

	// setup data.
	$environment = "http://studio.dashboard.mdl.io/api/Report/";	
	
	$callback = $_GET['callback'];
	$ClientIP = "";
	$AccountID = $_GET["account"];
	$UserEmail = $_GET["email"];
	$UserPass = $_GET["pwd"];
	//$ProgramID = 43;
	
	// for test
	//$AccountID = 1;
	//$UserEmail = "qa@mdl.io";
	//$UserPass = "1234";
	//$ProgramID = 152;

	$CampaignID = 0;
	$OutboundID = 0;
	$InboundID = 0;	
	
//	$rn = "2729_leftpanel";
	$rn = "home"; 
	$fd = date("m/d/Y",strtotime("-3 Months") );
	$td = date("m/d/Y", time() );

	/*
	$fd = "1/15/2016"; 
	$td = "4/14/2016"; */ 
	$seed = "false";
	$menu = "True";
	$pb = "true";
	$filter = "true";
	$si = "0"; 
	$st = "0"; 	

	// Get token
	$tokenReq = array(
        "AccountID"=> $AccountID,
        "ClientIP"=> $ClientIP,
		"UserEmail"=> $UserEmail,
        "UserPass"=> $UserPass

    );
	
	$tokenRes = callService($environment,"Authenticate", $tokenReq);
	$token = $tokenRes->{"Token"};
	//echo "token = $token<br>";


	// get scope id	
	$url = $environment."GetProgramDwID?ai=$AccountID&si=$ProgramID";
	//$url = $environment."GetCampaignDwID?ai=$AccountID&si=$CampaignID";
	//$url = $environment."GetOutboundDwID?ai=$AccountID&si=$OutboundID";
	//$url = $environment."GetInboundDwID?ai=$AccountID&si=$InboundID";

	$result = file_get_contents($url);
	$Scope =  json_decode($result, true);
	$ScopeID = $Scope['ID'];
	//echo "<br>ID = ".$Scope['ID']."<br>";
	//echo "<br>Error = ".$Scope['Error']."<br>";
	
	

	// run report			

	//$url = "https://studio.dashboard.mdl.io/api/Report/GetReportView?rn=$rn&ai=$AccountID&si=$ScopeID&fd=$fd&td=$td&seed=$seed&menu=$menu&token=$token&pb=$pb&filter=$filter";
	$url = "https://studio.dashboard.mdl.io//api/Report/GetReportView?rn=2729_leftpanel&si=$si&ai=$AccountID&st=$st&fd=$fd&td=$td&seed=$seed&menu=$menu&token=$token&pb=$pb&filter=$filter";

	//echo "<br>url = ".$url."<br>";	
	echo $callback, '(', json_encode( array('success'=>true, 'url'=>$url)), ')';
