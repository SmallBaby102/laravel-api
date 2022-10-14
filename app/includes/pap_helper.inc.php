<?php
require_once( app_path().'/includes/PostAffiliatePro/PapApi.class.php' );

define("MERCHANT_USERNAME", "info@cryptowire.vip"); 
define("MERCHANT_PASSWORD", "ipXVfB4d"); 
define("PAP_URL", "https://cryptowire.postaffiliatepro.com/scripts/server.php");
define("CAMPAIGN_ID", "89ab9105");
define("COMMISSION_TYPE_ID", "hd5273sd");

/*
* Login PAP
* @param $_pap_url : PAP URL
* @param $_username : username used to login PAP
* @param $_password : password used to login PAP
* @param $_type : login type ("merchant" or "affiliate")
* @return PAP login session on success, NULL on false
*/
function PapLogin($_pap_url, $_username, $_password, $_type){    

    try {

        if ($_type == "merchant") { //login as merchant
        	$_merchant_session = new Pap_Api_Session($_pap_url); 
	        if(!$_merchant_session->login($_username, $_password)) {
	            //die("Cannot login. Message: ".$_merchant_session->getMessage());
	            return;
	        }
	        return $_merchant_session;

        } else if ($_type == "affiliate") { //login as affiliate
        	$_aff_session = new Pap_Api_Session($_pap_url); 
	        if(!$_aff_session->login($_username,$_password, Pap_Api_Session::AFFILIATE)) {
	            //die("Cannot login. Message: ".$_aff_session->getMessage());
	            return;        
	            
	        }
	        return $_aff_session;
	        
        }
        
    } catch (Exception $e){
        //die('Error: '.$e->getMessage());
        return;
        
    }

}

/*
* Get information of an Affiliate by username
* @param $_username : username used to login PAP
* @param $_merchant_session : merchant session object
* @return an array of information of an Affiliate
*/
function GetInfoByUsername($_username, $_merchant_session) {
	//$affiliate_user = array('userid' => '', 'refid' => '', 'error' => '');
	$affiliate_user = array('username' => '', 'userid' => '', 'refid' => '', 'parentuserid' => '', 'first_name' => '', 'last_name' => '', 'error' => '');
	$pap_user_check_request = new Pap_Api_AffiliatesGrid($_merchant_session);
	//Filtering affiliate by username
	$pap_user_check_request->addFilter('username', Gpf_Data_Filter::EQUALS, $_username);

	// sets limit to 30 rows, offset to 0 (first row starts)
	$pap_user_check_request->setLimit(0, 30);

	// sets columns, use it only if you want retrieve other as default columns
	$pap_user_check_request->addParam('columns', new Gpf_Rpc_Array(array(array('id'), array('refid'), array('userid'), 
	array('username'), array('firstname'), array('lastname'), array('rstatus'), array('parentuserid'),
	array('dateinserted'), array('salesCount'), array('clicksRaw'), array('clicksUnique'))));

	// send request
	try {
		$pap_user_check_request->sendNow();
		// request was successful, get the grid result
		$grid = $pap_user_check_request->getGrid();

		// get recordset from the grid
		$pap_user_check_recordset = $grid->getRecordset();	
		
		if (!empty($pap_user_check_recordset)) {
			foreach($pap_user_check_recordset as $rec) {
				if ((trim($rec->get('userid')) != '')  && (trim($rec->get('refid')) != '')){					
					$affiliate_user['username'] = $rec->get('username');
					$affiliate_user['userid'] = $rec->get('userid');
					$affiliate_user['refid'] = $rec->get('refid');
					$affiliate_user['parentuserid'] = $rec->get('parentuserid');
					$affiliate_user['first_name'] = $rec->get('firstname');
					$affiliate_user['last_name'] = $rec->get('lastname');
					break;
				}
				
				//echo 'Affiliate userid: '.$rec->get('userid').', Affiliate name: '.$rec->get('firstname').' '.$rec->get('lastname'). ', refid = ' . $rec->get('refid') .'<br>';
			}
		}
	} catch(Exception $e) {
		//die("API call error: ".$e->getMessage());
		////_log("PapUserCheck::API call error: ".$e->getMessage());
		$affiliate_user['error'] = $e->getMessage();
		return $affiliate_user;
	}

	
	return $affiliate_user;
}

