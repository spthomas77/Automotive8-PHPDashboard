<?php
define("FOLDER", "/var/www/vhosts/ventas.com-ext.com/httpdocs/");
require_once('class.DB.php');
date_default_timezone_set('America/Los_Angeles');

ini_set("memory_limit", "-1");

DB::$SERVER = 'localhost';
DB::$DATABASE = 'studioCRM';
DB::$USERNAME = 'bondadmin';
DB::$PASSWORD = 'auto!23$';

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

function getFilter($FirstName,$LastName,$emailData,$opr1,$opr2,$opr3) {
	$Filter = '';
	$email = $_SESSION['admin']['email'];
	$superadmin = $_SESSION['admin']['superadmin'];

	$JoinOperator = "";
	$CriteriaRow = "";

	$row = 0;

	//echo "Product=$Product,LastName=$LastName<br>";


	if ($FirstName != '') {
		$row++;
		$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"FirstName\" Operator=\"$opr1\" Value=\"$FirstName\" <br>";
		$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"FirstName\" Operator=\"$opr1\" Value=\"$FirstName\" />";
		$JoinOperator = "$row";
	}



	if ($LastName != '') {
		$row++;
		$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"LastName\" Operator=\"$opr2\" Value=\"$LastName\" <br>";
		$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"LastName\" Operator=\"$opr2\" Value=\"$LastName\" />";
		if ($JoinOperator == '') {
			$JoinOperator = "$row";
		} else {
			$JoinOperator = "$JoinOperator &amp; $row";
		}
	}

	if ($emailData != '') {
		$row++;
		$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"email\" Operator=\"$opr3\" Value=\"$emailData\" <br>";
		$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"email\" Operator=\"$opr3\" Value=\"$emailData\" />";
		if ($JoinOperator == '') {
			$JoinOperator = "$row";
		} else {
			$JoinOperator = "$JoinOperator &amp; $row";
		}
	}
		
	//echo "JoinOperator = $JoinOperator<br>CriteriaRow1 = $CriteriaRow1<br>";

	if ($row > 0) {
		$Filter = "<Filter CriteriaJoinOperator=\"$JoinOperator\">$CriteriaRow</Filter>";
	} else {
		$Filter = "<Filter CriteriaJoinOperator=\"&amp;\" />";		
	}
	
	
	return $Filter;
}

function loadContact($userTicket,$folder,$FirstName,$LastName,$emailData,$opr1,$opr2,$opr3) {
	$Contact = "";
	$rows = array();

	$FieldNames = array("FirstName","LastName","Email","Phone","Address1","City","State","Zip");

	//echo "userTicket=$userTicket,emailData=$emailData,LastName=$LastName,opr1=$opr1,opr2=$opr2<br>";

	
	$Filter =  getFilter($FirstName,$LastName,$emailData,$opr1,$opr2,$opr3);
	$ContactListRequest   = array
	(
		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
	
		"FieldNames" => $FieldNames,
		"Filter" => $Filter,
		"OutputType" => 1,
	);
	$ContactListResponse = callService("contactservice/GetContactList", $ContactListRequest);
	$ErrorCode = $ContactListResponse->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {
		$Contacts = $ContactListResponse->{"Contacts"};		
		foreach ($Contacts as $chr) {
			$Contact .= chr($chr);
		}
		//echo "<br>Contact : $Contact <br>";
		$t = time();
		$importFile = $folder . '/temp/'.$t.'.csv';
		$FileName = basename($importFile); 
		//echo "<br>importFile : $importFile,FileName : $FileName <br>";
		file_put_contents( $importFile, $Contact);	

		if (($handle = fopen($importFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row == 0) {
					$row++;
					$header = $data;
				} else {
					$d01 = @$data[0];
					$d02 = @$data[1];
					$d03 = @$data[2];
					$d04 = @$data[3];
					$d05 = @$data[4];
					$d06 = @$data[5];
					$d07 = @$data[6];
					$d08 = @$data[7];
										

					$group = array(					
						'FirstName'=>$d01,
						'LastName'=>$d02,
						'Email'=>$d03,
						'Phone'=>$d04,
						'Address'=>$d05,
						'City'=>$d06,
						'State'=>$d07,
						'Zip'=>$d08
					);
					
					$rows[] = $group;

					
					$row++;
				}
			}
		}
		fclose($handle);
		unlink($importFile);
		


	} else {
		$errorMessage = "ContactListResponse ERROR : <br> ErrorMessage -> ".$ContactListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$ContactListResponse->{"Result"}->{"ExceptionMessage"};
		//$errorMessage = "ImportResponse ERROR : <br> ErrorMessage -> ".$ImportResponse->{"Result"}->{"ErrorMessage"};
		//echo $errorMessage."<BR>";
	}

	return $rows;

}

function getxmediaAPI($account) {
	$sqltxt = "select * from xmediaAPI where accountname = ? limit 1";
	$row2 = DB::QueryExecuteMulti($sqltxt, $account);
	$FieldNames = array();
	
	if ($row2) {
		foreach( $row2 as $row1 ) {			
			$FieldNames[] = $row1['PartnerGuid'];
			$FieldNames[] = $row1['PartnerPwd'];
		}
	} else {
		$FieldNames[] = '';
		$FieldNames[] = '';
	}
	return $FieldNames;

}

function getxmediaAPIaccount() {
	$sqltxt = "select * from xmediaAPI";
	$row2 = DB::QueryExecuteMulti($sqltxt);
	$AccountNames = array();
	
	if ($row2) {
		foreach( $row2 as $row1 ) {			
			$r1 = $row1['accountname'];
			$AccountNames[] = $r1;
		}
	}
	return $AccountNames;

}

function getContactFieldNames($email,$account) {

	$sqltxt = "select * from xmediaContact where username = ? and accountid = ? order by orderid";
	$row2 = DB::QueryExecuteMulti($sqltxt, $email, $account);
	$FieldNames = array();
	
	if ($row2) {
		foreach( $row2 as $row1 ) {			
			$r1 = $row1['fieldname'];
			$FieldNames[] = $r1;
		}
	} else {
		$FieldNames = array("FirstName","LastName","Email","Phone","Address1","City","State","Zip");	
	}
	return $FieldNames;

}

