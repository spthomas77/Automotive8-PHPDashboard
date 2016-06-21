<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
require_once('share.php');

$callback = $_GET['callback'];
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$account = $_GET["account"];
$mode = $_GET["mode"];
$to_select_list = $_GET["to_select_list"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];

//echo "aaa<br>";
//echo "mode = $mode<br>";
//var_dump($to_select_list);

if ($account) {
	$SelectedAccountID = $account;
} else {
	$SelectedAccountID = 228;
}

if ($mode) {

} else {
	$mode = '';
}

if ($mode == 'save') {
	//echo "$email, $account, $to_select_list"
	saveXmediaContact($email, $account, $to_select_list);
	echo $callback, '(',
		json_encode( array(
			'success'   => true
	)), ')';
} else {

	$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);
	//echo "USERTICKET = $USERTICKET<br>";

	$ContactFieldList = GetContactFieldList($USERTICKET);
	$ContactFieldNames = getContactFieldNames($email,$account);

	//echo json_encode($rows);

	echo $callback, '(',
		json_encode( array(
			'ContactFieldList'   => $ContactFieldList,
			'ContactFieldNames'  => $ContactFieldNames
	 )), ')';
}





