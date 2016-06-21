<?php
date_default_timezone_set('America/Los_Angeles');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
include 'share.php';

$callback = $_GET["callback"];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$account = $_GET["account"];

$programID = $_GET["programID"];
$LaunchDate = $_GET["LaunchDate"];
$ContactList = $_GET["ContactList"];
$ProgramName = $_GET["ProgramName"];
$EmailFromAddress = $_GET["EmailFromAddress"];
$EmailFromName = $_GET["EmailFromName"];
$ReplyToEmailAddress = $_GET["ReplyToEmailAddress"];
$EmailSubject = $_GET["EmailSubject"];
$HTMLContent = $_GET["HTMLContent"];
$TextOnlyContent = $_GET["TextOnlyContent"];
$ContactRadio = $_GET["ContactRadio"];
$listData = $_GET["listData"];
$emailData = $_GET["emailData"];
$data = $_GET["data"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];

$contentNameArray = null;
$contentValueArray = null;

$max = sizeof($data);
for($i = 0; $i < $max; ++$i) {
	$name = $data[$i]['name'];	
	$NameLower = strtolower($name);

	if (0 === strpos($NameLower, 'wiz_')) {
		$value = $data[$i]['value'];
		$contentNameArray[] = $NameLower;
		$contentValueArray[] = $value;
	}		
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

$max = sizeof($contentNameArray);
if ($max > 0) {
	$ContentVariable = $xmlDoc->getElementsByTagName( "ContentVariable" );
	foreach( $ContentVariable as $ContentVariable )
	{
		$ContentVariablesName = $ContentVariable->getAttribute('Name');
		$ContentVariablesNameLower = strtolower($ContentVariablesName);

		for($i = 0; $i < $max; ++$i) {
			$name = $contentNameArray[$i];
			$value = $contentValueArray[$i];
			if ($ContentVariablesNameLower == $name) {
				$ContentVariable->nodeValue = $value;
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
		
		if ($Category == "Outbound") {

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
			
		} else if ($Category == "TargetAudience") {
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
					$i = 1;
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