function saveXmediaContact($email, $account, $to_select_list) {

	$sqltxt = "delete from xmediaContact where username = ? and accountid = ?";
	//echo "sqltxt = $sqltxt<br>";
	$result = DB::QueryExecute($sqltxt, $email, $account);
	
	//var_dump($to_select_list);
	//echo "<br>";
	
	$i = 1;
	$pieces = explode(",", $to_select_list);
	foreach ($pieces as $ContactList) {
		if ($ContactList != '') {
			$sqltxt = "INSERT INTO xmediaContact (username, accountid, orderid, fieldname) VALUES (?, ?, ?, ?)";
			$result = DB::QueryExecute($sqltxt, $email, $account, $i, $ContactList);
			$i = $i+1;
		}

	}	
}

function mapOperator($op) {
	$ret = 'Contains';
	if ($op == 'eq') {
		$ret = 'Equal';
	} else if ($op == 'ne') {
		$ret = 'NotEqual';
	} else if ($op == 'bw') {
		$ret = 'StartsWith';
	} else if ($op == 'bn') {
		$ret = 'NotStartsWith';
	} else if ($op == 'ew') {
		$ret = 'EndsWith';
	} else if ($op == 'en') {
		$ret = 'NotEndsWith';
	} else if ($op == 'cn') {
		$ret = 'Contains';
	} else if ($op == 'nc') {
		$ret = 'NotContains';
	} else if ($op == 'lt') {
		$ret = 'Before';
	} else if ($op == 'le') {
		$ret = 'BeforeEqual';
	} else if ($op == 'gt') {
		$ret = 'After';
	} else if ($op == 'ge') {
		$ret = 'AfterEqual';
	} else if ($op == 'in') {
		$ret = 'Contains';
	} else if ($op == 'ni') {
		$ret = 'NotContains';
	}

	return $ret;

}

function getJsonFilter($jsonFilters) {
	$Filter = '';

	if ($jsonFilters) {
		$JoinOperator = "";
		$CriteriaRow = "";
		$row = 0;		
		$JoinOperator = $jsonFilters->{"groupOp"};
		if ($JoinOperator == 'AND') {
			$JoinOperator = "&amp;";
		} else if ($JoinOperator == 'OR') {
			$JoinOperator = "|";
		}

		$rules = $jsonFilters->{"rules"};
		foreach ($rules as $rule) {
			$field	= $rule->{"field"};
			$op		= $rule->{"op"};
			$op		= mapOperator($op);
			$data	= $rule->{"data"};
			//echo "field = $field, op = $op, data = $data<br>";
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"$field\" Operator=\"$op\" Value=\"$data\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"$field\" Operator=\"$op\" Value=\"$data\" />";
			
		}
		
		$Filter = "<Filter CriteriaJoinOperator=\"$JoinOperator\">$CriteriaRow</Filter>";
		$CriteriaRow1 = "Filter CriteriaJoinOperator=\"$JoinOperator\" $CriteriaRow1 Filter<br>";
		
	} else {
		$CriteriaRow1 = "Filter CriteriaJoinOperator=\"&amp;\" <br>";		
		$Filter = "<Filter CriteriaJoinOperator=\"&amp;\" />";		

		//echo "CriteriaRow1 = $CriteriaRow1<br>";
	}	
	//echo "JoinOperator = $JoinOperator<br>CriteriaRow1 = $CriteriaRow1<br>";	
	return $Filter;
}

function formatdate($DROPDATE){
//	echo '........DROPDATE=['.$DROPDATE.']';
	$MAILDATE = ''; 
	if($DROPDATE == '12/31/1969'){
		$DROPDATE = ''; 
	}
	if($DROPDATE != ''){
		$date2 = strtotime($DROPDATE);
		$year = date('Y', $date2);
		$month = date('m', $date2);
		$day = date('d', $date2);
		$time = ""; 
		if($date2 != ''){
			$hour = date('g', $date2);
			$min = date('i', $date2);
			$tt = date('a', $date2);
			if($hour != '' ){
				$time = ' '.$hour.':'.$min.' '.$tt; 
			}
	/*
				echo 'date=['.$date2.']';
				echo 'hour=['.$hour.']';
				echo 'min=['.$min.']';
				echo 'tt=['.$tt.']........';
	*/ 
		}	
	//	echo 'time=['.$time.']';
		$MAILDATE = $month.'/'.$day.'/'.$year.$time;
	//	echo 'MAILDATE=['.$MAILDATE.']........';
		if($MAILDATE == '12/31/1969'){
			$MAILDATE = ''; 
		}
	}//end DROPDATE !=''
	return $MAILDATE; 
}



//dtimepicker
function formatdatetime($DROPDATE){
	$MAILDATE = ''; 
	if($DROPDATE == '12/31/1969'){
		$DROPDATE = ''; 
	}
	if($DROPDATE != ''){
		$date2 = strtotime($DROPDATE);
		$year = date('Y', $date2);
		$month = date('m', $date2);
		$day = date('d', $date2);
		$time = ""; 
		if($date2 != ''){
			$hour = date('g', $date2);
			$min = date('i', $date2);
			$tt = date('a', $date2);
			if($hour != '' ){
				$time = ' '.$hour.':'.$min.' '.$tt; 
			}
		}	
		$MAILDATE = $month.'/'.$day.'/'.$year.$time;
		if($MAILDATE == '12/31/1969'){
			$MAILDATE = ''; 
		}
	}//end DROPDATE !=''
	return $MAILDATE; 
}


//First Name,Last Name,Phone,Mobile,Email,Followup,Followup_Date,Appointment_Date,Salesperson,Sale Status,Notes	
function getHeader($fieldName){
	
	$header = $fieldName ;  

	if($fieldName=='Sold_Lost'){
		$header ='Sale Status';

	}else if($fieldName == 'Followup_Date'){
		$header ='Follow Up Date';

	}else if($fieldName == 'SalesmanCode'){
		$header ='Sales Person';	

	}else{
		$header = $fieldName;
	}
	return $header; 
}


