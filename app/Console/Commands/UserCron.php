<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
require_once( app_path().'/includes/pap_helper.inc.php' );
class UserCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User command Executed Successfully!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \Log::info("User Cron execution!");
        $this->info('User:User Command is working fine!');

        $result = []; 
        $raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
        if ((!is_null($raw_pap_merchant_login_obj)) && (!empty($raw_pap_merchant_login_obj))) {
            
            $trans_req = new \Pap_Api_AffiliatesGrid($raw_pap_merchant_login_obj);
            // set filter
            // $trans_req->addFilter('dateinserted', \Gpf_Data_Filter::DATERANGE_IS, \Gpf_Data_Filter::RANGE_THIS_YEAR);
            // $trans_req->addFilter('parentuserid', \Gpf_Data_Filter::EQUALS, $pap_affiliate_obj['userid']);
            // list here all columns which you want to read from grid
            $trans_req->addParam('columns', new \Gpf_Rpc_Array(array(array('id'), array('refid'), array('userid'), 
                                array('username'), array('firstname'), array('lastname'), array('rstatus'), array('parentuserid'),
                                array('dateinserted'), array('salesCount'), array('clicksRaw'), array('clicksUnique'))));
            //$trans_req->addFilter('orderid', Gpf_Data_Filter::EQUALS, 'ORD_123');
            $trans_req->setLimit(0, 100);
            $trans_req->setSorting('dateinserted', false);
            
            try {
                 
                    $trans_req->sendNow();
                    $grid = $trans_req->getGrid();
                    $recordset = $grid->getRecordset(); 
                    // _log("Total number of records: ".$grid->getTotalCount());
                    // _log("Number of returned records: ".$recordset->getSize());
                    // iterate through the records
                    $commission_arr = []; 
                    foreach($recordset as $rec) {
                        $commission_item = array();

                        $commission_item['userid'] = $rec->get('userid');
                        $commission_item['refid'] = $rec->get('refid');
                        $commission_item['dateinserted'] = date('Y-m-d H:i:s',strtotime('+0 hour',strtotime($rec->get('dateinserted'))));;
                        $commission_item['username'] = $rec->get('username');
                        $commission_item['parentuserid'] = $rec->get('parentuserid');
                        $commission_item['firstname'] = $rec->get('firstname');
                        $commission_item['lastname'] = $rec->get('lastname');
                        $currentTime = Carbon::now();
                        $commission_item['created_at'] = $currentTime;
                        $commission_item['updated_at'] = $currentTime;
                
                        // set type
                        $users = DB::table('affiliate_users')->where('username', $rec->get("username"))->first();
                        if (!$users){
                            $commission_arr[] = $commission_item;

                        }
                    }
                    //----------------------------------------------
                    // in case there are more than 100 records total
                    // we should load and display the rest of the records
                    // in the cycle
                    $totalRecords = $grid->getTotalCount();
                    $maxRecords = $recordset->getSize();
                    if ($maxRecords > 0) {
                        $cycles = ceil($totalRecords / $maxRecords);
                        for($i=1; $i<$cycles; $i++) {
                            // now get next 100 records
                            $trans_req->setLimit($i * $maxRecords, $maxRecords);
                            $trans_req->sendNow();
                            $recordset = $trans_req->getGrid()->getRecordset();
                            // iterate through the records
                            foreach($recordset as $rec) {
                                $commission_item = array();
                                $commission_item['userid'] = $rec->get('userid');
                                $commission_item['refid'] = $rec->get('refid');
                                $commission_item['dateinserted'] = date('Y-m-d H:i:s',strtotime('+0 hour',strtotime($rec->get('dateinserted'))));;
                                $commission_item['username'] = $rec->get('username');
                                $commission_item['parentuserid'] = $rec->get('parentuserid');
                                $commission_item['firstname'] = $rec->get('firstname');
                                $commission_item['lastname'] = $rec->get('lastname');
                                $currentTime = Carbon::now();
                                $commission_item['created_at'] = $currentTime;
                                $commission_item['updated_at'] = $currentTime;
                                $users = DB::table('affiliate_users')->where('username', $rec->get("username"))->first();
                                if (!$users){
                                    $commission_arr[] = $commission_item;

                                }
                            }
                        }
                    }

                    $result['commission_arr'] = $commission_arr;
                    DB::table("affiliate_users")->insert($commission_arr);
                    $result['success'] = true;
                
                } catch(Exception $e) {
                    $result['success'] = false;
                }
            } else {
                $result['success'] = false;
            }

        return 0;
    }
}