/*
* Get Affiliate by Referal ID
* @param $_refid : referal ID
* @param $_merchant_session : merchant session
* @return $affiliate_user object
*/
function GetUserIdByRefId($_refid, $_merchant_session) {
	$affiliate_user = array('userid' => '', 'username' => '', 'refid' => '', 'error' => '');
	$pap_user_check_request = new Pap_Api_AffiliatesGrid($_merchant_session);
	//Filtering affiliate with refid
	$pap_user_check_request->addFilter('refid', Gpf_Data_Filter::EQUALS, $_refid);

	// sets limit to 30 rows, offset to 0 (first row starts)
	$pap_user_check_request->setLimit(0, 30);

	// sets columns, use it only if you want retrieve other as default columns
	$pap_user_check_request->addParam('columns', new Gpf_Rpc_Array(array(array('id'), array('refid'), array('userid'), 
	array('username'), array('firstname'), array('lastname'), array('rstatus'), array('parentuserid'),
	array('dateinserted'), array('salesCount'), array('clicksRaw'), array('clicksUnique'))));

	// send request
	try {
		$pap_user_check_request->sendNow();
		// request was successful, get the grid result
		$grid = $pap_user_check_request->getGrid();

		// get recordset from the grid
		$pap_user_check_recordset = $grid->getRecordset();	
		
		if (!empty($pap_user_check_recordset)) {
			foreach($pap_user_check_recordset as $rec) {
				if ((trim($rec->get('userid')) != '')  && (trim($rec->get('username')) != '')){
					$affiliate_user['userid'] = $rec->get('userid');
					$affiliate_user['refid'] = $rec->get('refid');
					$affiliate_user['username'] = $rec->get('username');
					$affiliate_user['parentuserid'] = $rec->get('parentuserid');
					break;
				}				
			}
		}
	} catch(Exception $e) {
		//die("API call error: ".$e->getMessage());
		$affiliate_user['error'] = $e->getMessage();
		return $affiliate_user;
	}

	
	return $affiliate_user;
}
/*
* Get Affiliate by User ID
* @param $_refid : user ID
* @param $_merchant_session : merchant session
* @return $affiliate_user object
*/
function GetUserIdByUserId($_userid, $_merchant_session) {
	$affiliate_user = array('userid' => '', 'username' => '', 'refid' => '', 'error' => '');
	$pap_user_check_request = new Pap_Api_AffiliatesGrid($_merchant_session);
	//Filtering affiliate with refid
	$pap_user_check_request->addFilter('userid', Gpf_Data_Filter::EQUALS, $_userid);

	// sets limit to 30 rows, offset to 0 (first row starts)
	$pap_user_check_request->setLimit(0, 30);

	// sets columns, use it only if you want retrieve other as default columns
	$pap_user_check_request->addParam('columns', new Gpf_Rpc_Array(array(array('id'), array('refid'), array('userid'), 
	array('username'), array('firstname'), array('lastname'), array('rstatus'), array('parentuserid'),
	array('dateinserted'), array('salesCount'), array('clicksRaw'), array('clicksUnique'))));

	// send request
	try {
		$pap_user_check_request->sendNow();
		// request was successful, get the grid result
		$grid = $pap_user_check_request->getGrid();

		// get recordset from the grid
		$pap_user_check_recordset = $grid->getRecordset();	
		
		if (!empty($pap_user_check_recordset)) {
			foreach($pap_user_check_recordset as $rec) {
				if ((trim($rec->get('userid')) != '')  && (trim($rec->get('username')) != '')){
					$affiliate_user['userid'] = $rec->get('userid');
					$affiliate_user['refid'] = $rec->get('refid');
					$affiliate_user['username'] = $rec->get('username');
					$affiliate_user['parentuserid'] = $rec->get('parentuserid');
					break;
				}				
			}
		}
	} catch(Exception $e) {
		//die("API call error: ".$e->getMessage());
		$affiliate_user['error'] = $e->getMessage();
		return $affiliate_user;
	}

	
	return $affiliate_user;
}
?>