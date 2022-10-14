<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\File;
use App\Models\AffiliateId;
use App\Models\DepositAddress;
use App\Models\VerifyToken;
use App\Models\PasswordToken;
use App\Models\AffiliateUser;
use App\Models\Report;
use App\Models\WireHistory;
use App\Models\BankTemplate;
use App\Models\Security;
use App\Models\News;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Validator;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
	public $successStatus = 200;
	/**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function getWireHistory(Request $request, $id, $email){
    	$result = [];
    	if ($id === "all") {
    		// code...
    		$wirehistories = WireHistory::where("email", $email)->get();

    	} else {
    		// code...
    		$wirehistories = WireHistory::where("wireid", $id)->first();

    	}
    	
    	$result['data'] = $wirehistories;
    	$result['success'] = true;
		return ($result); 

    }
    public function getReport(Request $request, $email){
        $result = []; 
        $reports = Report::where("email", $email)->get();
        $result['data'] = $reports;
        $result['success'] = true;
        return $result;

    }
     public function getBankTemplate(Request $request, $email){
        $result = []; 
        $templates = BankTemplate::where("email", $email)->get();
        $result['data'] = $templates;
        $result['success'] = true;
        return $result; 

    }
    public function saveBankTemplate(Request $request, $email){
        $result = [];

        $template = BankTemplate::where("email", $email)->where("id", $request->id)->first();
        if (!$template){
            $template = new BankTemplate;
        }
        $template->email = $email;
        $template->template_name = $request->template_name;
        $template->beneficiary_name = $request->beneficiary_name;
        $template->bank_name = $request->bank_name;
        $template->bank_account_number = $request->bank_account_number;
        $template->bank_country = $request->bank_country;
        $template->swift_bic_code = $request->swift_bic_code;
        $template->reference_code = $request->reference_code;
        $template->save();
        $result['id'] = $template->id;
        $result['success'] = true;
        return $result; 

    }
    public function deleteBankTemplate(Request $request, $id){
        $template = BankTemplate::find($id)->delete();
        $result['success'] = true;
        return $result; 
    }
   public function getSecurity(Request $request, $email){
        $result = []; 
        $reports = Security::where("email", $email)->first();
        $result['data'] = $reports;
        $result['success'] = true;
        return $result;

    }
    public function changeSecurity(Request $request, $email){
        $result = []; 
        $report = Security::where("email", $email)->first();
        if (!$report){
            $report = new Security;
            $report->email = $email;
        }
        $report->status = $request->status;
        $report->login = $request->login;
        $report->withdraw = $request->withdraw;
        $report->request_wire = $request->request_wire;
        $report->code_from_app = $request->code_from_app;
        $report->save();
        $result['data'] = $report;
        $result['success'] = true;
        return $result;

    }
    public function getNews(Request $request){
        $result = []; 
        $news = News::get();
        $result['data'] = $news;
        $result['success'] = true;
        return $result;

    }
}
