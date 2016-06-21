<?php
date_default_timezone_set('America/Los_Angeles');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('share.php');

$callback = $_GET["callback"];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$account = $_GET["account"];

$programID = $_GET["programID"];
$LaunchDate = $_GET["LaunchDate"];
$LaunchDate1 = $_GET["LaunchDate1"];
$ContactList = $_GET["ContactList"];
$ProgramName = $_GET["ProgramName"];

$ContactRadio = $_GET["ContactRadio"];
$ContactRadio1 = $_GET["ContactRadio1"];
$listData = $_GET["listData"];
$listData1 = $_GET["listData1"];
$emailData = $_GET["emailData"];
$emailData1 = $_GET["emailData1"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];
$domain = $_GET["DomainName"];
$subdomain = $_GET["subdomain"];
$sharename = $_GET["sharename"];
$sharedescription = $_GET["sharedescription"];
$sharelink = $_GET["sharelink"];
$sharepictureurl = $_GET["sharepictureurl"];
$schedulevisiturl = $_GET["schedulevisiturl"];
$appId = $_GET["appId"];
$DealerDisplayName = $_GET["DealerDisplayName"];
$DealerAddressLine1 = $_GET["DealerAddressLine1"];
$DealerAddressLine2 = $_GET["DealerAddressLine2"];
$SalesPhone = $_GET["SalesPhone"];
$ServicePhone = $_GET["ServicePhone"];
$DealerHomePage = $_GET["DealerHomePage"];
$dealerlogo = $_GET["dealerlogo"];
$dealerbanner = $_GET["dealerbanner"];
$mode = $_GET["mode"];
$testID = $_GET["testID"];
$url = $domain;

if ($subdomain == '') {
	
} else {
	$url = $domain.'/'.$subdomain;
}

if ($mode == 'preview') {	
	$ProgramName = $ProgramName."_TEST".$testID;
	$url = $domain.'/'.$subdomain."_TEST".$testID;
	$subdomain = $subdomain."_TEST".$testID;
}


$t = date("mdY-His",time());


$xmlSaveFile = getxmlFile($programID);

	
$t = date("mdY-His",time());
$xmlFile = getcwd().'/temp/'.$t.'.maml';

copy($xmlSaveFile, $xmlFile);
	
$errorMessage = '';



$userTicket = getTicket($account, $email, $pwd, $PartnerGuid, $PartnerPassword);

		


// change program name;
$xmlDoc = new DOMDocument();
$xmlDoc->load( $xmlFile );



$max = 14;
if ($max > 0) {
	$ContentVariable = $xmlDoc->getElementsByTagName( "ContentVariable" );
	foreach( $ContentVariable as $ContentVariable )
	{
		$ContentVariablesName = $ContentVariable->getAttribute('Name');
		$ContentVariablesNameLower = strtolower($ContentVariablesName);

		for($i = 0; $i < $max; ++$i) {
			//echo "$i , ContentVariablesNameLower = $ContentVariablesNameLower<br>";
			if ($ContentVariablesNameLower == 'sharename') {
				$ContentVariable->nodeValue = $sharename;
			} else if ($ContentVariablesNameLower == 'sharedescription') {
				$ContentVariable->nodeValue = $sharedescription;
			} else if ($ContentVariablesNameLower == 'sharelink') {
				$ContentVariable->nodeValue = $sharelink;
			} else if ($ContentVariablesNameLower == 'sharepictureurl') {
				$ContentVariable->nodeValue = $sharepictureurl;
			} else if ($ContentVariablesNameLower == 'schedulevisiturl') {
				$ContentVariable->nodeValue = $schedulevisiturl;
			} else if ($ContentVariablesNameLower == 'appid') {
				$ContentVariable->nodeValue = $appId;
			} else if ($ContentVariablesNameLower == 'dealerdisplayname') {
				$ContentVariable->nodeValue = $DealerDisplayName;
			} else if ($ContentVariablesNameLower == 'dealeraddressline1') {
				$ContentVariable->nodeValue = $DealerAddressLine1;
			} else if ($ContentVariablesNameLower == 'dealeraddressline2') {
				$ContentVariable->nodeValue = $DealerAddressLine2;
			} else if ($ContentVariablesNameLower == 'salesphone') {
				$ContentVariable->nodeValue = $SalesPhone;
			} else if ($ContentVariablesNameLower == 'servicephone') {
				$ContentVariable->nodeValue = $ServicePhone;
			} else if ($ContentVariablesNameLower == 'dealerhomepage') {
				$ContentVariable->nodeValue = $DealerHomePage;
			} else if ($ContentVariablesNameLower == 'dealerlogo') {
				$ContentVariable->nodeValue = $dealerlogo;
			} else if ($ContentVariablesNameLower == 'dealerbanner') {
				$ContentVariable->nodeValue = $dealerbanner;			
			}

		}
		
	}

}

