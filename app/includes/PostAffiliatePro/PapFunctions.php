<?php
function PapLogin($_username, $_password, $_type){    
	global $pap_url;
    try {
        //include_once API_FILE;
        if ($_type == "merchant") {
        	$_merchant_session = new Pap_Api_Session($pap_url); 
	        if(!$_merchant_session->login($_username, $_password)) {
	            //die("Cannot login. Message: ".$_merchant_session->getMessage());
	            return;
	        }
	        return $_merchant_session;

        } else if ($_type == "affiliate") {
        	$_aff_session = new Pap_Api_Session($pap_url); 
	        if(!$_aff_session->login($_username,$_password, Gpf_Api_Session::AFFILIATE)) {
	            //die("Cannot login. Message: ".$_aff_session->getMessage());
	            return;        
	            
	        }
	        
	        return $_aff_session;
	        
        }
        
    } catch (Exception $e){
        //die('Error: '.$e->getMessage());
        return;
        
        //$aff_res_obj['err'] = $e->getMessage();
        //return $aff_res_obj;
    }

}

function getCampaigns($merchant_session) {
	$ret_campaigns = array();
	///////////////////////////////////////////////
	$request = new Gpf_Rpc_GridRequest("Pap_Merchants_Campaign_CampaignsGrid", "getRows", $merchant_session);
	$request->setLimit(0, 100);
	// sets limit to 100 rows, offset to 0 (first row starts)

	//you can use general search
	//$request->addFilter('search', 'L', 'Campaign name');
	//sets columns, use it only if you want to retrieve other as default columns
	//$request->addParam('columns', new Gpf_Rpc_Array(array(array('id'), array('name'), array('commissionsdetails'), array('rstatus'), array('commissionsexist'), array('account'), array('dateinserted'), array('isdefault'), array('rorder'))));

	//send request
	try {
	    $request->sendNow();
	} catch(Exception $e) {
	    //die("getCampaigns::API call error: ".$e->getMessage());
	    //_log("API call error: ".$e->getMessage());
	    return "merchant_session_closed";
	}
	// request was successful, get the grid result
	$grid = $request->getGrid();
	// get recordset from the grid
	$recordset = $grid->getRecordset();
	$ret_campaigns = iterate_campaigns($recordset);

	$totalRecords = $grid->getTotalCount();
	$maxRecords = $recordset->getSize();
	/*
	if ($maxRecords != 0) {
	  $cycles = ceil($totalRecords / $maxRecords);
	  for($i=1; $i<$cycles; $i++) {
	      // now get next 100 records
	      $request->setLimit($i * $maxRecords, $maxRecords);
	      $request->sendNow();
	      $recordset = $request->getGrid()->getRecordset();
	      iterate($recordset);
	  }
	} else {
		//_log("maxRecords = 0");
	}
	*/
	
	return $ret_campaigns;

	
	///////////////////////////////////////////////
}

function iterate_campaigns($recordset) {
	$ret_campaigns = array();
    // iterate through the records
    foreach($recordset as $rec) {
    	unset($cur_campaign);
    	if ( strstr($rec->get('commissionsdetails'), "none active") === false ) {
    		$cur_campaign['name'] = $rec->get('name');
	    	$cur_campaign['commissions'] = $rec->get('commissionsdetails');
	    	$cur_campaign['status'] = $rec->get('rstatus');
	    	$cur_campaign['logourl'] = $rec->get('logourl');
	    	$cur_campaign['description'] = $rec->get('description');
	    	$cur_campaign['campaignid'] = $rec->get('campaignid');	    	
	    	$ret_campaigns[] = $cur_campaign;
	        //echo 'Campaign name: ' . $rec->get('name') . ' '. $rec->get('commissionsdetails') . '<br>';
	        //echo 'productID: ' . $rec->get('productid'). '<br>';
	        //echo 'ID: ' . $rec->get('campaignid'). '<br>';
	        //echo 'logo: ' . $rec->get('logourl'). '<br>';
	        //echo 'status: ' . $rec->get('rstatus'). '<br>';
	        //Uncomment this line to display/obtain also the campaign category name
	        // echo getCampaignCategory($rec->get('campaignid'), $session);
    	}
    	
    }
    //echo "campaigns_arr size : " . count($campaigns_arr) . "<br>";
    
    return $ret_campaigns;
}