function loadContactArrayFiltersUse($email,$userTicket,$folder,$account,$jsonFilters) {	
	//echo "loadContactArrayFilters Start<br>";		
	$Contact = "";
	$result = array(); 
	$rows = array();
				 	  
	$defFields = array("FirstName","LastName","Phone","Mobile","Email","FollowUp","Followup_Date","Appointment_Date","SalesmanCode","Sold_Lost","Notes","PURL");
	$FieldNames = getContactFieldNames($email,$account);
	$FieldHeader = array();
	$colModel = array();	
	
	$FieldCount = sizeof($FieldNames);

	//check field in table is in defField = view && editable.
	for($i = 0; $i < $FieldCount;$i++) {
		
		$fname = $FieldNames[$i];		
		$group = array();
		$group['name'] = $fname;
		$group['index'] = $fname;
		$wide = 90; 
		$view = true; 
		
		if($fname=='Notes'){	
			$wide = -1; 
			$view = false; 
			$group['edittype'] = "textarea"; 
    		$editoptions = array();
			$editoptions['rows'] = '2';
			$editoptions['cols'] = '20';
			$group['editoptions'] = $editoptions;
		}		
		if(strcasecmp($fname, 'PURL') == 0){
			 $editoptions = array();
			 $editoptions['readonly'] = 'readonly';
			 $group['editoptions'] = $editoptions;
		}
		if($fname=='Sold_Lost'){// Sold, Lost, In Progress
			$group['edittype'] = "select";
			$editoptions = array(); 
			$editoptions['value'] = ':;Sold:Sold;Lost:Lost;In Progress:In Progress';
			$group['editoptions'] = $editoptions;
		}
		if($fname=='FollowUp'){	
			$group['edittype'] = "select";
			$editoptions = array();
			$editoptions['value'] = ':;Phone Call:Phone Call;Email:Email;Appointment:Appointment';
			$group['editoptions'] = $editoptions;
		}

		$group['width'] = $wide;
		$group['viewable'] = $view;					
		
		// defField = editable; 
		if (in_array($fname,$defFields) ){
			$group['editable'] = true;		
		}else{
			$group['editable'] = false;		
		}		
		
		$header = getHeader($fname);
		$FieldHeader[] = $header; 
		$colModel[] = $group;
	}

	//Not in table xmediaContact But in defField
	if( $FieldNames == $defFields ){

	}else{ 

		$differ = array_diff($defFields , $FieldNames);			

		foreach($differ as $val){

			$group = array();
			$group['name'] = $val;
			$group['index'] = $val;					

			$wide = 90; 
			$view = true; 
			$canEdit = false; 

			if (in_array($val,$defFields) ){
				$view = false; 
				$canEdit = true;		

				if($val=='Notes' || strcasecmp($val, 'PURL') == 0 ){					
					$wide = -1; 
					$view = false; 
					$canEdit = true;

					if($val=='Notes'){
						$group['edittype'] = "textarea"; 
						$editoptions = array();
						$editoptions['rows'] = '2';
						$editoptions['cols'] = '20';
						$group['editoptions'] = $editoptions;
					}
					if(strcasecmp($val, 'PURL') == 0){					 
						 $editoptions = array();
						 $editoptions['readonly'] = 'readonly';
						 $group['editoptions'] = $editoptions;
					}				
				}
				if($val=='Sold_Lost'){	 // Sold, Lost, In Progress
					//$val = "Status"; 
					$group['edittype'] = "select";
					$editoptions = array();
					$editoptions['value'] = ':;Sold:Sold;Lost:Lost;In Progress:In Progress';
					$group['editoptions'] = $editoptions;
				}
				if($val=='FollowUp'){	
					$group['edittype'] = "select";
					$editoptions = array();
					$editoptions['value'] = ':;Phone Call:Phone Call;Email:Email;Appointment:Appointment';
					$group['editoptions'] = $editoptions;
				}		

			}else{

				$view = true; 
				$canEdit = false;		
			}					

			$group['width'] = $wide;
			$group['viewable'] = $view;		
			$group['editable'] = $canEdit;		

			$FieldNames[] =$val; //set balance header&column

			$header = getHeader($val);
			$FieldHeader[] = $header; 

			$colModel[]   =$group;
		}
	}

	

	//put data

	//print

	echo "FieldNames [".array_values($FieldNames)."]";

	foreach($FieldNames as $key => $value)
	{
	  echo $key."=[". $value."]";
	}





	$Filter =  getJsonFilter($jsonFilters);
	$ContactListRequest   = array(		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
	
		"FieldNames" => $FieldNames,
		"Filter" => $Filter,
		"OutputType" => 1,
	);

	$ContactListResponse = callService("contactservice/GetContactList", $ContactListRequest);
	$ErrorCode = $ContactListResponse->{"Result"}->{"ErrorCode"};


	if ($ErrorCode == "") {

		$Contacts = $ContactListResponse->{"Contacts"};		
		foreach ($Contacts as $chr) {
			$Contact .= chr($chr);
		}
		//echo "<br>Contact : $Contact <br>";
		$t = time();
		$importFile = $folder . '/temp/'.$t.'.csv';
		$FileName = basename($importFile); 
		//echo "<br>importFile : $importFile,FileName : $FileName <br>";

		$FieldCount = sizeof($FieldNames); //new fieldName set
		file_put_contents( $importFile, $Contact);
		if (($handle = fopen($importFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row == 0) {
					$row++;
					$header = $data;
				} else {
					$group = array();
					//dTimepicker
					$adate=""; 
					$atime=""; 
					$dtime=""; 
					for($i = 0; $i < $FieldCount;$i++) {
						$fname = $FieldNames[$i];
						//datepicker
						if ($fname == 'Appointment_Date'){	
							$group[$fname] = formatdatetime(@$data[$i]);
							$adate = @$data[$i]; 						

						}else if($fname == 'Followup_Date'){	
							$group[$fname] = formatdate(@$data[$i]);

						}else{
							$group[$fname] = @$data[$i];
						}
						if ($fname == 'Appointment_Time'){								
							$atime = ' '.@$data[$i] ; 
						}
						if($adate != '' && strlen($adate) == 10){
							$dtime = $adate . $atime ; 						
							$group['Appointment_Date'] = formatdatetime($dtime);	
						}
						
						if ($fname == 'PURL'){	
							
							echo "DDDDDD = [".@$data[$i]."]";

						}
		
					}									
					$rows[] = $group;					
					$row++;
				}
			}
		}
		fclose($handle);
		unlink($importFile);
		$errorMessage = '';


	} else {
		$errorMessage = "ContactListResponse ERROR : <br> ErrorMessage -> ".$ContactListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$ContactListResponse->{"Result"}->{"ExceptionMessage"};
		//$errorMessage = "ImportResponse ERROR : <br> ErrorMessage -> ".$ImportResponse->{"Result"}->{"ErrorMessage"};
		//echo $errorMessage."<BR>";
	}
	if ($errorMessage == '') {
		$result['success'] = true;
	} else {
		$result['success'] = false;
	}

	$result['data'] = $rows;

	$FieldHeader = $FieldNames;  //fung
	$result['colNames'] = $FieldHeader;  
	$result['colModel'] = $colModel;

//	echo '__Header='.json_encode($FieldNames).'<br>\n';
//	echo '__Column='.json_encode($colModel).'<br>\n';
	return $result;
	//echo "loadContactArrayFilters End<br>";
}


