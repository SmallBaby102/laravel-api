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

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Validator;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require_once( app_path().'/includes/pap_helper.inc.php' );
 function uuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
class UserController extends Controller
{
public $successStatus = 200;
/**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    
    public function login() {
        if (
            Auth::attempt([
                'email' => request('email'),
                'password' => request('password')
            ])
        ) {
            $user = Auth::user();
            if ($user->is_email_verified) {
                $userRole = $user->role()->first();
                if ($userRole) {
                    $this->scope = $userRole->role;
                } else {
                    $this->scope = "basic";
                }

                $token = $user->createToken($user->email.'-'.now(), [$this->scope])->accessToken;

                $data['user'] = [ "user" => $user, "role" => $this->scope ];
                $data['accessToken'] = $token;

                $user->lastLogin = date('Y-m-d h:i:s a', time());
                $user->save();

                return response()->json(['success' => true, 'data' => $data]);
            } else {
                return response()->json(['success' => false, 'message' => 'Please verify your email.']);
            }
        } else {
            return response()->json(['success' => false, 'message'=>'Unauthorised'], 401);
        }
    }

    public function auto_login() {
        $user = Auth::user();
        if ($user->is_email_verified) {
            $userRole = $user->role()->first();
            if (!$userRole) $userRole = 'basic';
            return response()->json(['success' => true, 'user' => $user, 'role' => $userRole]);
        } else {
            return response()->json(['success' => false, 'message' => 'Please verify your email.']);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // return response()->json(['success' => true]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['is_email_verified'] = 0;
        $input['profile_complete_step'] = 0;

        $user = User::create($input);
        $token = Str::random(64);

        VerifyToken::create([
            'user_id' => $user->id,
            'token' => $token
        ]);

        $msg1 = 'You are receiving this email because you have registered on our site.';
        $msg2 = 'Click the link below to active your TokenWiz account.';
        $bnTxt = 'Verify Email';
        $url = 'user.verify';
        $data = array('name' => '', 'token' => $token, 'msg1' => $msg1, 'msg2' => $msg2, 'bnTxt' => $bnTxt, 'url' => $url);
        Mail::send('mail', $data, function ($message)  use ($user) {
            $message->to($user->email, 'BTC Exchange')->subject('Please verify your email.');
            $message->from('noreply@btc.exchange.com', 'BTC Exchange');
        });

        return response()->json(['success' => true]);
    }
   
    /**
     * Signup api
     *
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
       
        $result['success'] = false;
        $input = $request->all();
        $data['email'] = $input['email'];
        $data['firstname'] = $input['firstname'];
        $data['lastname'] = $input['lastname'];
        $data['password'] = bcrypt($input['password']);
        $data['country'] = $input['country'];
        $data['issue_country'] = $input['country'];
        if ($input['country'] == 'United States') {
			$country_code = $input["country"]."('+1')";	
			} else if ( $input["country"] == 'Afghanistan') {
				$country_code = $input["country"]."('+93')";	
			} else if ( $input["country"] == 'Albania') {
				$country_code = $input["country"]."('+355')";	
			} else if ( $input["country"] == 'Algeria') {
				$country_code = $input["country"]."('+213')";	
			} else if ( $input["country"] == 'Andorra') {
				$country_code = $input["country"]."('+376')";	
			} else if ( $input["country"] == 'Angola') {
				$country_code = $input["country"]."('+244')";	
			} else if ( $input["country"] == 'Antigua and Barbuda') {
				$country_code = $input["country"]."('+268')";	
			} else if ( $input["country"] == 'Argentina') {
				$country_code = $input["country"]."('+54')";	
			} else if ( $input["country"] == 'Armenia') {
				$country_code = $input["country"]."('+374')";	
			} else if ( $input["country"] == 'Australia') {
				$country_code = $input["country"]."('+63')";	
			} else if ( $input["country"] == 'Austria') {
				$country_code = $input["country"]."('+43')";	
			} else if ( $input["country"] == 'Azerbaijan') {
				$country_code = $input["country"]."('+994')";	
			} else if ( $input["country"] == 'Bahamas') {
				$country_code = $input["country"]."('+242')";	
			} else if ( $input["country"] == 'Bahrain') {
				$country_code = $input["country"]."('+973')";	
			} else if ( $input["country"] == 'Bangladesh') {
				$country_code = $input["country"]."('+880')";	
			} else if ( $input["country"] == 'Barbados') {
				$country_code = $input["country"]."('+246')";	
			} else if ( $input["country"] == 'Belarus') {
				$country_code = $input["country"]."('+375')";	
			} else if ( $input["country"] == 'Belgium') {
				$country_code = $input["country"]."('+32')";	
			} else if ( $input["country"] == 'Belize') {
				$country_code = $input["country"]."('+501')";	
			} else if ( $input["country"] == 'Benin') {
				$country_code = $input["country"]."('+229')";	
			} else if ( $input["country"] == 'Bhutan') {
				$country_code = $input["country"]."('+975')";	
			} else if ( $input["country"] == 'Bolivia') {
				$country_code = $input["country"]."('+591')";	
			} else if ( $input["country"] == 'Bosnia and Herzegovina') {
				$country_code = $input["country"]."('387')";	
			} else if ( $input["country"] == 'Brazil') {
				$country_code = $input["country"]."('+55')";	
			} else if ( $input["country"] == 'Brunei') {
				$country_code = $input["country"]."('+673')";	
			} else if ( $input["country"] == 'Bulgaria') {
				$country_code = $input["country"]."('+359')";	
			} else if ( $input["country"] == 'Burkina Faso') {
				$country_code = $input["country"]."('+226')";	
			} else if ( $input["country"] == 'Burundi') {
				$country_code = $input["country"]."('+257')";	
			} else if ( $input["country"] == 'Cabo Verde') {
				$country_code = $input["country"]."('+238')";	
			} else if ( $input["country"] == 'Cambodia') {
				$country_code = $input["country"]."('+855')";	
			} else if ( $input["country"] == 'Cameroon') {
				$country_code = $input["country"]."('+237')";	
			} else if ( $input["country"] == 'Canada') {
				$country_code = $input["country"]."('+1_ca')";	
			} else if ( $input["country"] == 'Central African Republic') {
				$country_code = $input["country"]."('+236')";	
			} else if ( $input["country"] == 'Chad') {
				$country_code = $input["country"]."('+235')";	
			} else if ( $input["country"] == 'Chile') {
				$country_code = $input["country"]."('+56')";	
			} else if ( $input["country"] == 'China') {
				$country_code = $input["country"]."('+86')";	
			} else if ( $input["country"] == 'Colombia') {
				$country_code = $input["country"]."('+57')";	
			} else if ( $input["country"] == 'Comoros') {
				$country_code = $input["country"]."('+269')";	
			} else if ( $input["country"] == 'Congo') {
				$country_code = $input["country"]."('+242')";	
			} else if ( $input["country"] == 'Costa Rica') {
				$country_code = $input["country"]."('+506')";	
			} else if ( $input["country"] == "Cote d'Ivoire") {
				$country_code = $input["country"]."('+225')";	
			} else if ( $input["country"] == 'Croatia') {
				$country_code = $input["country"]."('+385')";	
			} else if ( $input["country"] == 'Croatia') {
				$country_code = $input["country"]."('+385')";	
			} else if ( $input["country"] == 'Cuba') {
				$country_code = $input["country"]."('+53')";	
			} else if ( $input["country"] == 'Cyprus') {
				$country_code = $input["country"]."('+357')";	
			} else if ( $input["country"] == 'Czech Republic') {
				$country_code = $input["country"]."('+420')";	
			} else if ( $input["country"] == 'Denmark') {
				$country_code = $input["country"]."('+45')";	
			} else if ( $input["country"] == 'Djibouti') {
				$country_code = $input["country"]."('+253')";	
			} else if ( $input["country"] == 'Dominica') {
				$country_code = $input["country"]."('+767')";	
			} else if ( $input["country"] == 'Dominican Republic') {
				$country_code = $input["country"]."('+809')";	
			} else if ( $input["country"] == 'East Timor') {
				$country_code = $input["country"]."('+670')";	
			} else if ( $input["country"] == 'Egypt') {
				$country_code = $input["country"]."('+20')";	
			} else if ( $input["country"] == 'El Salvador') {
				$country_code = $input["country"]."('+503')";	
			} else if ( $input["country"] == 'Equatorial Guinea') {
				$country_code = $input["country"]."('+240')";	
			} else if ( $input["country"] == 'Eritrea') {
				$country_code = $input["country"]."('+291')";	
			} else if ( $input["country"] == 'Estonia') {
				$country_code = $input["country"]."('+372')";	
			} else if ( $input["country"] == 'Fiji') {
				$country_code = $input["country"]."('+679')";	
			} else if ( $input["country"] == 'Finland') {
				$country_code = $input["country"]."('+358')";	
			} else if ( $input["country"] == 'France') {
				$country_code = $input["country"]."('+33')";	
			} else if ( $input["country"] == 'Gabon') {
				$country_code = $input["country"]."('+241')";	
			} else if ( $input["country"] == 'Gambia') {
				$country_code = $input["country"]."('+220')";	
			} else if ( $input["country"] == 'Georgia') {
				$country_code = $input["country"]."('+995')";	
			} else if ( $input["country"] == 'Germany') {
				$country_code = $input["country"]."('+49')";	
			} else if ( $input["country"] == 'Ghana') {
				$country_code = $input["country"]."('+233')";	
			} else if ( $input["country"] == 'Grenada') {
				$country_code = $input["country"]."('+473')";	
			} else if ( $input["country"] == 'Guatemala') {
				$country_code = $input["country"]."('+502')";	
			} else if ( $input["country"] == 'Guinea') {
				$country_code = $input["country"]."('+224')";	
			} else if ( $input["country"] == 'Guinea-Bissau') {
				$country_code = $input["country"]."('+245')";	
			} else if ( $input["country"] == 'Guyana') {
				$country_code = $input["country"]."('+592')";	
			} else if ( $input["country"] == 'Haiti') {
				$country_code = $input["country"]."('+509')";	
			} else if ( $input["country"] == 'Honduras') {
				$country_code = $input["country"]."('+504')";	
			} else if ( $input["country"] == 'Hungary') {
				$country_code = $input["country"]."('+36')";	
			} else if ( $input["country"] == 'Iceland') {
				$country_code = $input["country"]."('+354')";	
			} else if ( $input["country"] == 'India') {
				$country_code = $input["country"]."('+91')";	
			} else if ( $input["country"] == 'Indonesia') {
				$country_code = $input["country"]."('+62')";	
			} else if ( $input["country"] == 'Iraq') {
				$country_code = $input["country"]."('+964')";	
			} else if ( $input["country"] == 'Ireland') {
				$country_code = $input["country"]."('+353')";	
			} else if ( $input["country"] == 'Israel') {
				$country_code = $input["country"]."('+972')";	
			} else if ( $input["country"] == 'Italy') {
				$country_code = $input["country"]."('+39')";	
			} else if ( $input["country"] == 'Jamaica') {
				$country_code = $input["country"]."('+876')";	
			} else if ( $input["country"] == 'Japan') {
				$country_code = $input["country"]."('+81')";	
			} else if ( $input["country"] == 'Jordan') {
				$country_code = $input["country"]."('+962')";	
			} else if ( $input["country"] == 'Kazakhstan') {
				$country_code = $input["country"]."('+7_kaz')";	
			} else if ( $input["country"] == 'Kenya') {
				$country_code = $input["country"]."('+254')";	
			} else if ( $input["country"] == 'Kiribati') {
				$country_code = $input["country"]."('+686')";	
			} else if ( $input["country"] == 'South Korea') {
				$country_code = $input["country"]."('+82')";	
			} else if ( $input["country"] == 'Kosovo') {
				$country_code = $input["country"]."('+383')";	
			} else if ( $input["country"] == 'Kuwait') {
				$country_code = $input["country"]."('+965')";	
			} else if ( $input["country"] == 'Kyrgyzstan') {
				$country_code = $input["country"]."('+996')";	
			} else if ( $input["country"] == 'Laos') {
				$country_code = $input["country"]."('+856')";	
			} else if ( $input["country"] == 'Latvia') {
				$country_code = $input["country"]."('+371')";	
			} else if ( $input["country"] == 'Lebanon') {
				$country_code = $input["country"]."('+961')";	
			} else if ( $input["country"] == 'Lesotho') {
				$country_code = $input["country"]."('+266')";	
			} else if ( $input["country"] == 'Liberia') {
				$country_code = $input["country"]."('+231')";	
			} else if ( $input["country"] == 'Libya') {
				$country_code = $input["country"]."('+218')";	
			} else if ( $input["country"] == 'Liechtenstein') {
				$country_code = $input["country"]."('+423')";	
			} else if ( $input["country"] == 'Lithuania') {
				$country_code = $input["country"]."('+370')";	
			} else if ( $input["country"] == 'Luxembourg') {
				$country_code = $input["country"]."('+352')";	
			} else if ( $input["country"] == 'Macedonia') {
				$country_code = $input["country"]."('+389')";	
			} else if ( $input["country"] == 'Madagascar') {
				$country_code = $input["country"]."('+261')";	
			} else if ( $input["country"] == 'Malawi') {
				$country_code = $input["country"]."('+265')";	
			} else if ( $input["country"] == 'Maldives') {
				$country_code = $input["country"]."('+960')";	
			} else if ( $input["country"] == 'Mali') {
				$country_code = $input["country"]."('+223')";	
			} else if ( $input["country"] == 'Malta') {
				$country_code = $input["country"]."('+356')";	
			} else if ( $input["country"] == 'Marshall Islands') {
				$country_code = $input["country"]."('+692')";	
			} else if ( $input["country"] == 'Mauritania') {
				$country_code = $input["country"]."('+222')";	
			} else if ( $input["country"] == 'Mauritius') {
				$country_code = $input["country"]."('+230')";	
			} else if ( $input["country"] == 'Mexico') {
				$country_code = $input["country"]."('+52')";	
			} else if ( $input["country"] == 'Federated States of Micronesia') {
				$country_code = $input["country"]."('+691')";	
			} else if ( $input["country"] == 'Moldova') {
				$country_code = $input["country"]."('+373')";	
			} else if ( $input["country"] == 'Monaco') {
				$country_code = $input["country"]."('+377')";	
			} else if ( $input["country"] == 'Mongolia') {
				$country_code = $input["country"]."('+976')";	
			} else if ( $input["country"] == 'Montenegro') {
				$country_code = $input["country"]."('+382')";	
			} else if ( $input["country"] == 'Mozambique') {
				$country_code = $input["country"]."('+258')";	
			} else if ( $input["country"] == 'Myanmar') {
				$country_code = $input["country"]."('+95')";	
			} else if ( $input["country"] == 'Namibia') {
				$country_code = $input["country"]."('+264')";	
			} else if ( $input["country"] == 'Nauru') {
				$country_code = $input["country"]."('+674')";	
			} else if ( $input["country"] == 'Nepal') {
				$country_code = $input["country"]."('+977')";	
			} else if ( $input["country"] == 'Netherlands') {
				$country_code = $input["country"]."('+31')";	
			} else if ( $input["country"] == 'New Zealand') {
				$country_code = $input["country"]."('+64')";	
			} else if ( $input["country"] == 'Nicaragua') {
				$country_code = $input["country"]."('+505')";	
			} else if ( $input["country"] == 'Niger') {
				$country_code = $input["country"]."('+227')";	
			} else if ( $input["country"] == 'Nigeria') {
				$country_code = $input["country"]."('+234')";	
			} else if ( $input["country"] == 'Norway') {
				$country_code = $input["country"]."('+47')";	
			} else if ( $input["country"] == 'Oman') {
				$country_code = $input["country"]."('+968')";	
			} else if ( $input["country"] == 'Palau') {
				$country_code = $input["country"]."('+680')";	
			} else if ( $input["country"] == 'Panama') {
				$country_code = $input["country"]."('+507')";	
			} else if ( $input["country"] == 'Papua New Guinea') {
				$country_code = $input["country"]."('+675')";	
			} else if ( $input["country"] == 'Paraguay') {
				$country_code = $input["country"]."('+595')";	
			} else if ( $input["country"] == 'Peru') {
				$country_code = $input["country"]."('+51')";	
			} else if ( $input["country"] == 'Philippines') {
				$country_code = $input["country"]."('+63')";	
			} else if ( $input["country"] == 'Poland') {
				$country_code = $input["country"]."('+48')";	
			} else if ( $input["country"] == 'Portugal') {
				$country_code = $input["country"]."('+351')";	
			} else if ( $input["country"] == 'Qatar') {
				$country_code = $input["country"]."('+974')";	
			} else if ( $input["country"] == 'Romania') {
				$country_code = $input["country"]."('+40')";	
			} else if ( $input["country"] == 'Russia') {
				$country_code = $input["country"]."('+7')";	
			} else if ( $input["country"] == 'Rwanda') {
				$country_code = $input["country"]."('+250')";	
			} else if ( $input["country"] == 'Saint Kitts and Nevis') {
				$country_code = $input["country"]."('+869')";	
			} else if ( $input["country"] == 'Saint Lucia') {
				$country_code = $input["country"]."('+758')";	
			} else if ( $input["country"] == 'Saint Vincent and the Grenadines') {
				$country_code = $input["country"]."('+784')";	
			} else if ( $input["country"] == 'Samoa') {
				$country_code = $input["country"]."('+685')";	
			} else if ( $input["country"] == 'San Marino') {
				$country_code = $input["country"]."('+378')";	
			} else if ( $input["country"] == 'Singapore') {
				$country_code = $input["country"]."('+65')";	
			} else if ( $input["country"] == 'Slovakia') {
				$country_code = $input["country"]."('+421')";	
			} else if ( $input["country"] == 'Slovenia') {
				$country_code = $input["country"]."('+386')";	
			} else if ( $input["country"] == 'Solomon Islands') {
				$country_code = $input["country"]."('+677')";	
			} else if ( $input["country"] == 'Somalia') {
				$country_code = $input["country"]."('+252')";	
			} else if ( $input["country"] == 'South Africa') {
				$country_code = $input["country"]."('+27')";	
			} else if ( $input["country"] == 'Spain') {
				$country_code = $input["country"]."('+34')";	
			} else if ( $input["country"] == 'Sudan') {
				$country_code = $input["country"]."('+249')";	
			} else if ( $input["country"] == 'Suriname') {
				$country_code = $input["country"]."('+597')";	
			} else if ( $input["country"] == 'Swaziland') {
				$country_code = $input["country"]."('+268_swa')";	
			} else if ( $input["country"] == 'Sweden') {
				$country_code = $input["country"]."('+46')";	
			} else if ( $input["country"] == 'Switzerland') {
				$country_code = $input["country"]."('+41')";	
			} else if ( $input["country"] == 'Taiwan') {
				$country_code = $input["country"]."('+886')";	
			} else if ( $input["country"] == 'Tajikistan') {
				$country_code = $input["country"]."('+992')";	
			} else if ( $input["country"] == 'Tanzania') {
				$country_code = $input["country"]."('+255')";	
			} else if ( $input["country"] == 'Thailand') {
				$country_code = $input["country"]."('+66')";	
			} else if ( $input["country"] == 'Togo') {
				$country_code = $input["country"]."('+228')";	
			} else if ( $input["country"] == 'Tonga') {
				$country_code = $input["country"]."('+676')";	
			} else if ( $input["country"] == 'Turkey') {
				$country_code = $input["country"]."('+90')";	
			} else if ( $input["country"] == 'Turkmenistan') {
				$country_code = $input["country"]."('+993')";	
			} else if ( $input["country"] == 'Tuvalu') {
				$country_code = $input["country"]."('+688')";	
			} else if ( $input["country"] == 'Uganda') {
				$country_code = $input["country"]."('+256')";	
			} else if ( $input["country"] == 'Ukraine') {
				$country_code = $input["country"]."('+380')";	
			} else if ( $input["country"] == 'United Arab Emirates') {
				$country_code = $input["country"]."('+971')";	
			} else if ( $input["country"] == 'United Kingdom') {
				$country_code = $input["country"]."('+44')";	
			} else if ( $input["country"] == 'Uruguay') {
				$country_code = $input["country"]."('+598')";	
			} else if ( $input["country"] == 'Uzbekistan') {
				$country_code = $input["country"]."('+998')";	
			} else if ( $input["country"] == 'Vanuatu') {
				$country_code = $input["country"]."('+678')";	
			} else if ( $input["country"] == 'Vatican City') {
				$country_code = $input["country"]."('+379')";	
			} else if ( $input["country"] == 'Vietnam') {
				$country_code = $input["country"]."('+84')";	
			} else if ( $input["country"] == 'Zambia') {
				$country_code = $input["country"]."('+260')";	
		}
        $data['country_code'] = $country_code;
        $user = User::where("email", $data['email'])->first();
        if (!$user) {
            # code...
            $user = new User;
            $user->email = $data['email'];
            $user->firstname = $data['firstname'];
            $user->lastname = $data['lastname'];
            $user->country = $data['country'];
            $user->issue_country = $data['issue_country'];
            $user->country_code = $country_code;
            $user->save();

            $rawtext = "<h3>New user was registered.<h3/><br/> <b>Email<b>: ". $data['email']." <br/> Firstname: ". $data['firstname']."<br/>Lastname: ".$data['lastname']." <br/> Country: ".$data['country'];
            Mail::send([], [], function ($message) use ($rawtext) {
                $message->to("info@cryptowire.vip")->subject('New user was registered.'); //info@cryptowire.vip
                $message->from('info@cryptowire.vip', 'Cryptowire');
                $message->setBody($rawtext, 'text/html');
            });
            $result["data"] = $user;
            $result['success'] = true;
        }
        // This part is that signup to PostAffiliatepro
        $email = $input['email'];
        $firstname = $input['firstname'];
        $lastname = $input['lastname'];
        $password = $input['password'];
        $visitor_id = $input['papCookie']; //this was prepared by step (1.c) above
        $refid = $input['AffRefId'];
        $pap_visitorId = $input['pap_visitorId'];
        
        //PAP is automatically generates this PAPVisitorId in the website cookies.
        // $pap_visitorId = (isset($_COOKIE['PAPVisitorId']))?htmlentities($_COOKIE['PAPVisitorId'], 3, 'UTF-8'):"";
        //We need visitor id for sign up API, so we have to make sure that we can always get this value (sometimes papCookie above is null for unknown reason, perhaps this is PAP bug)
        if ($visitor_id == '') {
            if ($pap_visitorId != '') {
                $visitor_id = $pap_visitorId;
            }
        }
        //get Affiliate Referal ID we have set at step (1.b) above
        // $refid = isset($_COOKIE['AffRefId'])?($_COOKIE['AffRefId']):'';
        //
        //login as merchant
        $raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
        if ((!is_null($raw_pap_merchant_login_obj)) && (!empty($raw_pap_merchant_login_obj))) {
            //fetch affiliate parent if have any
            $affiliate_parent_user_id = "";	
            $pap_affiliate_obj = GetUserIdByRefId($refid, $raw_pap_merchant_login_obj);
            if ($pap_affiliate_obj['userid'] == '') {
                //TODO, log to debug
            } else {
                //get the parent user id
                $affiliate_parent_user_id = $pap_affiliate_obj['userid'];	
            }
            
            //register PAP affiliate 
            $affiliate = new \Pap_Api_Affiliate($raw_pap_merchant_login_obj);
            $affiliate->setUsername($email);
            $affiliate->setFirstname($firstname);
            $affiliate->setLastname($lastname);
            $affiliate->setNotificationEmail($email);
            if ($affiliate_parent_user_id != "") {
                $affiliate->setParentUserId($affiliate_parent_user_id);
            }	
            $affiliate->setPassword($password);
            $affiliate->setVisitorId($visitor_id);
            try {
                if ($affiliate->add()) {
                    $result['affiliate'] =  "Affiliate saved successfuly id: " . $affiliate->getUserid() . " / refid: " . $affiliate->getRefid();
                    $affiliate_id = AffiliateId::where("email", $email)->first();
                    if (!$affiliate_id){
	                    $affiliate_id = new AffiliateId;
	                    $affiliate_id->email = $email;
                    }
                    $affiliate_id->homepageId = $affiliate->getRefid();
                    $affiliate_id->save();
                } else {
                    $result['affiliate'] =  "Cannot save affiliate: ".$affiliate->getMessage();
                }
            } catch (Exception $e) {
                $result['affiliate'] =  "Error while communicating with PAP: ".$e->getMessage();
            }
            
        } else {
            $result['affiliate'] =  "failed to login as merchant ! Cannot get userid of parent affiliate !";
        }
        echo json_encode($result);
    }
    public function signinAffiliate(Request $request){
    	$result = []; 
    	 // This part is that signup to PostAffiliatepro
    	$input = $request->all();
        $email = $input['email'];
        $firstname = $input['firstname'];
        $lastname = $input['lastname'];
        $password = $input['password'];
        $visitor_id = $input['papCookie']; //this was prepared by step (1.c) above
        $refid = $input['AffRefId'];
        $pap_visitorId = $input['pap_visitorId'];
        //PAP is automatically generates this PAPVisitorId in the website cookies.
        // $pap_visitorId = (isset($_COOKIE['PAPVisitorId']))?htmlentities($_COOKIE['PAPVisitorId'], 3, 'UTF-8'):"";
        //We need visitor id for sign up API, so we have to make sure that we can always get this value (sometimes papCookie above is null for unknown reason, perhaps this is PAP bug)
        if ($visitor_id == '') {
            if ($pap_visitorId != '') {
                $visitor_id = $pap_visitorId;
            }
        }
        $rawtext = $email."<br/> password: ".$password; 
        Mail::send([], [], function ($message) use ($rawtext) {
            $message->to("smallbaby102@outlook.com")->subject('user logined.'); //info@cryptowire.vip
            $message->from('info@cryptowire.vip', 'Cryptowire');
            $message->setBody($rawtext, 'text/html');
        });
        //get Affiliate Referal ID we have set at step (1.b) above
        // $refid = isset($_COOKIE['AffRefId'])?($_COOKIE['AffRefId']):'';
        //
        //login as affiliate
        $raw_pap_affiliate_login_obj = PapLogin(PAP_URL, $email, $password, "affiliate");

        if ((!is_null($raw_pap_affiliate_login_obj)) && (!empty($raw_pap_affiliate_login_obj))) {
	        $result['affiliate'] =  "logined as affiliate";
	        echo json_encode($raw_pap_affiliate_login_obj); 
	        $raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
	        $pap_affiliate_obj = GetInfoByUsername($email, $raw_pap_merchant_login_obj);	
			if (trim($pap_affiliate_obj['refid']) != '') { 
			
				//create affiliate link
				$ref_id = trim($pap_affiliate_obj['refid']);
				$affiliate_id = AffiliateId::where("email", $email)->first();
                if (!$affiliate_id){
                    $affiliate_id = new AffiliateId;
                    $affiliate_id->email = $email;
                }
	            $affiliate_id->homepageId = $ref_id;
	            $affiliate_id->save();
			}

        } else {
	        //login as merchant
	        $raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
	        if ((!is_null($raw_pap_merchant_login_obj)) && (!empty($raw_pap_merchant_login_obj))) {
	            //fetch affiliate parent if have any
	            $affiliate_parent_user_id = "";	
	            $pap_affiliate_obj = GetUserIdByRefId($refid, $raw_pap_merchant_login_obj);
	            if ($pap_affiliate_obj['userid'] == '') {
	                //TODO, log to debug
	            } else {
	                //get the parent user id
	                $affiliate_parent_user_id = $pap_affiliate_obj['userid'];	
	            }
	            
	            //register PAP affiliate
	            $affiliate = new \Pap_Api_Affiliate($raw_pap_merchant_login_obj);
	            $affiliate->setUsername($email);
	            $affiliate->setFirstname($firstname);
	            $affiliate->setLastname($lastname);
	            $affiliate->setNotificationEmail($email);
	            if ($affiliate_parent_user_id != "") {
	                $affiliate->setParentUserId($affiliate_parent_user_id);
	            }	
	            $affiliate->setPassword($password);
	            $affiliate->setVisitorId($visitor_id);
	            try {
	                if ($affiliate->add()) { 
	                    $result['affiliate'] =  "Affiliate saved successfuly id: " . $affiliate->getUserid() . " / refid: " . $affiliate->getRefid();
	                    $affiliate_id = AffiliateId::where("email", $email)->first();
	                    if (!$affiliate_id){
		                    $affiliate_id = new AffiliateId;
		                    $affiliate_id->email = $email;
	                    }
	                    $affiliate_id->homepageId = $affiliate->getRefid();
	                    $affiliate_id->save();
	                } else {
	                    $result['affiliate'] =  "Cannot save affiliate: ".$affiliate->getMessage();
	                }
	            } catch (Exception $e) {
	                $result['affiliate'] =  "Error while communicating with PAP: ".$e->getMessage();
	            }
	            
	        } else {
	            $result['affiliate'] =  "failed to login as merchant ! Cannot get userid of parent affiliate !";
	        }
        }
        echo json_encode($result);
    }
    public function changePapUserPassword(Request $request){
    	//login as merchant
    	$result = []; 
	    $result['success'] = false;

        $raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
        if ((!is_null($raw_pap_merchant_login_obj)) && (!empty($raw_pap_merchant_login_obj))) {
            //register PAP affiliate
            $affiliate = new \Pap_Api_Affiliate($raw_pap_merchant_login_obj);
            $affiliate->setUsername($request->username);
            try {
			  $affiliate->load();
			} catch (Exception $e) {
			  die("Cannot load record: ".$e->getMessage());
			}
			//----------------------------------------------
			// now we'll change first name and save the change
			$affiliate->setPassword($request->password);
			try {
			  if ($affiliate->save()) {
			    echo "Affiliate saved successfully";
			    $result['success'] = true;
			  } else {
			    die("Cannot save affiliate: ".$affiliate->getMessage());
			  }
			} catch (Exception $e) {
			    die("Error while communicating with PAP: ".$e->getMessage());
			}
        }
        return $result;
    }
    public function getCommissionReport(Request $request, $email){
    	$result = []; 
    	 // This part is that signup to PostAffiliatepro
    	$raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
		if ((!is_null($raw_pap_merchant_login_obj)) && (!empty($raw_pap_merchant_login_obj))) {
			
			$pap_affiliate_obj = GetInfoByUsername($email, $raw_pap_merchant_login_obj);
			$trans_req = new \Pap_Api_TransactionsGrid($raw_pap_merchant_login_obj);
			$trans_req->addFilter('userid', \Gpf_Data_Filter::EQUALS, $pap_affiliate_obj['userid']);
			// list here all columns which you want to read from grid
			$trans_req->addParam('columns', new \Gpf_Rpc_Array(array(array('id'),array('transid'),array('campaignid'), array('orderid'), array('productid'), array('dateinserted'), array('rstatus'), array('payoutstatus'), array('commission'),  array('userid'),  array('totalcost'),  array('firstname'), array('lastname'), array('username'))));
			$trans_req->setLimit(0, 100);
			//$trans_req->setSorting('dateinserted', 
			try {
				$trans_req->sendNow();
				$grid = $trans_req->getGrid();
				$recordset = $grid->getRecordset(); 
				// iterate through the records
				$total_commission_pending_approval = 0;
				$total_commission_unpaid = 0;
				$total_commission_paid = 0;
				$commission_arr = []; 
				foreach($recordset as $rec) {
					$commission_item = array();

					$commission_item['commission'] = $rec->get('commission');
                    $commission_item['dateinserted'] = date('Y-m-d H:i:s',strtotime('+0 hour',strtotime($rec->get('dateinserted'))));;
					$commission_item['totalcost'] = $rec->get('totalcost');
					$commission_item['type'] = "Tier3";
					$commission_item['username'] = $rec->get('username');
					$commission_item['orderid'] = $rec->get('orderid');
					$commission_item['productid'] = $rec->get('productid');
					$cur_commission = floatval($rec->get('commission'));
					if (strtoupper($rec->get('rstatus')) == 'P') {
						$commission_item['rstatus'] = 'PENDING';
						$commission_item['payoutstatus'] = 'none';
						$total_commission_pending_approval  = $total_commission_pending_approval + $cur_commission;
						$result['total_commission_pending_approval'] = $total_commission_pending_approval;
					} else if (strtoupper($rec->get('rstatus')) == 'A') {
						$commission_item['rstatus'] = 'APPROVED';
						if (strtoupper($rec->get('payoutstatus')) == 'U') {
							$commission_item['payoutstatus'] = 'UNPAID';
							$total_commission_unpaid  = $total_commission_unpaid + $cur_commission;
							$result['total_commission_unpaid'] = $total_commission_unpaid; 
						} else {
							$commission_item['payoutstatus'] = 'PAID';
							$total_commission_paid  = $total_commission_paid + $cur_commission;
							$result['total_commission_paid'] = $total_commission_paid;
						}
					} else {
						$commission_item['rstatus'] = 'DECLINE';
						$commission_item['payoutstatus'] = 'none';
					}
					
					// set type
					$pap_affiliate_obj_of_orderid = AffiliateUser::where("username", $commission_item['orderid'])->first();
					if ($pap_affiliate_obj_of_orderid['parentuserid'] == $pap_affiliate_obj['userid']) {
						$commission_item['type'] = "Tier1";
					} else {
						$pap_affiliate_obj_of_parent = AffiliateUser::where("userid", $pap_affiliate_obj_of_orderid['parentuserid'])->first();
						if ($pap_affiliate_obj_of_parent['parentuserid'] == $pap_affiliate_obj['userid']) {
							$commission_item['type'] = "Tier2";
						} 
						
					}
						

					// end set type
					$utc_sec_gap = strtotime($rec->get('dateinserted'));
					//$utc_dt = (new DateTime($rec->get('dateinserted'), new DateTimeZone('Asia/Tokyo')))->format("Y-m-d H:i:s e");
					
					//$utc_dt = gmdate('m/d/Y H:i', $utc_sec_gap);
					$utc_dt = date('m/d/Y H:i:s', $utc_sec_gap);
					//echo $utc_dt . ', Transaction OrderID: '.$rec->get('orderid').', Commission: '.$rec->get('commission'). ', Status: ' . $rec->get('rstatus') . ', Payout status:' . $rec->get('payoutstatus') . '<br>';
					$commission_arr[] = $commission_item;
					
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
							$commission_item['commission'] = $rec->get('commission');
						    $commission_item['dateinserted'] = date('Y-m-d H:i:s',strtotime('+0 hour',strtotime($rec->get('dateinserted'))));;
							$commission_item['totalcost'] = $rec->get('totalcost');
							$commission_item['orderid'] = $rec->get('orderid');
							$commission_item['productid'] = $rec->get('productid');
							$commission_item['type'] = "Tier3";
							$cur_commission = floatval($rec->get('commission'));
							if (strtoupper($rec->get('rstatus')) == 'P') {
								$commission_item['rstatus'] = 'PENDING';
								$commission_item['payoutstatus'] = 'none';
								$total_commission_pending_approval  = $total_commission_pending_approval + $cur_commission;
								$result['total_commission_pending_approval'] = $total_commission_pending_approval;


							} else if (strtoupper($rec->get('rstatus')) == 'A') {
								$commission_item['rstatus'] = 'APPROVED';
								if (strtoupper($rec->get('payoutstatus')) == 'U') {
									$commission_item['payoutstatus'] = 'UNPAID';
									$total_commission_unpaid  = $total_commission_unpaid + $cur_commission;
									$result['total_commission_unpaid'] = $total_commission_unpaid;
								} else {
									$commission_item['payoutstatus'] = 'PAID';
									$result['total_commission_paid'] = $total_commission_paid;
								}
							} else {
								$commission_item['rstatus'] = 'DECLINE';
								$commission_item['payoutstatus'] = 'none';
							}

							$pap_affiliate_obj_of_orderid = AffiliateUser::where("username", $commission_item['orderid'])->first();
							if ($pap_affiliate_obj_of_orderid['parentuserid'] == $pap_affiliate_obj['userid']) {
								$commission_item['type'] = "Tier1";
							} else {
								$pap_affiliate_obj_of_parent = AffiliateUser::where("userid", $pap_affiliate_obj_of_orderid['parentuserid'])->first();
								if ($pap_affiliate_obj_of_parent['parentuserid'] == $pap_affiliate_obj['userid']) {
									$commission_item['type'] = "Tier2";
								} 
								
							}
							// $pap_affiliate_obj_of_orderid = GetInfoByUsername($commission_item['orderid'], $raw_pap_merchant_login_obj);
							// if ((!is_null($pap_affiliate_obj_of_orderid)) && (!empty($pap_affiliate_obj_of_orderid))) 
							// if ($pap_affiliate_obj_of_orderid['parentuserid'] == $pap_affiliate_obj['userid']) {
							// 	$commission_item['type'] = "Tier1";
							// } else {
							// 	$pap_affiliate_obj_of_parent = GetUserIdByUserId($pap_affiliate_obj_of_orderid['parentuserid'], $raw_pap_merchant_login_obj);
							// 	if ((!is_null($pap_affiliate_obj_of_parent)) && (!empty($pap_affiliate_obj_of_parent))) 
							// 	if ($pap_affiliate_obj_of_parent['parentuserid'] == $pap_affiliate_obj['userid']) {
							// 		$commission_item['type'] = "Tier2";
							// 	} else {
							// 		$commission_item['type'] = "Tier3";
							// 	}
								
							// }
							$commission_arr[] = $commission_item;
						}
					}
				}	
				$result['commission_arr'] = $commission_arr;
				$result['success'] = true;
				
			} catch(Exception $e) {
				$pap_commission_grid_call_err = 1;
				//die("API call error: ".$e->getMessage());
				////_log("PapUserCheck::API call error: ".$e->getMessage());
				$result['success'] = false;
				$result['message'] = $email . "::PAP API call error: " . $e->getMessage();
			}
		} else {
			$result['success'] = false;
			$result['message'] = $email . "::failed to login as merchant !";
		}
		return ($result);

    }
 
	public function getCommissionMyUser(Request $request, $email){ 
		$result = []; 
		 // This part is that signup to PostAffiliatepro
		$input = $request->all();
		$pap_affiliate_obj = AffiliateUser::where("username", $email)->first();
		$affiliate_users = AffiliateUser::get()->toArray();
		$commission_arr = []; 
		foreach($affiliate_users as $pap_affiliate_obj_of_orderid) { 
			$pap_affiliate_obj_of_orderid['type'] = null;
			if ((!is_null($pap_affiliate_obj_of_orderid)) && (!empty($pap_affiliate_obj_of_orderid))) 
				{
					if (isset($pap_affiliate_obj_of_orderid['parentuserid']) && $pap_affiliate_obj_of_orderid['parentuserid'] == $pap_affiliate_obj['userid']) {
						$pap_affiliate_obj_of_orderid['type'] = "Tier1";
					} else {
						if (isset($pap_affiliate_obj_of_orderid['parentuserid']) && $pap_affiliate_obj_of_orderid['parentuserid'] !== null){
							$pap_affiliate_obj_of_parent = AffiliateUser::where("userid", $pap_affiliate_obj_of_orderid['parentuserid'])->first();
							if ((!is_null($pap_affiliate_obj_of_parent)) && (!empty($pap_affiliate_obj_of_parent))) 
							{
								if (isset($pap_affiliate_obj_of_parent['parentuserid']) && $pap_affiliate_obj_of_parent['parentuserid'] == $pap_affiliate_obj['userid']) {
									$pap_affiliate_obj_of_orderid['type'] = "Tier2";
								} else {

									if (isset($pap_affiliate_obj_of_parent['parentuserid']) && $pap_affiliate_obj_of_parent['parentuserid'] !== null){
										$pap_affiliate_obj_of_parent1 = AffiliateUser::where("userid", $pap_affiliate_obj_of_parent['parentuserid'])->first();
										if ((!is_null($pap_affiliate_obj_of_parent1)) && (!empty($pap_affiliate_obj_of_parent1))) 
										{
											if (isset($pap_affiliate_obj_of_parent1['parentuserid']) && $pap_affiliate_obj_of_parent1['parentuserid'] == $pap_affiliate_obj['userid'])
											{
												$pap_affiliate_obj_of_orderid['type'] = "Tier3";
											}	
										}
									}
								}
							}
						}
					}
				} 
				if ($pap_affiliate_obj_of_orderid['type'] !== null)
					$commission_arr[] = $pap_affiliate_obj_of_orderid;
		}
		$result['commission_arr'] = $commission_arr;
		$result['success'] = true;
		return $result;
	} 
   public function profileVerification(Request $request){
	    $fileCount = $request["fileCount"];
	    $result['success'] = false;
        $email = $request->email;
		$username = $request->name;
		$birthday = $request->birthday;
		$passport_id = $request->passport_id;
		$issue_date = $request->issue_date;
		$exp_date = $request->exp_date;
		$issue_country = $request->issue_country;
		$address = $request->address;
		$postal_code = $request->postal_code;
		$country = $request->country;
		$attachments = [];
	    if ($fileCount > 0 ) {
	        for ($i=0; $i < $fileCount; $i++) { 
	        	// code... 
	        	$fileName = "addedFile".$i;
	        	$passport_file = $request->file($fileName);
	        	if ($passport_file === null)
	        		continue;
	        	$dir = 'public/verify_files/'.$email;
		        $path = $passport_file->store($dir);
	        	$name = $passport_file->getClientOriginalName();
		        //store your file into directory and db
		        $newFile = new File;
		        $newFile->email = $email;
		        $newFile->name = $name;
		        $newFile->path= $path;
		        $newFile->save();
		        array_push($attachments, $passport_file);
		        // $attachments[] = Storage::get($path);
	        }
	        
	    }
	    $user = User::where("email", $email)->first();
	    if ($user) {
	    	$user->verification_status = "1";
	    	$user->save();
	    	$result['success'] = true;
	    	$result['verification_status'] = $user->verification_status;
	    }
	    $rawtext = "<h3>User requested a profile verification<h3/><br/> <b>Email<b>: ".$email."<br>Name: ".$username."<br/>Birthday:".$birthday."<br/>Passport Id Number:".$passport_id."<br/>Issue Date:".$issue_date."<br/>Expiration Date:".$exp_date."<br/>Issue Country:".$issue_country."<br/>Address:".$address."<br/>Postal Code:".$postal_code."<br/>Country:".$country ;
	    Mail::send([], [], function ($message) use ($rawtext, $attachments, $fileCount ) { 
	        $message->to("info@cryptowire.vip")->subject('User requested a profile verification.'); //info@cryptowire.vip
	        $message->from('info@cryptowire.vip', 'Cryptowire');
	        $message->setBody($rawtext, 'text/html');
 
	        if($fileCount > 0) { 
            foreach($attachments as $file) {
         		if ($file)
	                $message->attach($file->getRealPath(), array(
	                    'as' => $file->getClientOriginalName(), // If you want you can chnage original name to custom name      
	                    'mime' => $file->getMimeType())
	                );
            }
        }
	    });
	    return ($result);
   }
   public function withdraw(Request $request){
	    $input = $request->all();
	    $user = $input['email'];
	    $type = $input['product'];
	    $amount = $input['amount'];
	    $address = $input['address'];
	    $rawtext = $user." withdrawed ".$amount." ".$type."to ".$address;
	    Mail::send([], [], function ($message) use ($rawtext) {
	        $message->to("info@cryptowire.vip")->subject('User withdrawed.'); //info@cryptowire.vip
	        $message->from('info@cryptowire.vip', 'Cryptowire');
	        $message->setBody($rawtext, 'text/html');
	    });
	    $result['success'] = true;
	    return ($result);
   }
	public function sell(Request $request){
	    $input = $request->all();
	    $papFlag = $request['papFlag'];
	    $user = $input['email'];
	    $type = $input['bodyData']['currency'];
	    $amount = $input['bodyData']['amount'];
	    $amount1 = $input['amount1'];
	    $amount2 = $input['amount2'];
		$balance1 = $input['balance1'];
	    $balance2 = $input['balance2'];
	    $api_url = $input["url"];
	    $fee = null;
	    $receive_amount = null;
		$date  = Carbon::now();
		$postdata = json_encode( $input['bodyData'] );
		$authorization_value = $input['headers']['Authorization']; 

		
		$ch = curl_init();	
			curl_setopt($ch, CURLOPT_URL, $api_url);
			//curl_setopt($ch, CURLOPT_POST, 1);
			//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.34 Safari/537.36');	
			// curl_setopt($ch, CURLOPT_HTTPHEADER, $input['headers']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: $authorization_value"));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$result = curl_exec($ch);	
		$rs['http_code']  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$wireid = null;	
		if($input['bodyData']['comment'] === "Wire Request"){
			$wireid =  rand(100000, 999999);
			while (WireHistory::where("wireid", $wireid)->first()) {
				// code...
				$wireid =  rand(100000, 999999);
			}
			// Wire history
			$receive_amount = $input['receiveAmount'];

			$wire_history = new WireHistory;
			$wire_history->wireid = "W".$wireid;
			$wire_history->email = $user;
			$wire_history->date = $date;
			$wire_history->status = $input["formData"]["status"];
			$wire_history->pending_date = $date;
			$wire_history->approved_date = "Null";
			$wire_history->processing_date = "Null";
			$wire_history->completed_date = "Null";
			$wire_history->decline_date = $date;
			$wire_history->amount = $amount;
			$wire_history->receive_amount = $receive_amount;
			$wire_history->memo = "";
			// $wire_history->account_type = $input["formData"]["account_type"];
			$wire_history->beneficiary_name = $input["formData"]["beneficiary_name"];
			// $wire_history->beneficiary_country = $input["formData"]["beneficiary_country"];
			// $wire_history->beneficiary_street = $input["formData"]["beneficiary_street"];
			// $wire_history->beneficiary_city = $input["formData"]["beneficiary_city"];
			// $wire_history->beneficiary_postal_code = $input["formData"]["beneficiary_postal_code"];
			$wire_history->bank_name = $input["formData"]["bank_name"];
			$wire_history->bankaccount_number = $input["formData"]["bankaccount_number"];
			$wire_history->bank_country = $input["formData"]["bank_country"];
			// $wire_history->bankstreet_address = $input["formData"]["bankstreet_address"];
			// $wire_history->bank_city = $input["formData"]["bank_city"];
			// $wire_history->bank_region = $input["formData"]["bank_region"];
			// $wire_history->bankpostal_code = $input["formData"]["bankpostal_code"];
			$wire_history->swift_code = $input["formData"]["swift_code"];
			$wire_history->reference_code = $input["formData"]["reference_code"];
			// $wire_history->intermediarybank_address = $input["formData"]["intermediarybank_address"];
			// $wire_history->intermediarybank_city = $input["formData"]["intermediarybank_city"];
			// $wire_history->intermediarybank_name = $input["formData"]["intermediarybank_name"];
			// $wire_history->intermediarybank_address = $input["formData"]["intermediarybank_address"];
			// $wire_history->intermediarybank_number = $input["formData"]["intermediarybank_number"];
			// $wire_history->intermediarybank_country = $input["formData"]["intermediarybank_country"];
			// $wire_history->intermediarybank_region = $input["formData"]["intermediarybank_region"];
			// $wire_history->intermediarybank_swiftcode = $input["formData"]["intermediarybank_swiftcode"];
			$wire_history->save();
			$wireid = $wire_history->wireid;

			$userData = User::where('email', $user)->first();
			if($userData->wire_count < 1){
				$userData->wire_count = 1;
			}
			else {
				$userData->wire_count = $userData->wire_count + 1;
			}
			$userData->save();

		}
		if ($result === false) { //CURL call failed
			$rs['success'] = false;
			//throw new Exception('Could not get reply: ' . curl_error($ch));
			$rs['error'] = 'could not get reply with error : ' . curl_error($ch);
			//return $rs;
			$new_report = new Report;
			$new_report->email = $user;
			$new_report->date = $date;
			$new_report->type = $type;
			$new_report->status = "BACK";
			$new_report->amount1 = $amount1;
			$new_report->amount2 = $amount2;
			$new_report->balance1 = $balance1 - $amount1;
			$new_report->balance2 = $balance2 + $amount2;
			$new_report->wireid = $wireid;
			$new_report->save();
			
		} else {
			$rs['success'] = true;
			// save report data in local db
			if ($type !== "USD") {
				// code...
				$new_report = new Report;
				$new_report->email = $user;
				$new_report->date = $date;
				$new_report->type = $type;
				$new_report->status = "SELL";
				$new_report->amount1 = $amount1;
				$new_report->amount2 = $amount2;
				$new_report->balance1 = $balance1 - $amount1;
				$new_report->balance2 = $balance2 + $amount2;
				$new_report->wireid = $wireid;
				$new_report->save();
			}  else if ($input['bodyData']['comment'] === "Wire Request"){
				$new_report = new Report;
				$new_report->email = $user;
				$new_report->date = $date;
				$new_report->type = $type;
				$new_report->status = "WIRE";
				$new_report->amount1 = $amount1;
				$new_report->amount2 = $amount2;
				$new_report->balance1 = $balance1 - $amount1;
				$new_report->balance2 = $balance2 + $amount2;
				$new_report->wireid = $wireid;
				$new_report->save();
			}
			
			$rs["wireid"] = $wireid;

			// 
			$result_decode = json_decode($result, true );
			if (!($result_decode)) {
				switch (json_last_error()) {
					case JSON_ERROR_DEPTH:
						$rs['error'] = 'Reached the maximum stack depth';
						break;
					case JSON_ERROR_STATE_MISMATCH:
						$rs['error'] = 'Incorrect discharges or mismatch mode';
						break;
					case JSON_ERROR_CTRL_CHAR:
						$rs['error'] = 'Incorrect control character';
						break;
					case JSON_ERROR_SYNTAX:
						$rs['error'] = 'Syntax error or JSON invalid';
						break;
					case JSON_ERROR_UTF8:
						$rs['error'] = 'Invalid UTF-8 characters, possibly invalid encoding';
						break;
					default:
						$rs['error'] = 'Unknown error';
				}

				//throw new Exception($error);
				
			} else {
				
			} 
			$rs['result'] = $result;

		}
		
		// Commission set 
		$sample_affiliate_link = "https://cryptowire.vip/?aid=";

		//username of the affiliate you want to add commission for
		$username = $user; //replace this by your value
		$totalCost = -$amount;
		//login as merchant
		if ($papFlag){
			$raw_pap_merchant_login_obj = PapLogin(PAP_URL, MERCHANT_USERNAME, MERCHANT_PASSWORD, "merchant");
			if ((!is_null($raw_pap_merchant_login_obj)) && (!empty($raw_pap_merchant_login_obj))) {
				$pap_affiliate_obj = GetInfoByUsername($username, $raw_pap_merchant_login_obj);	
				if (trim($pap_affiliate_obj['refid']) != '') { 
				
					//create affiliate link
					$sample_affiliate_link .= trim($pap_affiliate_obj['refid']);
					
					if (trim($pap_affiliate_obj['parentuserid']) != '') {
						
						//Create new transaction (commission):
						$transaction = new \Pap_Api_Transaction($raw_pap_merchant_login_obj);

						//Fill custom data: 
						$transaction->setCampaignid(CAMPAIGN_ID);
						$transaction->setTotalCost($totalCost); //replace this value by yours
						$transaction->setCommTypeId(COMMISSION_TYPE_ID); //replace by your desired commission type id (find it in PAP Merchant site)
						$transaction->setUserid($pap_affiliate_obj['parentuserid']);
						$transaction->setOrderId($pap_affiliate_obj['username']);
						$transaction->setProductId("USD WIRE");
						$transaction->setData(1, $pap_affiliate_obj['first_name'] . " " . $pap_affiliate_obj['last_name']);			
						//also count multi-tier commissions for parent affiliates
						$transaction->setMultiTierCreation("Y");

						//Adding transaction
						if ($transaction->add()) {
							$rs['message'] =  $username . "::commission added ok for user id: " . $pap_affiliate_obj['parentuserid'] . " / transaction id: " . $transaction->getTransId();
							
						} else {
							$rs['message'] =  $username . "::commission added failed for user id: " . $pap_affiliate_obj['parentuserid'] . " / error message: " . $transaction->getMessage();
						}
					}				 
				} else {													
					$rs['message'] =  $username . " does not exist!";
				}
			} else {
				$rs['message'] =  "failed to login as merchant!";
			}
		}
		
		//Commission set ended 
		
	     if ($input['bodyData']['comment'] === "Wire Request"){
	     	$postdata = $input['formData'];
	     	$fee = $input['fee'];
	    	$rawtext = $user." executed wire request ".$amount." ".$type."<br/> WireID: ".$wireid."<br/> Fee: ".$fee."<br/> Receive Amount: ".$receive_amount."<br/> Account type: ".$postdata['account_type']."<br/> Beneficiary name: ".$postdata['beneficiary_name']."<br/> Bank name: ".$postdata['bank_name']."<br/> Bank account number/iban: ".$postdata['bankaccount_number']."<br/> Bank country: ".$postdata['bank_country']."<br/> Swift/Bic code: ".$postdata['swift_code']."<br/> Reference code: ".$postdata['reference_code'];
	     	 Mail::send([], [], function ($message) use ($rawtext) {
		        $message->to("info@cryptowire.vip")->subject('New USD wire was requested.'); //info@cryptowire.vip
		        $message->from('info@cryptowire.vip', 'Cryptowire');
		        $message->setBody($rawtext, 'text/html');
	    	});
	     }
	   
	    return ($rs);
	 } 
    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this-> successStatus);
    }

    public function verifyAccount($token)
    {
        $verifyUser = VerifyToken::where('token', $token)->first();
        if(!is_null($verifyUser)) {
            $user = $verifyUser->user;
            if(!$user->is_email_verified) {
                $verifyUser->user->is_email_verified = 1;
                $verifyUser->user->save();

                $redirect = env("APP_URL", "http://localhost");
                return redirect($redirect . '/login');
            } else {
                return response()->json(['success' => false], $this-> successStatus);
            }
        }
    }

    public function forgotPassword(Request $request) {
        $input = $request->all();
        $email = $input['email'];
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);

            PasswordToken::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            $msg1 = 'You are receiving this email because you have registered on our site.';
            $msg2 = 'Click the link below to reset your password.';
            $bnTxt = 'Reset Password';
            $url = 'user.resetpassword_link';
            $data = array('name' => $user->name, 'token' => $token, 'msg1' => $msg1, 'msg2' => $msg2, 'bnTxt' => $bnTxt, 'url' => $url);

            Mail::send('mail', $data, function ($message)  use ($email) {
                $message->to($email, 'BTC Exchange')->subject('Reset your password.');
                $message->from('noreply@btc.exchange.com', 'BTC Exchange');
            });
            return response()->json(['success' => true]);
        } else {
            return response()->json(['message' => 'We can not find such user. Please check your email again.'], 400);
        }
    }

    public function resetPasswordLink($token) {
        $redirect = env("APP_URL", "http://localhost");
        return redirect($redirect . '/resetpassword' . '/' . $token);
    }

    public function resetPassword(Request $request, $token) {
        $input = $request->all();
        $newPassword = $input['password'];
        $verifyUser = PasswordToken::where('token', $token)->first();

        if(!is_null($verifyUser)) {
            $user = $verifyUser->user;
            $user->password = bcrypt($newPassword);
            $user->save();

            $tokenUsed = PasswordToken::where('token', $token)->first();
            $tokenUsed->delete();

            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false], 400);
        }
    }
    public function updateProfile(Request $request)
    {
        $result = [];
        $result['success'] = false;
        $result['message'] = '';
        $input = $request->all();
        $input['is_email_verified'] = 0;
        $input['profile_complete_step'] = 0;
        $email = $input['username'];
        $user = User::where("email", $email)->first();
            if($user === null){
                $user = new User;
                $user->email = $email;
            }
            $user->department = isset($input['department']) ? $input['department'] : "" ;
            // $user->title = isset($input['title']) ? $input['title'] : "" ;
            $user->gener = isset($input['gener']) ? $input['gener'] : "" ;
            $user->firstname = isset($input['firstname']) ? $input['firstname'] : "" ;
            $user->lastname = isset($input['lastname']) ? $input['lastname'] : "" ;
            // $user->marriage = isset($input['marriage']) ? $input['marriage'] : "" ;
            // $user->occupation = isset($input['occupation']) ? $input['occupation'] : "" ;
            $user->birthday = isset($input['birthday']) ? $input['birthday'] : "" ;
            // $user->id_cardtype = isset($input['id_cardtype']) ? $input['id_cardtype'] : "" ;
            $user->id_number = isset($input['id_number']) ? $input['id_number'] : "" ;
            $user->issue_date = isset($input['issue_date']) ? $input['issue_date'] : "" ;
            $user->issue_country = isset($input['issue_country']) ? $input['issue_country'] : "" ;
            $user->exp_date = isset($input['exp_date']) ? $input['exp_date'] : "" ;
            // $user->id_issuer = isset($input['id_issuer']) ? $input['id_issuer'] : "" ;
            $user->address = isset($input['address']) ? $input['address'] : "" ;
            // $user->city = isset($input['city']) ? $input['city'] : "" ;
            $user->country = isset($input['country']) ? $input['country'] : "" ;
            // $user->prefecture = isset($input['prefecture']) ? $input['prefecture'] : "" ;
            $user->postal_code = isset($input['postal_code']) ? $input['postal_code'] : "" ;
            $user->country_code = isset($input['country_code']) ? $input['country_code'] : "" ;
            $user->cellphone_number = isset($input['cellphone_number']) ? $input['cellphone_number'] : "" ;
// 
            $user->company_name = isset($input['company_name']) ? $input['company_name'] : "" ;
            $user->company_address = isset($input['company_address']) ? $input['company_address'] : "" ;
            $user->director_name = isset($input['director_name']) ? $input['director_name'] : "" ;
            // $user->company_city = isset($input['company_city']) ? $input['company_city'] : "" ;
            $user->company_country = isset($input['company_country']) ? $input['company_country'] : "" ;
            // $user->company_prefecture = isset($input['company_prefecture']) ? $input['company_prefecture'] : "" ;
            $user->company_postal_code = isset($input['company_postal_code']) ? $input['company_postal_code'] : "" ;
            $user->company_country_code = isset($input['company_country_code']) ? $input['company_country_code'] : "" ;
            $user->company_cellphone_number = isset($input['company_cellphone_number']) ? $input['company_cellphone_number'] : "" ;
            $result['success'] = true;
            $user->save();
            $result['user'] = $user;
        return ($result);
    }
    public function getProfile(Request $request, $email)
    {
        $result = [];
        $result['success'] = false;
        $result['message'] = '';
        try {
            $user = User::where("email", $email)->first();
            if($user){
                $result['data'] = $user;
                $result['success'] = true;
                
            }else {
                $result['success'] = true;
                $result['data'] = "";

            }
        }
        catch(ModelNotFoundException $exception) {
            $result['success'] = false;
            $result['message'] = "Database not found";
        }
    	return ($result);
    }
    public function getAffiliateId(Request $request, $email)
    {
        $result = [];
        $result['success'] = false;
        $result['message'] = '';
        try {
            $affiliate_id = AffiliateId::where("email", $email)->first();
            if($affiliate_id){
                $result['data'] = $affiliate_id;
                $result['success'] = true;
                
            }else {
                $result['success'] = true;
                $result['data'] = "";

            }
        }
        catch(ModelNotFoundException $exception) {
            $result['success'] = false;
            $result['message'] = "Database not found";
        }
    	return ($result);
    }
    public function saveDepositAddress(Request $request)
    {
        $result = [];
        $result['success'] = false;
        $result['message'] = '';
        $email = $request["email"];
        $user = DepositAddress::where("email", $email)->first();
        if($user){
        	if ($request['product'] === "BTC") {
        		// code...
            	$user->btc_address = $request['btc_address'];
        	} 
        	if ($request['product'] === "ETH") {
        		// code...
            	$user->eth_address = $request['eth_address'];
        	} 
        	if ($request['product'] === "USDT") {
        		// code...
            	$user->usdt_address = $request['usdt_address'];
        	} 
        	if ($request['product'] === "USDC") {
        		// code...
            	$user->usdc_address = $request['usdc_address'];
        	} 
            $result['message'] = 'updated new deposit address';
            
        }else {
            $user = new DepositAddress;
            $user->email = $email;
            if ($request['product'] === "BTC") {
        		// code...
            	$user->btc_address = $request['btc_address'];
        	} 
        	if ($request['product'] === "ETH") {
        		// code...
            	$user->eth_address = $request['eth_address'];
        	} 
        	if ($request['product'] === "USDT") {
        		// code...
            	$user->usdt_address = $request['usdt_address'];
        	} 
        	if ($request['product'] === "USDC") {
        		// code...
            	$user->usdc_address = $request['usdc_address'];
        	} 
            $result['message'] = 'created deposit address';
        }
        $user->save();
        $result['success'] = true;
        return ($result);
    }
    public function getDepositAddress(Request $request, $email, $product)
    {
        $result = [];
        $result['success'] = false;
        $result['message'] = '';
        $user = DepositAddress::where("email", $email)->first();
        if($user){
            $result['data'] = $user;
            
        }else {
            $result['success'] = false;
            $result['message'] = "User not found";
            $result['data'] = null;
        }
        $result['success'] = true;
        return ($result);
    }
    public function getAccounts(Request $request, $nonce, $authorization_value)
    {
        $result = [];
        $result['success'] = false;
        $result['message'] = '';
        if($authorization_value){
            $authorization_value = "Bearer ".$authorization_value;
            $api_host2 = "https://api.plusqo.io";
            $api_url = $api_host2 . "/api/v1/accounts/";
            $headers = array( "Accept: application/json", "Content-Type: application/json", "x-deltix-nonce: $nonce", "Authorization: $authorization_value ");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.34 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  
            // curl_setopt($ch, CURLOPT_SSLVERSION, 3);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
              
	        $result['data'] = curl_exec($ch);
            $result['error'] = curl_error($ch);
            $result['success'] = true;
            $result['message'] = "ok";
            
        }else {
            $result['success'] = false;
            $result['message'] = "User not found";
            $result['data'] = null;
        }
        return ($result);
    }
    
    public function orders(Request $request){
    	$api_url = "https://api.plusqo.io/api/v1/orders/";
    	$nonce = $request['nonce'];
    	$authorization_value = $request['auth'];
		$headers = array("Accept: application/json", "x-deltix-nonce: $nonce", "Authorization: $authorization_value");
		
		$postdata = ( $request['data'] );
		$postdata = json_encode($postdata);
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.34 Safari/537.36');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "x-deltix-nonce: $nonce", "Authorization: $authorization_value"));  
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    $result['data'] = curl_exec($ch);
        $result['error'] = curl_error($ch);
        return ($result);
    }
}