function getBannerCodes($session,$_campaign_id=NULL,$htmlSpecialChars=NULL){
    //----------------------------------------------
    // get recordset with list of banners
    $request = new Gpf_Rpc_Gridrequest("Pap_Affiliates_Promo_BannersGrid","getRows",$session);
    // sets limit to 30 rows, offset to 0 (first row starts)
    $request->setLimit(0, 30);
    // Filter particular banner type(s); A = link banner, E =  promo email, F = flash, H = HTML, I = image, V = Simple PDF, T = Text-Link
    //$request->addFilter('rtype',Gpf_Data_Filter::EQUALS,'E'); 
    //$request->addFilter('id',Gpf_Data_Filter::EQUALS,'11110001');  // use this for filtering exact banner ID
    $request->addFilter('campaignid',Gpf_Data_Filter::EQUALS,$_campaign_id);  // use this for filtering exact banner ID
    // send request
    try {
      $request->sendNow();
    } catch(Exception $e) {
      //die("getBannerCodes::API call error: ".$e->getMessage());
      return "affiliate_session_closed";
    }
    // request was successful, get the grid result
    $grid = $request->getGrid();
    $totalRecords = $grid->getTotalCount();
    // get recordset from the grid
    $recordset = $grid->getRecordset();
    if ($totalRecords>0){
        //$bannerCodes = array();
        $bannerList = array();
        // iterate through the records
        foreach($recordset as $rec) {                    
            
            //banner id
            $banner_info['bannerid'] = $rec->get('bannerid');
            
            //banner name
            $banner_info['name'] = $rec->get('name');
            
            //banner target
            $banner_info['target'] = $rec->get('target');
            
            //banner destinationurl
            $banner_info['destinationurl'] = $rec->get('destinationurl');
            
            //banner destinationurl
            $banner_info['rtype'] = $rec->get('rtype');
            
            //banner code
            if(isset($htmlSpecialChars) && $htmlSpecialChars == 'Y'){
                //$bannerCodes[] = htmlspecialchars($rec->get('bannercode'));
                $banner_info['bannercode_escape'] = htmlspecialchars($rec->get('bannercode'));
                $banner_info['bannercode'] = $rec->get('bannercode');
            } else {
                ////$bannerCodes[] = $rec->get('bannercode');
                //$banner_info['bannercode'] = $rec->get('bannercode');
            }
            
            $bannerList[] = $banner_info;
        }
        /*
        //----------------------------------------------
        // in case there are more than 30 records in total,
        // we should load and display the rest of the records
        // via the cycle below
        $maxRecords = $recordset->getSize();
        if ($maxRecords>0){
            $cycles = ceil($totalRecords / $maxRecords);
            for($i=1; $i<$cycles; $i++) {
              // now get next 30 records
                $request->setLimit($i * $maxRecords, $maxRecords);
                $request->sendNow();
                $recordset = $request->getGrid()->getRecordset();
                // iterate through the records
                foreach($recordset as $rec) {
                    if(isset($htmlSpecialChars) && $htmlSpecialChars == 'Y'){
                        $bannerCodes[] = htmlspecialchars($rec->get('bannercode'));
                    } else {
                        $bannerCodes[] = $rec->get('bannercode');
                    }
                }      
            }
        }       
        */
         
        return $bannerList;
    }
}

function getCampaignCategory($campaignid, $session) {
    $request2 = new Gpf_Rpc_GridRequest("Pap_Features_CampaignsCategories_CampaignInCategoriesGrid", "getRows", $session);
    $request2->addFilter("campaignid","=",$campaignid);
    // send request
    try {
        $request2->sendNow();
    } catch(Exception $e) {
        die("API call error: ".$e->getMessage());
    }
    // request was successful, get the grid result
    $grid2 = $request2->getGrid();
    // get recordset from the grid
    $recordset2 = $grid2->getRecordset();
    foreach($recordset2 as $rec2) {
        return $rec2->get('name');
    }
}
?>