function loadContactArrayFiltersSub($email,$userTicket,$folder,$account,$jsonFilters,$mode,$searchData) {	

	$Contact = "";
	$result = array(); 
	$rows = array();
				 	  
	$defFields = array("FirstName","LastName","Phone","Mobile","Email","FollowUp","Followup_Date","Appointment_Date","SalesmanCode","Sold_Lost","Notes","PURL");
	$FieldNames = getContactFieldNames($email,$account);
	$FieldHeader = array();
	$colModel = array();	
	
	$FieldCount = sizeof($FieldNames);

	//check field in table is in defField = view && editable.
	for($i = 0; $i < $FieldCount;$i++) {
		
		$fname = $FieldNames[$i];		
		$group = array();
		$group['name'] = $fname;
		$group['index'] = $fname;
		$wide = 90; 
		$view = true; 
		
		if($fname=='Notes'){	
			$wide = -1; 
			$view = false; 
			$group['edittype'] = "textarea"; 
    		$editoptions = array();
			$editoptions['rows'] = '2';
			$editoptions['cols'] = '20';
			$group['editoptions'] = $editoptions;
		}		
		if(strcasecmp($fname, 'PURL') == 0){
			 $editoptions = array();
			 $editoptions['readonly'] = 'readonly';
			 $group['editoptions'] = $editoptions;
		}
		if($fname=='Sold_Lost'){// Sold, Lost, In Progress
			$group['edittype'] = "select";
			$editoptions = array(); 
			$editoptions['value'] = ':;Sold:Sold;Lost:Lost;In Progress:In Progress';
			$group['editoptions'] = $editoptions;
		}
		if($fname=='FollowUp'){	
			$group['edittype'] = "select";
			$editoptions = array();
			$editoptions['value'] = ':;Phone Call:Phone Call;Email:Email;Appointment:Appointment';
			$group['editoptions'] = $editoptions;
		}

		$group['width'] = $wide;
		$group['viewable'] = $view;					
		
		// defField = editable; 
		if (in_array($fname,$defFields) ){
			$group['editable'] = true;		
		}else{
			$group['editable'] = false;		
		}		
		
		$fheader = getHeader($fname);
		$FieldHeader[] = $fheader; 
		$colModel[] = $group;
	}

	//Not in table xmediaContact But in defField
	if( $FieldNames == $defFields ){

	}else{ 

		$differ = array_diff($defFields , $FieldNames);			

		foreach($differ as $val){

			$group = array();
			$group['name'] = $val;
			$group['index'] = $val;					

			$wide = 90; 
			$view = true; 
			$canEdit = false; 

			if (in_array($val,$defFields) ){
				$view = false; 
				$canEdit = true;		

				if($val=='Notes' || strcasecmp($val, 'PURL') == 0 ){					
					$wide = -1; 
					$view = false; 
					$canEdit = true;

					if($val=='Notes'){
						$group['edittype'] = "textarea"; 
						$editoptions = array();
						$editoptions['rows'] = '2';
						$editoptions['cols'] = '20';
						$group['editoptions'] = $editoptions;
					}
					if(strcasecmp($val, 'PURL') == 0){					 
						 $editoptions = array();
						 $editoptions['readonly'] = 'readonly';
						 $group['editoptions'] = $editoptions;
					}				
				}
				if($val=='Sold_Lost'){	 // Sold, Lost, In Progress
					//$val = "Status"; 
					$group['edittype'] = "select";
					$editoptions = array();
					$editoptions['value'] = ':;Sold:Sold;Lost:Lost;In Progress:In Progress';
					$group['editoptions'] = $editoptions;
				}
				if($val=='FollowUp'){	
					$group['edittype'] = "select";
					$editoptions = array();
					$editoptions['value'] = ':;Phone Call:Phone Call;Email:Email;Appointment:Appointment';
					$group['editoptions'] = $editoptions;
				}		

			}else{

				$view = true; 
				$canEdit = false;		
			}					

			$group['width'] = $wide;
			$group['viewable'] = $view;		
			$group['editable'] = $canEdit;		

			$FieldNames[] =$val; //set balance header&column

			$fheader = getHeader($val);
			$FieldHeader[] = $fheader; 

			$colModel[]   =$group;
		}
	}

	//echo "userTicket=$userTicket,emailData=$emailData,LastName=$LastName,opr1=$opr1,opr2=$opr2<br>";	

	echo 'ss['.$searchData.']'; 

	if( ($mode == '' || $mode == 'All') && $searchData == '' ){
		$Filter =  getJsonFilter($jsonFilters);

	}else{
		$Filter =  getReportFilter($searchData,$mode);
	}


	
	echo " getFilter.php filter >> ".$Filter; 

/*
    foreach($FieldNames as $key => $value)
	{
	  echo $key."=[". $value."]";
	}
*/ 


	$ContactListRequest   = array(		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
	
		"FieldNames" => $FieldNames,
		"Filter" => $Filter,
		"OutputType" => 1,
	);

	$ContactListResponse = callService("contactservice/GetContactList", $ContactListRequest);
	$ErrorCode = $ContactListResponse->{"Result"}->{"ErrorCode"};


	if ($ErrorCode == "") {

		$Contacts = $ContactListResponse->{"Contacts"};		
		foreach ($Contacts as $chr) {
			$Contact .= chr($chr);
		}
		//echo "<br>Contact : $Contact <br>";
		$t = time();
		$importFile = $folder . '/temp/'.$t.'.csv';
		$FileName = basename($importFile); 
		//echo "<br>importFile : $importFile,FileName : $FileName <br>";


		$FieldCount = sizeof($FieldNames); //new fieldName set
		file_put_contents( $importFile, $Contact);
		if (($handle = fopen($importFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row == 0) {
					$row++;
					$header = $data;
				} else {
					$group = array();
					//dTimepicker
					$adate=""; 
					$atime=""; 
					$dtime=""; 
					for($i = 0; $i < $FieldCount;$i++) {
						$fname = $FieldNames[$i];
						//datepicker
						if ($fname == 'Appointment_Date'){	
							$group[$fname] = formatdatetime(@$data[$i]);
							$adate = @$data[$i]; 						

						}else if($fname == 'Followup_Date'){	
							$group[$fname] = formatdate(@$data[$i]);

						}else{
							$group[$fname] = @$data[$i];
						}
						if ($fname == 'Appointment_Time'){								
							$atime = ' '.@$data[$i] ; 
						}
						if($adate != '' && strlen($adate) == 10){
							$dtime = $adate . $atime ; 						
							$group['Appointment_Date'] = formatdatetime($dtime);	
						}
					}									
					$rows[] = $group;					
					$row++;
				}
			}
		}
		fclose($handle);
		unlink($importFile);
		$errorMessage = '';


	} else {
		$errorMessage = "ContactListResponse ERROR : <br> ErrorMessage -> ".$ContactListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$ContactListResponse->{"Result"}->{"ExceptionMessage"};
		//$errorMessage = "ImportResponse ERROR : <br> ErrorMessage -> ".$ImportResponse->{"Result"}->{"ErrorMessage"};
		//echo $errorMessage."<BR>";
	}
	if ($errorMessage == '') {
		$result['success'] = true;
	} else {
		$result['success'] = false;
	}

	$result['data'] = $rows;

//	$FieldHeader = $FieldNames;  //fung reset header = fieldName
	$result['colNames'] = $FieldHeader;  
	$result['colModel'] = $colModel;

//	echo '__Header='.json_encode($FieldNames).'<br>\n';
//	echo '__Column='.json_encode($colModel).'<br>\n';
	return $result;

}