$CampaignElement = $xmlDoc->getElementsByTagName( "Program" );
$CampaignElement->item(0)->setAttribute('Name', $ProgramName);

$Campaign = $xmlDoc->getElementsByTagName( "Campaign" );

foreach( $Campaign as $Campaign ) {
	$CampaignID = $Campaign->getAttribute('Id');
	$CampaignName = $Campaign->getAttribute( "Name" );
	
	$CampaignElement = $Campaign->getElementsByTagName( "CampaignElement" );
	foreach( $CampaignElement as $CampaignElement )
	{
		
		$Category = $CampaignElement->getAttribute( "Category" );
		$Type = $CampaignElement->getAttribute( "Type" );
		$ElementID = $CampaignElement->getAttribute('Id');
		$ElementName = $CampaignElement->getAttribute( "Name" );
		$ElementType = $CampaignElement->getAttribute( "Type" );
		
		if ($ElementID == "1") {

			$Schedules = $CampaignElement->getElementsByTagName( "Schedules" );
			if ($programID == '1') {
				$scheduleName = 'Scheduled Email';
				$startDate = $LaunchDate;
			} else {
				$scheduleName = 'Quick Blast Email';
				$startDate = date("m/d/Y H:i:s",time());
			}

			foreach( $Schedules as $Schedules ) {
				$Schedule = $Schedules->getElementsByTagName('Schedule');
				foreach( $Schedule as $Schedule ) {

					$Schedule->getElementsByTagName("Subject")->item(0)->nodeValue = $scheduleName;

					$newStartElement = $Schedule->getElementsByTagName("Start");
					$newStartElement->item(0)->setAttribute('DateTime', $startDate);
				}

			}		
		} else if ($ElementID == "9") {
			$Schedules = $CampaignElement->getElementsByTagName( "Schedules" );
			if ($programID == '1') {
				$scheduleName = 'Scheduled Email';
				$startDate = $LaunchDate1;
			} else {
				$scheduleName = 'Quick Blast Email';
				$startDate = date("m/d/Y H:i:s",time());
			}

			foreach( $Schedules as $Schedules ) {
				$Schedule = $Schedules->getElementsByTagName('Schedule');
				foreach( $Schedule as $Schedule ) {

					$Schedule->getElementsByTagName("Subject")->item(0)->nodeValue = $scheduleName;

					$newStartElement = $Schedule->getElementsByTagName("Start");
					$newStartElement->item(0)->setAttribute('DateTime', $startDate);
				}

			}	
			
		} else if ($ElementID == "2") {
			$i = 1;
			if ($ContactRadio == 'all') {

			} else if (($ContactRadio == 'email') || ($ContactRadio == 'list')) {
				$Filter = $CampaignElement->getElementsByTagName( "Filter" );
				foreach( $Filter as $Filter ) {
					if ($ContactRadio == 'email') {
						$pieces = explode(",", $emailData);
						$fields = 'email';
					} else {
						$pieces = explode(",", $listData);
						$fields = 'importname';
					}					
					foreach ($pieces as $ContactList) {
						if ($ContactList != '') {
							//echo "ContactList = $ContactList<br>";
							$newCriteriaElement = $xmlDoc ->createElement('Criteria');
							$newCriteriaElement->setAttribute('Row', $i);
							$newCriteriaElement->setAttribute('Field', $fields);
							$newCriteriaElement->setAttribute('Operator', 'Equal');
							$newCriteriaElement->setAttribute('Value', $ContactList);
							$Filter -> appendChild($newCriteriaElement);
							$i++;
						}
					}	
				}

			}
			if ($mode == 'preview') {					
					
				$Filter = $CampaignElement->getElementsByTagName( "Filter" );
				foreach( $Filter as $Filter ) {
					
					
					//echo "ContactList = $ContactList<br>";
					$newCriteriaElement = $xmlDoc ->createElement('Criteria');
					$newCriteriaElement->setAttribute('Row', $i);
					$newCriteriaElement->setAttribute('Field', 'isseed');
					$newCriteriaElement->setAttribute('Operator', 'Equal');
					$newCriteriaElement->setAttribute('Value', 'True');
					$Filter -> appendChild($newCriteriaElement);
					$i++;						
				}						
				
			}
		
		} else if (($ElementID == "7") || ($ElementID == "8")) {
			if ($mode == 'preview') {
				$i = 1;
				$Filter = $CampaignElement->getElementsByTagName( "Filter" );
				foreach( $Filter as $Filter ) {					
					
					//echo "ContactList = $ContactList<br>";
					$newCriteriaElement = $xmlDoc ->createElement('Criteria');
					$newCriteriaElement->setAttribute('Row', $i);
					$newCriteriaElement->setAttribute('Field', 'isseed');
					$newCriteriaElement->setAttribute('Operator', 'Equal');
					$newCriteriaElement->setAttribute('Value', 'True');
					$Filter -> appendChild($newCriteriaElement);
					$i++;						
				}
			}

		
		
		} else if ($Type == "Microsite") {
			$newTouchPointElement = $xmlDoc ->createElement('CallbackTouchPoint');			
			$newTouchPointElement->setAttribute('DbId', '0');
			$newTouchPointElement->setAttribute('Callback', 'http://'.$url);
			$newTouchPointElement->setAttribute('Id', '0');

			$CallbackTouchPoints = $CampaignElement->getElementsByTagName('CallbackTouchPoints')->item(0);
			
			$newTouchPoints = false;	
			if ($CallbackTouchPoints == null) {
				$CallbackTouchPoints = $xmlDoc ->createElement('CallbackTouchPoints');
				$newTouchPoints = true;
			} 

			$CallbackTouchPoints -> appendChild($newTouchPointElement);

			if ($newTouchPoints == true) {
				$CampaignElement -> appendChild($CallbackTouchPoints);
			}


			//
			$newBaseUrl = $xmlDoc ->createElement('BaseUrl');			
			$newBaseUrl->setAttribute('DbId', '0');
			$newBaseUrl->setAttribute('URL', $url);
			$newBaseUrl->setAttribute('Id', '0');
			$newBaseUrl->setAttribute('Extension', $subdomain);
			$newBaseUrl->setAttribute('Outbound_Id', '0');
			$newBaseUrl->setAttribute('Outbound_DbId', '0');
			$newBaseUrl->setAttribute('Parent_Id', '0');
			$newBaseUrl->setAttribute('PurlPosition', 'Prefix');
			$newBaseUrl->setAttribute('Domain', $domain);
			$newBaseUrl->setAttribute('SSL', "False");
			$newBaseUrl->setAttribute('SearchEngineAllowed', "False");

			$BaseUrlCollection = $CampaignElement->getElementsByTagName('BaseUrlCollection')->item(0);
			
			$newBaseUrlCollection = false;	
			if ($BaseUrlCollection == null) {
				$BaseUrlCollection = $xmlDoc ->createElement('BaseUrlCollection');
				$newBaseUrlCollection = true;
			} 

			$BaseUrlCollection -> appendChild($newBaseUrl);

			if ($newBaseUrlCollection == true) {
				$CampaignElement -> appendChild($BaseUrlCollection);
			}

			
			
		} else {
			continue;
		}
		

	}

}



