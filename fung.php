<?php
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
include 'share.php';

/*
$email = $_GET["email"];
$pwd = $_GET["pwd"];
$emailData = $_GET['emailData'];
$LastName = $_GET['LastName'];
$FirstName = $_GET['FirstName'];
$opr1 = $_GET['opr1'];
$opr2 = $_GET['opr2'];
$opr3 = $_GET['opr3'];
$Product = $_GET['Product'];
$account = $_GET["account"];
$PartnerGuid = $_GET["PartnerGuid"];
$PartnerPassword = $_GET["PartnerPassword"];
$filters = $_GET["filters"];

$filters = urldecode($filters);
*/ 
//echo "filters = $filters<br>";


$SelectedAccountID = '2848'; 
$email = 'kdutta@mindfireinc.com'; 
$pwd = 'MindFire2012'; 
$PartnerGuid = 'Ventas Strategies'; 
$PartnerPassword= '846a4340e6b4e'; 
$USERTICKET = getTicket($SelectedAccountID, $email, $pwd, $PartnerGuid, $PartnerPassword);


$jsonFilters = NULL;
$result = loadContactArrayFilters($email,$USERTICKET,FOLDER,$SelectedAccountID,$jsonFilters);


//$result = getContactFieldNamesfff($email,$SelectedAccountID); 
function getContactFieldNamesfff($email,$account){

	$sqltxt = "select * from xmediaContact where username = ? and accountid = ? order by orderid";
	$row2 = DB::QueryExecuteMulti($sqltxt, $email, $account);
	$FieldNames = array();

	$diffArr;
	$exField = array();
	$defFields = array("FirstName","LastName","Email","Phone","FollowUp","Appointment_Date","PURL","Sold_Lost");		

	if ($row2){
		foreach( $row2 as $row1 ) {			
			$r1 = $row1['fieldname'];
			$is7Field = chk7Fieldfff($r1); 
			if($is7Field){
				$exField[] = $r1; 
			}
			$FieldNames[] = $r1;
		}
		sort($exField);
		sort($defFields);
		if( $exField == $defFields ){
			echo "yes";
			$FieldNames[]= "Yes"; 
		}else{ 
			echo "no";
			$FieldNames[]= "NO";  //*****START HERE****diff >> add fieldName 
			$diffArr = array_diff($defFields , $exField);			

			//echo json_encode($diffArr).'<br>';

			foreach($diffArr as $val){
				$FieldNames[] = $val; 
			}
		}
	} else {
		$FieldNames = array("FirstName","LastName","Email","Phone","FollowUp","Appointment_Date","Sold_Lost");	
	}	
	//$FieldNames = $diffArr; 
	return $FieldNames;

}

//FirstName LastName Email Phone Notes FollowUp Sold_Lost Appointment_Date Appointment_Time
//true-1-true-1-true-1-true-1-false--true-1-true-1-true-1-false-

function chk7Fieldfff($fieldName){
	$srcField = array("FirstName","LastName","Email","Phone","FollowUp","Appointment_Date","Sold_Lost");	
//	echo $fieldName; 

//	if (array_key_exists($fieldName,$srcField)){
	if (in_array($fieldName,$srcField)){
		//echo "-true-";
		return true; 
	}else{
		//echo "-false-";
		//array_push($srcField,$fieldName);
		return false; 
	}	
	return false; 
}






//header("Content-type: application/octet-stream");
//header("Content-Disposition: attachment; filename=\"exportData.csv\"");

//echo $output;