function loadContactArrayFilters($email,$userTicket,$folder,$account,$jsonFilters) {	

	$result = array(); 
	$rows = array();
	$colModel = array();

	$group = array();
	$group['name'] = 'id';
	$group['index'] = 'id';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;		
	$editoptions = array();
	$editoptions['readonly'] = 'readonly';
	$group['editoptions'] = $editoptions;
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'invdate';
	$group['index'] = 'invdate';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;	
	//$group['unformat'] = "js:pickDate";	
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'name';
	$group['index'] = 'name';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;		
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'invtime';
	$group['index'] = 'invtime';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;		
	$group['sorttype'] = 'date';			
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'tax';
	$group['index'] = 'tax';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;		
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'total';
	$group['index'] = 'total';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;		
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'type';
	$group['index'] = 'type';
	$group['width'] = 90;
	$group['editable'] = true;
	$group['viewable'] = true;	
	$group['edittype'] = "select";
	$editoptions = array();
	$editoptions['value'] = ':;promo:promo;sale:sale';
	$group['editoptions'] = $editoptions;
	$colModel[] = $group;

	$group = array();
	$group['name'] = 'note';
	$group['index'] = 'note';
	$group['width'] = -1;
	$group['editable'] = true;
	$group['viewable'] = true;		
	$group['edittype'] = "textarea";	
	$editoptions = array();
	$editoptions['rows'] = '2';
	$editoptions['cols'] = '20';
	$group['editoptions'] = $editoptions;
	$colModel[] = $group;	
	
	$FieldNames = array("Inv No", "Date", "Client", "Time", "Tax", "Total", "Type", "Notes");
	$FieldCount = sizeof($FieldNames);

	$group = array();
	$group['id'] = "1";
	//$group['invdate'] = "04/21/2007";
	$group['invdate'] = "04/21/2007";
	$group['name'] = "client 1";
	$group['invtime'] = "12:00 am";
	$group['tax'] = "10";
	$group['total'] = "1010";
	$group['type'] = "promo";
	$group['note'] = "test 1";					
	$rows[] = $group;

	$group = array();
	$group['id'] = "2";
	//$group['invdate'] = "03/22/2007";
	$group['invdate'] = "08/20/2016";
	$group['name'] = "client 2";
	$group['invtime'] = "10:53am";
	$group['tax'] = "20";
	$group['total'] = "2020";
	$group['type'] = "sale";
	$group['note'] = "test 2";					
	$rows[] = $group;

	$group = array();
	$group['id'] = "3";
	//$group['invdate'] = "02/21/2007";
	$group['invdate'] = "2007-02-21";
	$group['name'] = "client 3";
	$group['invtime'] = "3000";
	$group['tax'] = "30";
	$group['total'] = "3030";
	$group['type'] = "promo";
	$group['note'] = "test 3";					
	$rows[] = $group;
	
	$result['success'] = true;
	$result['data'] = $rows;
	$result['colNames'] = $FieldNames;
	$result['colModel'] = $colModel;

	subDate(); 

	return $result;

}

