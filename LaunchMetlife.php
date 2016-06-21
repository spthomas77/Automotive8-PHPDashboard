<?php
date_default_timezone_set('America/Los_Angeles');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
include 'share.php';

$callback = $_POST["callback"];
$email = $_POST["email"];
$pwd = $_POST["pwd"];
$account = $_POST["account"];

$programID = $_POST["programID"];
$LaunchDate = $_POST["LaunchDate"];
$ContactList = $_POST["ContactList"];
$ProgramName = $_POST["ProgramName"];
$EmailFromAddress = $_POST["EmailFromAddress"];
$EmailFromName = $_POST["EmailFromName"];
$ReplyToEmailAddress = $_POST["ReplyToEmailAddress"];
$EmailSubject = $_POST["EmailSubject"];
$HTMLContent = $_POST["HTMLContent"];
$TextOnlyContent = $_POST["TextOnlyContent"];
$ContactRadio = $_POST["ContactRadio"];
$listData = $_POST["listData"];
$emailData = $_POST["emailData"];
$data = $_POST["data"];
$PartnerGuid = $_POST["PartnerGuid"];
$PartnerPassword = $_POST["PartnerPassword"];

$xyzCompany = $_POST["xyzCompany"];
$employees = $_POST["employees"];
$domvar = $_POST["domvar"];
$employee = $_POST["employee"];
$companylogo = $_POST["companylogo"];
$ProgramDate = $_POST["ProgramDate"];
$domain = $_POST["DomainName"];
$subdomain = $_POST["subdomain"];
$url = $domain;

if ($subdomain == '') {
	
} else {
	$url = $domain.'/'.$subdomain;
}


$contentNameArray = null;
$contentValueArray = null;

$max = sizeof($data);
for($i = 0; $i < $max; ++$i) {
	$name = $data[$i]['name'];	
	$NameLower = strtolower($name);

	//if (0 === strpos($NameLower, 'wiz_')) {
		$value = $data[$i]['value'];
		$contentNameArray[] = $NameLower;
		$contentValueArray[] = $value;
	//}		
}


//echo "emailData = $emailData<br>";

$t = date("mdY-His",time());

$serviceLog = fopen("servicelog.txt", "a");
fwrite($serviceLog, "\r\n $t");
fwrite($serviceLog, "\r\n email : $email");
fwrite($serviceLog, "\r\n max : $max");
for($i = 0; $i < $max; ++$i) {
	$name = $data[$i]['name'];
	$value = $data[$i]['value'];
	fwrite($serviceLog, "\r\n name : $name");
	fwrite($serviceLog, "\r\n value : $value");
}
fwrite($serviceLog, "\r\n ===========");
fclose($serviceLog);


$xmlSaveFile = getxmlFile($programID);

$t = date("mdY-His",time());
$xmlFile = getcwd().'/temp/'.$t.'.maml';

copy($xmlSaveFile, $xmlFile);
	
$errorMessage = '';

$userTicket = getTicket($account, $email, $pwd, $PartnerGuid, $PartnerPassword);




// change program name;
$xmlDoc = new DOMDocument();
$xmlDoc->load( $xmlFile );

//$max = sizeof($contentNameArray);
$max = 6;
if ($max > 0) {
	$ContentVariable = $xmlDoc->getElementsByTagName( "ContentVariable" );
	foreach( $ContentVariable as $ContentVariable )
	{
		$ContentVariablesName = $ContentVariable->getAttribute('Name');
		$ContentVariablesNameLower = strtolower($ContentVariablesName);

		for($i = 0; $i < $max; ++$i) {
			if ($ContentVariablesNameLower == 'xyzcompany') {
				$ContentVariable->nodeValue = $xyzCompany;
			} else if ($ContentVariablesNameLower == 'employees') {
				$ContentVariable->nodeValue = $employees;
			} else if ($ContentVariablesNameLower == 'domvar') {
				$ContentVariable->nodeValue = $domvar;
			} else if ($ContentVariablesNameLower == 'employee') {
				$ContentVariable->nodeValue = $employee;
			} else if ($ContentVariablesNameLower == 'companylogo') {
				$ContentVariable->nodeValue = $companylogo;
			} else if ($ContentVariablesNameLower == 'launchdate') {
				$ContentVariable->nodeValue = $ProgramDate;
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
		
		if ($ElementName == "PURL Initial Email") {

			$Schedules = $CampaignElement->getElementsByTagName( "Schedules" );
			
			$scheduleName = 'Scheduled Email';
			$startDate = $LaunchDate;
			

			foreach( $Schedules as $Schedules ) {
				$Schedule = $Schedules->getElementsByTagName('Schedule');
				foreach( $Schedule as $Schedule ) {

					$Schedule->getElementsByTagName("Subject")->item(0)->nodeValue = $scheduleName;

					$newStartElement = $Schedule->getElementsByTagName("Start");
					$newStartElement->item(0)->setAttribute('DateTime', $startDate);
				}

			}
			

			$Messages = $CampaignElement->getElementsByTagName( "Messages" );
			
				
			foreach( $Messages as $Messages )
			{
				
				$Message = $Messages->getElementsByTagName('Message');
				
				foreach( $Message as $Message ) {
					$MessageId = $Message->getAttribute('Id');		
					
					$Message->getElementsByTagName("FromName")->item(0)->nodeValue = $EmailFromName;
					$Message->getElementsByTagName("FromAddress")->item(0)->nodeValue = $EmailFromAddress;
					$Message->getElementsByTagName("ReplyTo")->item(0)->nodeValue = $ReplyToEmailAddress;
					$Message->getElementsByTagName("Subject")->item(0)->nodeValue = $EmailSubject;
					$Message->getElementsByTagName("HtmlContent")->item(0)->nodeValue = $HTMLContent;
					$Message->getElementsByTagName("TextContent")->item(0)->nodeValue = $TextOnlyContent;

					

					
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

	//unlink($xmlFile);
	$isFound = 2;
} else {
	//$errorMessage = "publishResponse ERROR : <br> ErrorMessage -> ".$publishResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
	//"ExceptionMessage : ".$publishResponse->{"Result"}->{"ErrorMessage"};
	$errorMessage = "publishResponse ERROR : <br> ErrorMessage -> ".$publishResponse->{"Result"}->{"ErrorMessage"};
}


echo $errorMessage;

/*
if ($errorMessage == '') {
	echo $callback, '(',
        json_encode( array(
            'success'   => true,
			'message'	=>$errorMessage
        )), ')';

} else {
	echo $callback, '(',
        json_encode( array(
            'success'   => false,
			'message'	=>$errorMessage
        )), ')';

}

*/





