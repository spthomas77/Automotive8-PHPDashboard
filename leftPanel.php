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
	$CampaignID = 0;
	$OutboundID = 0;
	$InboundID = 0;		

	//$rn = "home"; 
	$rn = "2729_leftpanel";
	$fd = date("m/d/Y",strtotime("-3 Months") );
	$td = date("m/d/Y", time() );
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
		
	$url = "https://studio.dashboard.mdl.io/api/Report//GetAdminReportData?rn=2729_leftpanel&si=0&ai=2848&st=0&fd=$fd&td=$td&seed=false&datasourceType=redshift"; 
	
//	$url= "https://studio.dashboard.mdl.io//api/Report/GetReportView?rn=2729_leftpanel&si=0&ai=2848&st=0&fd=$fd&td=$td&seed=False&menu=True&token=$token&pb=true&filter=False" ; 

//$url= "https://studio.dashboard.mdl.io//api/Report/GetReportView?rn=2729_leftpanel&si=0&ai=2848&st=0&fd=01/18/2016&td=04/18/2016&seed=False&menu=True&token=be4eef2f-91e6-47d9-a5be-5d1747494b06&pb=true&filter=False" ; 

	

	$data = file_get_contents($url);
	$result = array();
	//echo $callback, '(', json_encode( array('success'=>true, 'url'=>$url, 'result'=>$result)), ')';
	if ($data) {
			$data = substr($data,2);
			$data = substr($data,0,-2);
			$lines = explode("},{", $data);	
			foreach ($lines as $line) {			
				$category = '';
				$value = '';
				$items = explode(",", $line);	
				$piece = array();
				foreach ($items as $item) {				
					
					if ( substr( $item, 0, 8 ) === "category" ) {
						$field = $item;
						$field = substr($field,10);
						$field = substr($field,0,-1);
						$category = $field;
					}
					if ( substr( $item, 0, 5 ) === "value" ) {
						$field = $item;
						$field = substr($field,6);
						$value = $field;						
					}
					$piece['category'] = $category;
					$piece['value'] = $value;
				}
				$result[] = $piece;			
			}
		}

		echo $callback, '(', json_encode( array('success'=>true, 'result'=>$result)), ')';




			 