function subDate(){
//	$value = '08/20/2016 9:00 am'; 
	$value = '08/20/2016 1:00 pm'; 

	$value = str_replace("09:00 am","9:00:00",$value);
	$value = str_replace("09:30 pm","9:30:00",$value);
	$value = str_replace("10:00 am","10:00:00",$value);
	$value = str_replace("10:30 pm","10:30:00",$value);
	$value = str_replace("11:00 am","11:00:00",$value);
	$value = str_replace("11:30 pm","11:30:00",$value);
	$value = str_replace("12:00 am","12:00:00",$value);
	$value = str_replace("12:30 pm","12:30:00",$value);
	$value = str_replace("01:00 pm","13:00:00",$value);
	$value = str_replace("01:30 pm","13:30:00",$value);
	$value = str_replace("02:00 pm","14:00:00",$value);
	$value = str_replace("02:30 pm","14:30:00",$value);
	$value = str_replace("03:00 pm","15:00:00",$value);
	$value = str_replace("03:30 pm","15:30:00",$value);
	$value = str_replace("04:00 pm","16:00:00",$value);
	$value = str_replace("04:30 pm","16:30:00",$value);
	$value = str_replace("05:00 pm","17:00:00",$value);
	$value = str_replace("05:30 pm","17:30:00",$value);
	$value = str_replace("06:00 pm","18:00:00",$value);
	$value = str_replace("06:30 pm","18:30:00",$value);
	$value = str_replace("07:00 pm","19:00:00",$value);
	$value = str_replace("07:30 pm","19:30:00",$value);
	
//echo 'substring=['.$value.']'; 

	$adate = substr($value,0,10); 
	$atime = substr($value,11); 

//echo 'adate=['.$adate.']'; 
//echo 'atime=['.$atime.'].......'; 

}

function loadContactReportFilter($userTicket,$folder,$searchWord,$mode) {
	$Contact = "";
	$rows = array();
	$FieldNames = array("FirstName","LastName","Email","Phone","FollowUp","Appointment_Date","Sold_Lost","PURL");
	//echo "userTicket=$userTicket,searchWord=$searchWord,LastName=$LastName,opr1=$opr1,opr2=$opr2<br>";
	
	$Filter =  getReportFilter($searchWord,$mode);
	$ContactListRequest   = array
	(		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),	
		"FieldNames" => $FieldNames,
		"Filter" => $Filter,
		"OutputType" => 1,
	);
	$ContactListResponse = callService("contactservice/GetContactList", $ContactListRequest);
	$ErrorCode = $ContactListResponse->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {
		$Contacts = $ContactListResponse->{"Contacts"};		
		foreach ($Contacts as $chr) {
			$Contact .= chr($chr);
		}
		//echo "<br>Contact : $Contact <br>";
		$t = time();
		$importFile = $folder . '/temp/'.$t.'.csv';
		$FileName = basename($importFile); 
		//echo "<br>importFile : $importFile,FileName : $FileName <br>";
		file_put_contents( $importFile, $Contact);
		if (($handle = fopen($importFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row == 0) {
					$row++;
					$header = $data;
				} else {
					$d01 = @$data[0];
					$d02 = @$data[1];
					$d03 = @$data[2];
					$d04 = @$data[3];
					$d05 = @$data[4];
					$d06 = @$data[5];
					$d07 = @$data[6];
					$d08 = @$data[7];
					$group = array(					
						'FirstName'=>$d01,
						'LastName'=>$d02,
						'Email'=>$d03,
						'Phone'=>$d04,
						'Address'=>$d05,
						'City'=>$d06,
						'State'=>$d07,
						'Zip'=>$d08
					);					
					$rows[] = $group;					
					$row++;
				}
			}
		}
		fclose($handle);
		unlink($importFile);
	} else {
		$errorMessage = "ContactListResponse ERROR : <br> ErrorMessage -> ".$ContactListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$ContactListResponse->{"Result"}->{"ExceptionMessage"};
		//$errorMessage = "ImportResponse ERROR : <br> ErrorMessage -> ".$ImportResponse->{"Result"}->{"ErrorMessage"};
		//echo $errorMessage."<BR>";
	}
	return $rows;
}


function getReportFilter($searchWord,$mode) {
	$Filter = '';
	$email = $_SESSION['admin']['email'];
	$superadmin = $_SESSION['admin']['superadmin'];
	$JoinOperator = "";
	$CriteriaRow = "";
	$CriteriaRow1 = "";
	$row = 0;		
	if ($mode != '' && $mode != 'All' ) {  

		if($mode == 'Leads'){	//boolean		

			$opr1 = "From"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"completeDate\" Operator=\"$opr1\" Value=\"01/01/1900\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"completeDate\" Operator=\"$opr1\" Value=\"01/01/1900\" />";
			//$JoinOperator = "$row";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}
		}else if($mode == 'Incompletes'){ //date-time

			$opr1 = "Equal"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"completeDate\" Operator=\"$opr1\" Value=\"\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"completeDate\" Operator=\"$opr1\" Value=\"\" />";
			//$JoinOperator = "$row";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}

			$opr2 = "From"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"visitDate\" Operator=\"$opr2\" Value=\"01/01/1900\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"visitDate\" Operator=\"$opr2\" Value=\"01/01/1900\" />";

			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}

		}else if($mode == 'FollowUp'){

			$opr1 = "NotEqual"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"FollowUp\" Operator=\"$opr1\" Value=\"\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"FollowUp\" Operator=\"$opr1\" Value=\"\" />";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}

		}else if($mode == 'Appointment'){
			$opr1 = "From"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"Appointment_Date\" Operator=\"$opr1\" Value=\"01/01/1900\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"Appointment_Date\" Operator=\"$opr1\" Value=\"01/01/1900\" />";
			//$JoinOperator = "$row";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}

		}else if($mode == 'InProgress'){
			$opr1 = "Equal"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"Sold_Lost\" Operator=\"$opr1\" Value=\"In Progress\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"Sold_Lost\" Operator=\"$opr1\" Value=\"In Progress\" />";
			//$JoinOperator = "$row";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}
		}else if($mode == 'Sold'){
			$opr1 = "Equal"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"Sold_Lost\" Operator=\"$opr1\" Value=\"Sold\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"Sold_Lost\" Operator=\"$opr1\" Value=\"Sold\" />";
			//$JoinOperator = "$row";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}
		}else if($mode == 'Lost'){
			$opr1 = "Equal"; 
			$row++;
			$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"Sold_Lost\" Operator=\"$opr1\" Value=\"Lost\" <br>";
			$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"Sold_Lost\" Operator=\"$opr1\" Value=\"Lost\" />";
			//$JoinOperator = "$row";
			if ($JoinOperator == '') {
				$JoinOperator = "$row";
			} else {
				$JoinOperator = "$JoinOperator &amp; $row";
			}
		}
	}// if($mode)
		
	//search box above 
	if ($searchWord != '') {
		//FirstName
		$opr1 = "Contains"; 
		$row++;
		$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"FirstName\" Operator=\"$opr1\" Value=\"$searchWord\" <br>";
		$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"FirstName\" Operator=\"$opr1\" Value=\"$searchWord\" />";
		//$JoinOperator = "$row";
		if ($JoinOperator == '') {
			$JoinOperator = "( $row";
		} else {
			$JoinOperator = "$JoinOperator &amp; ( $row";
		}
	
		//LastName
		//$opr1 = "Contains"; 
		$row++;
		$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"LastName\" Operator=\"$opr1\" Value=\"$searchWord\" <br>";
		$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"LastName\" Operator=\"$opr1\" Value=\"$searchWord\" />";
		$JoinOperator = "$JoinOperator | $row";

		//Email
		//$opr1 = "Contains"; 
		$row++;
		$CriteriaRow1 .= "Criteria Row=\"$row\" Field=\"email\" Operator=\"$opr1\" Value=\"$searchWord\" <br>";
		$CriteriaRow .= "<Criteria Row=\"$row\" Field=\"email\" Operator=\"$opr1\" Value=\"$searchWord\" />";
		$JoinOperator = "$JoinOperator | $row )";
	}		

	echo 'JoinOperator = ['.$JoinOperator.']..............................CriteriaRow = ['.$CriteriaRow.']';

	if ($row > 0) {
		$Filter = "<Filter CriteriaJoinOperator=\"$JoinOperator\">$CriteriaRow</Filter>";
	} else {
		$Filter = "<Filter CriteriaJoinOperator=\"&amp;\" />";		
	}
	
	return $Filter;
}