$xmlDoc->save($xmlFile);


$file = file_get_contents($xmlFile);

$findme = '<?xml version="1.0"?>';
$pos = strpos($file, $findme);
if ($pos === false) {
} else {
	$file = substr($file,strlen($findme),strlen($file));
}
$byteArr = str_split($file);
foreach ($byteArr as $key=>$val) { 
	$byteArr[$key] = ord($val); 
}
$publishRequest  = array
(
	"Credentials" => array
	(
		"Ticket" => $userTicket        
	), 
	"MamlFormat" => 0,
	"Maml" => $byteArr			
);
$publishResponse = callService("programservice/PublishProgram", $publishRequest);
$ErrorCode = $publishResponse->{"Result"}->{"ErrorCode"};
//$ErrorCode = '';
if ($ErrorCode == "") {

	unlink($xmlFile);
	$isFound = 2;
} else {
	//$errorMessage = "publishResponse ERROR : <br> ErrorMessage -> ".$publishResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
	//"ExceptionMessage : ".$publishResponse->{"Result"}->{"ErrorMessage"};
	$errorMessage = "publishResponse ERROR : <br> ErrorMessage -> ".$publishResponse->{"Result"}->{"ErrorMessage"};
}





if ($errorMessage == '') {
	echo $callback, '(',
        json_encode( array(
            'success'   => true,
			'message'	=>$errorMessage,
		    'url'		=>'http://'.$url
        )), ')';

} else {
	echo $callback, '(',
        json_encode( array(
            'success'   => false,
			'message'	=>$errorMessage,
		    'url'		=>''
        )), ')';

}







