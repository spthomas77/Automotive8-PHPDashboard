<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('share.php');

$callback = $_GET["callback"];
$programID = $_GET["programID"];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];
$account = $_GET["account"];

$SelectedAccountID = $account;

$EmailFromAddress = '';
$EmailFromName = '';
$ReplyToEmailAddress = '';
$EmailSubject = '';
$HTMLContent = '';
$TextOnlyContent = '';
$HTMLContent1 = '';
$TextOnlyContent1 = '';

$xmlFile = getxmlFile($programID);

	
		
$xmlDoc = new DOMDocument();
$xmlDoc->load( $xmlFile );



$contentArray = getAllContentArray($xmlDoc);
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
		if ($ElementID == "1") {
			$Messages = $CampaignElement->getElementsByTagName( "Messages" );
			
				
			foreach( $Messages as $Messages )
			{
				
				$Message = $Messages->getElementsByTagName('Message');
				
				foreach( $Message as $Message ) {
					$MessageId = $Message->getAttribute('Id');										
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
			

		} else if ($ElementID == "9") {
			$Messages = $CampaignElement->getElementsByTagName( "Messages" );
			
				
			foreach( $Messages as $Messages )
			{
				
				$Message = $Messages->getElementsByTagName('Message');
				
				foreach( $Message as $Message ) {
					$MessageId = $Message->getAttribute('Id');										
					$HTMLContentNode = $Message->getElementsByTagName('HtmlContent');
					foreach( $HTMLContentNode as $HTMLContentNode ) {
						$HTMLContent1 = $HTMLContentNode->nodeValue;
					}
					$TextContent = $Message->getElementsByTagName('TextContent');
					foreach( $TextContent as $TextContent ) {
						$TextOnlyContent1 = $TextContent->nodeValue;
					}
				}
			}
			

		
		
		} else {
			continue;
		}
		

	}
}






$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);
$DomainListArray = GetDomainList($USERTICKET);


echo $callback, '(',
        json_encode( array(
            'success'   => true,
			'contentArray'=>$contentArray,
			'campaignArray'=>$campaignArray,
			'DomainListArray'=>$DomainListArray,
			'HTMLContent'=>$HTMLContent,
			'TextOnlyContent'=>$TextOnlyContent,
			'HTMLContent1'=>$HTMLContent1,
			'TextOnlyContent1'=>$TextOnlyContent1	
        )), ')';