/*
function loadContactArray($email,$userTicket,$folder,$account,$FirstName,$LastName,$emailData,$opr1,$opr2,$opr3) {
	$Contact = "";
	$result = array(); 
	$rows = array();

	$FieldNames = getContactFieldNames($email,$account);
	$colModel = array();
	
	
	$FieldCount = sizeof($FieldNames);
	for($i = 0; $i < $FieldCount;$i++) {
		
		$fname = $FieldNames[$i];
		
		$group = array();
		$group['name'] = $fname;
		$group['index'] = $fname;
		$group['width'] = 90;
		$group['editable'] = true;
		$group['viewable'] = true;
		
		
		
		
		$colModel[] = $group;

	}
	
	

	//echo "userTicket=$userTicket,emailData=$emailData,LastName=$LastName,opr1=$opr1,opr2=$opr2<br>";

	
	//$Filter =  getFilter($FirstName,$LastName,$emailData,$opr1,$opr2,$opr3);
	$Filter = "<Filter CriteriaJoinOperator=\"&amp;\" />";	
	$ContactListRequest   = array
	(
		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
	
		"FieldNames" => $FieldNames,
		"Filter" => $Filter,
		"OutputType" => 1,
	);
	$ContactListResponse = callService("contactservice/GetContactList", $ContactListRequest);
	$ErrorCode = $ContactListResponse->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {
		$Contacts = $ContactListResponse->{"Contacts"};


		
		foreach ($Contacts as $chr) {
			$Contact .= chr($chr);
		}

		

		//echo "<br>Contact : $Contact <br>";
		$t = time();
		$importFile = $folder . '/temp/'.$t.'.csv';
		$FileName = basename($importFile); 
		//echo "<br>importFile : $importFile,FileName : $FileName <br>";
		file_put_contents( $importFile, $Contact);
		

		if (($handle = fopen($importFile, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if ($row == 0) {
					$row++;
					$header = $data;



				} else {
					$group = array();
					for($i = 0; $i < $FieldCount;$i++) {
						$fname = $FieldNames[$i];
						//echo "fname = $fname<br>";
						$group[$fname] = @$data[$i];
					}				
					
					$rows[] = $group;

					
					$row++;
				}
			}
		}
		fclose($handle);
		unlink($importFile);
		$errorMessage = '';


	} else {
		$errorMessage = "ContactListResponse ERROR : <br> ErrorMessage -> ".$ContactListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$ContactListResponse->{"Result"}->{"ExceptionMessage"};
		//$errorMessage = "ImportResponse ERROR : <br> ErrorMessage -> ".$ImportResponse->{"Result"}->{"ErrorMessage"};
		//echo $errorMessage."<BR>";
	}
	if ($errorMessage == '') {
		$result['success'] = true;
	} else {
		$result['success'] = false;
	}
	$result['data'] = $rows;
	$result['colNames'] = $FieldNames;
	$result['colModel'] = $colModel;
	return $result;

}
*/

function loadImportName($userTicket,$folder,$FirstName,$LastName,$emailData,$opr1,$opr2,$opr3) {
	$rows = array();
	$ImportListRequest   = array
	(
		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
		"MaxRows" => 100000,		
		"StartRowIndex" => 1,
		"Status" => 2,
		"SearchString" => "",
		"SortExpression" => "",
	);
	$ImportListResponse = callService("contactservice/GetImportList", $ImportListRequest);
	$ErrorCode = $ImportListResponse->{"Result"}->{"ErrorCode"};
	//echo "ErrorCode = $ErrorCode<br>";
	//echo "ErrorMessage = ".$ImportListResponse->{"Result"}->{"ErrorMessage"}."<br>";
	//echo "ExceptionMessage = ".$ImportListResponse->{"Result"}->{"ExceptionMessage"}."<br>";
	if ($ErrorCode == "") {
		$ImportList = $ImportListResponse->{"ImportList"};		
		foreach ($ImportList as $x){
			//echo "Name = ".$ImportList->{'Name'}."<br>";
			$importName = $x->{'Name'};
			if ($importName != '') {
				$group = array(					
					'Name'=>$x->{'Name'}
				);
					
				$rows[] = $group;
			}
			
		}
		sort($rows);
	} else {
		$errorMessage = "ImportListResponse ERROR : <br> ErrorMessage -> ".$ImportListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$ImportListResponse->{"Result"}->{"ExceptionMessage"};
		
	}

	return $rows;

}


function getContentArray($xmlDoc) {
	$contentArray = null;
	$ContentVariable = $xmlDoc->getElementsByTagName( "ContentVariable" );		
	foreach( $ContentVariable as $ContentVariable )
	{
		$ContentVariablesName = $ContentVariable->getAttribute('Name');
		$ContentVariablesNameLower = strtolower($ContentVariablesName);
		
		if (0 === strpos($ContentVariablesNameLower, 'wiz_')) {
			$ContentValue = $ContentVariable->nodeValue;
			$contentArray[] = array($ContentVariablesName, $ContentValue);
		}			
	}

	return $contentArray;
}

