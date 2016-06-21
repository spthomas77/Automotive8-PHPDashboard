<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('share.php');

$email = $_GET["email"];
$pwd = $_GET["pwd"];
$account = $_GET["account"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];



if ($account) {
	$SelectedAccountID = $account;
} else {
	$SelectedAccountID = 228;
}

$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);
$jsonFilters = NULL;
$result = loadContactArrayFilters($email,$USERTICKET,FOLDER,$account,$jsonFilters);

echo json_encode( array(
	'success'   => $result['success'],
	'data'		=> $result['data'],
	'colNames'	=> $result['colNames'],
	'colModel'	=> $result['colModel']
));




