<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('share.php');

$callback = $_GET["callback"];
$programID = $_GET["programID"];

$EmailFromAddress = '';
$EmailFromName = '';
$ReplyToEmailAddress = '';
$EmailSubject = '';
$HTMLContent = '';
$TextOnlyContent = '';

$xmlFile = getxmlFile($programID);

$email = $_GET["email"];
$pwd = $_GET["pwd"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];

if ($account) {
	$SelectedAccountID = $account;
} else {
	$SelectedAccountID = 228;
}
$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);

$DomainListArray = GetDomainList($USERTICKET);

	
		
$xmlDoc = new DOMDocument();
$xmlDoc->load( $xmlFile );


$contentArray = getContentArray($xmlDoc);
$campaignArray = getCampaignArray($xmlDoc);



$Campaign = $xmlDoc->getElementsByTagName( "Campaign" );
foreach( $Campaign as $Campaign )
{
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
			$Messages = $CampaignElement->getElementsByTagName( "Messages" );
			
				
			foreach( $Messages as $Messages )
			{
				
				$Message = $Messages->getElementsByTagName('Message');
				
				foreach( $Message as $Message ) {
					$MessageId = $Message->getAttribute('Id');					
					$FromName = $Message->getElementsByTagName('FromName');
					foreach( $FromName as $FromName ) {
						$EmailFromName = $FromName->nodeValue;
					}
					$FromAddress = $Message->getElementsByTagName('FromAddress');
					foreach( $FromAddress as $FromAddress ) {
						$EmailFromAddress = $FromAddress->nodeValue;
					}
					$ReplyTo = $Message->getElementsByTagName('ReplyTo');
					foreach( $ReplyTo as $ReplyTo ) {
						$ReplyToEmailAddress = $ReplyTo->nodeValue;
					}
					$Subject = $Message->getElementsByTagName('Subject');
					foreach( $Subject as $Subject ) {
						$EmailSubject = $Subject->nodeValue;
					}
					$HTMLContentNode = $Message->getElementsByTagName('HtmlContent');
					foreach( $HTMLContentNode as $HTMLContentNode ) {
						$HTMLContent = $HTMLContentNode->nodeValue;
					}
					$TextContent = $Message->getElementsByTagName('TextContent');
					foreach( $TextContent as $TextContent ) {
						$TextOnlyContent = $TextContent->nodeValue;
					}
				}
			}
			

		
		} else {
			continue;
		}
		

	}
}




echo $callback, '(',
        json_encode( array(
            'success'   => true,
			'EmailFromAddress'=>$EmailFromAddress,
			'DomainListArray'=>$DomainListArray,
			'EmailFromName'=>$EmailFromName,
			'ReplyToEmailAddress'=>$ReplyToEmailAddress,
			'EmailSubject'=>$EmailSubject,			
			'TextOnlyContent'=>$TextOnlyContent,
			'contentArray'=>$contentArray,
			'campaignArray'=>$campaignArray,
			'HTMLContent'=>$HTMLContent,
        )), ')';