function getAllContentArray($xmlDoc) {
	$contentArray = null;
	$ContentVariable = $xmlDoc->getElementsByTagName( "ContentVariable" );		
	foreach( $ContentVariable as $ContentVariable )
	{
		$ContentVariablesName = $ContentVariable->getAttribute('Name');
		$ContentVariablesNameLower = strtolower($ContentVariablesName);
		
		
		$ContentValue = $ContentVariable->nodeValue;
		$contentArray[] = array($ContentVariablesName, $ContentValue);
					
	}

	return $contentArray;
}

function getCampaignArray($xmlDoc) {
	$Campaign = $xmlDoc->getElementsByTagName( "Campaign" );
	foreach( $Campaign as $Campaign )
	{
		$CampaignID = $Campaign->getAttribute('Id');
		$CampaignName = $Campaign->getAttribute( "Name" );
		
		$outBoundArray = null;
		$micrositeArray = null;
		$CampaignElement = $Campaign->getElementsByTagName( "CampaignElement" );
		foreach( $CampaignElement as $CampaignElement )
		{
			$ScheduleArray = null;
			$domainArray = null;
			$Category = $CampaignElement->getAttribute( "Category" );
			$Type = $CampaignElement->getAttribute( "Type" );
			$ElementID = $CampaignElement->getAttribute('Id');
			$ElementName = $CampaignElement->getAttribute( "Name" );
			$ElementType = $CampaignElement->getAttribute( "Type" );
			$IsTrackingRequired = $CampaignElement->getAttribute('IsTrackingRequired');
			if ($Category == "Outbound") {
				
				$Schedules = $CampaignElement->getElementsByTagName( "Schedules" );
				
				foreach( $Schedules as $Schedules )
				{
					$Schedule = $Schedules->getElementsByTagName('Schedule');
					foreach( $Schedule as $Schedule )		
					{
						
						
						$ScheduleId = $Schedule->getAttribute('Id');

						$Subject = $Schedule->getElementsByTagName('Subject');
						foreach($Subject as $Subject)
						{
							$ScheduleArray[] = array($ScheduleId, $Subject->nodeValue);
						}
					}
				}
				
				$outBoundArray[] = array($ElementID, $Category, $ElementName, $ElementType, $ScheduleArray, $IsTrackingRequired);
			} else if ($Type == "Microsite") {
				$BaseUrlCollection = $CampaignElement->getElementsByTagName( "BaseUrlCollection" );
				foreach( $BaseUrlCollection as $BaseUrlCollection )
				{
					$BaseUrl = $BaseUrlCollection->getElementsByTagName('BaseUrl');
					foreach( $BaseUrl as $BaseUrl )		
					{						
						$CallbackId = $BaseUrl->getAttribute('Id');
						$CallbackName = $BaseUrl->getAttribute('URL');
						$domainArray[] = array($CallbackId, $CallbackName);
					}
				}				
				$micrositeArray[] = array($ElementID, $Category, $ElementName, $ElementType, $domainArray);
			} else {
				continue;
			}
			

		}
		$campaignArray[] = array($CampaignID, $CampaignName, $outBoundArray, $micrositeArray);
	}
	return $campaignArray;
}

function getxmlFile($programID) {
	$xmlFile = '';
	if (($programID == '1') || ($programID == '2')) {
		$xmlFile = getcwd().'/maml/VehicleEquityTemplate-orig.maml';
	} else if ($programID == '3') {
		$xmlFile = getcwd().'/maml/maml1.maml';
	} else if ($programID == '4') {
		$xmlFile = getcwd().'/maml/Metlife.maml';
	}
	return $xmlFile;
}

function GetDomainList($userTicket) {
	$DomainListArray = array();
	$DomainListRequest  = array
	(
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		)			
	);
	$DomainListResponse = callService("configurationservice/GetDomainList", $DomainListRequest);
	$ErrorCode = $DomainListResponse->{"Result"}->{"ErrorCode"};
	if ($ErrorCode == "") {
		$DomainList = $DomainListResponse->{"DomainList"};
		foreach ($DomainList as $x){
			$DomainListArray[] = array($x->{'ID'}, $x->{'Name'});
		}
		//sort($DomainListArray);
	} else {
		$errorMessage .= "<br>DomainListResponse ERROR : <br> ErrorMessage -> ".$DomainListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
			"ExceptionMessage : ".$DomainListResponse->{"Result"}->{"ExceptionMessage"};
	}
	return $DomainListArray;

}

function GetContactFieldList($userTicket){
	$rows = array();
	$GetContactFieldListRequest   = array
	(
		
		"Credentials" => array
		(
			"Ticket" => $userTicket        
		),
		"Type" => 0
	);
	$GetContactFieldListResponse = callService("contactservice/GetContactFieldList", $GetContactFieldListRequest);
	$ErrorCode = $GetContactFieldListResponse->{"Result"}->{"ErrorCode"};
	//echo "ErrorCode = $ErrorCode<br>";
	//echo "ErrorMessage = ".$ImportListResponse->{"Result"}->{"ErrorMessage"}."<br>";
	//echo "ExceptionMessage = ".$ImportListResponse->{"Result"}->{"ExceptionMessage"}."<br>";
	if ($ErrorCode == "") {
		$ImportList = $GetContactFieldListResponse->{"FieldList"};		
		foreach ($ImportList as $x){
			//echo "Name = ".$ImportList->{'Name'}."<br>";
			$importName = $x->{'Name'};
			if ($importName != '') {
				$group = array(					
					'Name'=>$x->{'Name'}
				);
					
				$rows[] = $group;
			}			
		}
		sort($rows);
	} else {
		$errorMessage = "GetContactFieldListResponse ERROR : <br> ErrorMessage -> ".$GetContactFieldListResponse->{"Result"}->{"ErrorMessage"}.'<br>'.
		"ExceptionMessage : ".$GetContactFieldListResponse->{"Result"}->{"ExceptionMessage"};

		//echo $errorMessage;		
	}
	return $rows;
}
