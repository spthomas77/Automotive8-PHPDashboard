<?php
	date_default_timezone_set('America/Chicago');
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


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

	function getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid = "CampaignLauncherAPIUser", $PartnerPassword = "4e98af380d523688c0504e98af3=") {
		$authRequest = array
		(
			"SelectedAccountID" => $SelectedAccountID,
			"Email" => $email, 
			"Password" => $pwd, 
			"PartnerGuid" => $PartnerGuid, 
			"PartnerPassword" => $PartnerPassword
		);

		$authResponse = callService("userservice/Authenticate", $authRequest);

		$userTicket = $authResponse->{"Credentials"}->{"Ticket"};

		$ErrorCode = $authResponse->{"Result"}->{"ErrorCode"};
		if ($ErrorCode == "") {	
			
		} else {
			$errorMessage = "Authenticate ERROR : ".$authResponse->{"Result"}->{"ErrorMessage"};
			$userTicket = '';
		}

		return $userTicket;
	}


	$SelectedAccountID	= '228';
	$loginEmail			= 'boonsom@mindfireinc.com';
	$loginPassword		= 'atm123';
	$PartnerGuid		= 'CampaignLauncherAPIUser';
	$PartnerPassword	= '4e98af380d523688c0504e98af3=';
	

	$userTicket = getTicket($SelectedAccountID, $loginEmail, $loginPassword, $PartnerGuid, $PartnerPassword);

	echo "userTicket : $userTicket<br>\n";
	$tmp = "16041915";

	$addContact  = array // Prepare our new Contact request
 		(
			"Credentials" => array
			(
				"Ticket" => $userTicket       
			), 
			"KeyValueList" => array( // Here you would include all the standard & custom fields articulating the new Contact's info
				array("Key" => "Email", "Value" => "test$tmp@gmail.com"),
				array("Key" => "FirstName", "Value" => "first$tmp"),
				array("Key" => "TwitterAccount", "Value" => "twitter$tmp"), // My sandbox account uses Twitter as de-deup
				array("Key" => "LastName", "Value" => "last$tmp"
				))
		);

	echo "Adding a new Contact<br>\n"; // You can remove this if echo scares Kush; not sure why he said no echo plz

	$newContact =  callService("contactservice/CreateContact", $addContact); // Add our new Contact.  
	$ErrorCode = $newContact->{"Result"}->{"ErrorCode"};
	echo "ErrorCode : $ErrorCode <br>\n";
	$ErrorMessage = $newContact->{"Result"}->{"ErrorMessage"};
	echo "ErrorMessage : $ErrorMessage <br>\n";
	$ExceptionMessage = $newContact->{"Result"}->{"ExceptionMessage"};
	echo "ExceptionMessage : $ExceptionMessage <br>\n";
	$purl=$newContact->{'Purl'}; // From what was returned by the API, let's get the Contact's newly issued PURL
	echo "purl : $purl <br>\n";
	$ContactID=$newContact->{'ContactID'}; // From what was returned by the API, let's get the Contact's newly issued PURL
	echo "ContactID : $ContactID <br>\n";
	
	if ($ErrorCode == '') {
		$FieldNames = array("purl");
		$ContactIDs = array("$ContactID");
		$ContactReq = array (
			"Credentials" => array
				(
					"Ticket" => $userTicket       
				),
				"ContactIDs" => $ContactIDs,
				"FieldNames" => $FieldNames,
				"OutputType" => 1
			);

		echo "GetContactListById<br>\n"; // You can remove this if echo scares Kush; not sure why he said no echo plz
		$ContactRes =  callService("contactservice/GetContactListById", $ContactReq); // Add our new Contact.  
		$ErrorCode = $ContactRes->{"Result"}->{"ErrorCode"};
		echo "ErrorCode : $ErrorCode <br>\n";
		$ErrorMessage = $ContactRes->{"Result"}->{"ErrorMessage"};
		echo "ErrorMessage : $ErrorMessage <br>\n";
		$ExceptionMessage = $ContactRes->{"Result"}->{"ExceptionMessage"};
		echo "ExceptionMessage : $ExceptionMessage <br>\n";
		$Contacts = $ContactRes->{"Contacts"};		
		$Contact = '';
		foreach ($Contacts as $chr) {
			$Contact .= chr($chr);
		}
		$purl = '';
		$t = time();
		$importFile = '/tmp/'.$t.'.csv';
		$FileName = basename($importFile); 
		//echo "<br>importFile : $importFile,FileName : $FileName <br>";
		file_put_contents( $importFile, $Contact);
		
		$row = 0;
		if (($handle = fopen($importFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row == 0) {
					$row++;
				} else if ($row == 1) {
					$purl = @$data[0];
					$row++;
					break;
				}
			}
		}
		fclose($handle);
		unlink($importFile);
		echo "purl :<br>\n";
		echo "$purl";
		echo "<br>\n";
	}



	/*


	// setup data.
	$environment = "http://studio.dashboard.mdl.io/api/Report/";	
	
	$callback = $_GET['callback'];
	$test = $_GET['test'];
	$ClientIP = "";
	$AccountID = $_GET["account"];
	$UserEmail = $_GET["email"];
	$UserPass = $_GET["pwd"];
	

	$CampaignID = 0;
	$OutboundID = 0;
	$InboundID = 0;
	$rn = "home";
	$fd = "01/01/2011";
	$td = "05/30/2015";
	$seed = "false";
	$menu = "true";
	$pb = "true";
	$filter = "true";
	
	// Get token
	$tokenReq = array(
        "AccountID"=> $AccountID,
        "ClientIP"=> $ClientIP,
		"UserEmail"=> $UserEmail,
        "UserPass"=> $UserPass

    );
	
	$tokenRes = callService($environment,"Authenticate", $tokenReq);
	$token = $tokenRes->{"Token"};
	
	$fd = date("m/d/Y",strtotime("-3 Months") );
	$td = date("m/d/Y", time() );

	if ($test == 't') {
		echo "fd : $fd, td : $td";
		echo "<br>\n";
	}	
	

	$json_url = "https://studio.dashboard.mdl.io/api/Report//GetAdminReportData?rn=2729_leftpanel&si=0&ai=2848&st=0&fd=$fd&td=$td&seed=false&datasourceType=redshift";
	$data = file_get_contents($json_url);
	$result = array();
	if ($test == 't') {
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}
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
				if ($test == 't') {
					echo "item : $item<br>";
				}
				
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
	//echo "<br>\n";
	*/