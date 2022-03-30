<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use Illuminate\Support\Str;
use App\Users_Model;
use App\ProductsModel;
use App\SettingsModel;
use App\TripsModel;
use App\ShipmentModel;
use App\RatingModel;
use App\FeedbackModel;
use App\PaymentModel;
use App\NotificationModel;
use Auth;
use Validator;
use Mail;

class Users_Controller extends Controller
{
    
    private $dihedral = array(
        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
        array(1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
        array(2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
        array(3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
        array(4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
        array(5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
        array(6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
        array(7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
        array(8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
        array(9, 8, 7, 6, 5, 4, 3, 2, 1, 0)
    );
    private $permutation = array(
        array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
        array(1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
        array(5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
        array(8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
        array(9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
        array(4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
        array(2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
        array(7, 0, 4, 6, 9, 1, 3, 2, 5, 8)
    );
    private $inverse = array(0, 4, 3, 2, 1, 5, 6, 7, 8, 9);
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //Api Functions
    public function user_registration(Request $request)
    {
        $data = array();
        $data['name'] = $request->input('full_name');
        $data['email'] = $request->input('user_email');
        $data['password'] = Hash::make($request->input('user_password'));
        $data['country_code'] = ($request->input('country_code') != '')?$request->input('country_code'):'India';
        $token = Str::random(80);
        $data['api_token'] = hash('sha256', $token);
        $data['signup_type'] = ($request->input('signup_type') != '')?$request->input('signup_type'):'app';
        $profile_image = $request->input('profile_image');
        $referral_code = Str::random(9);
        $data['referral_code'] = $referral_code;
        $data['fcm_token'] = $request->input('fcm_token');

        if((!empty($profile_image))){
            if(($profile_image != 'null')){
                $filename = rand(100,999999).time().'.jpg';
                $image = public_path('assets/users').'/'.$filename;
                file_put_contents($image, file_get_contents($profile_image));
                $data['profile_image'] = 'assets/users/'.$filename;
            }else{
                $data['profile_image'] = '';
            }
        }else{
            $data['profile_image'] = '';
        }
        /*
        if($request->file('profile_image')){
            $image = $request->file('profile_image');
            if($image->isValid()){
                
                $extension = $image->getClientOriginalExtension();
                $fileName = rand(100,999999).time().'.'.$extension;
                $image_path = public_path('assets/users/');
                $request->profile_image->move($image_path, $fileName);
                $data['profile_image'] = 'assets/users/'.$fileName;
            }
        }*/

        if($data['signup_type'] != 'app'){
            //Social
            $user = Users_Model::where('email','=',$data['email'])->first();
            if(empty($user)){
                if($data['signup_type'] == 'facebook'){
                    $data['fb_verify'] = '1';
                }else if($data['signup_type'] == 'linkdin'){
                    $data['linkdin_verify'] = '1';
                }else if($data['signup_type'] == 'google'){
                    $data['google_verify'] = '1';
                }
                
                $register_id = Users_Model::create($data)->id;
                
                $user_data = array();
                $user_data['user_id'] = $register_id;
                $user_data['name'] = $data['name'];
                $user_data['email'] = $data['email'];
                $user_data['country_code'] = $data['country_code'];
                $user_data['reg_type'] = $data['signup_type'];
                $user_data['referral_code'] = $data['referral_code'];
                
    
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Register Successfully','token'=>$token,'data'=>$user_data,'error'=>[]], 200);
            }else{
                $update = array();
                if($user->status == '1'){
                    $update['api_token'] = $data['api_token'];
                
                    $update['fcm_token'] = $data['fcm_token'];
                    
                    if($data['signup_type'] == 'facebook'){
                        $update['fb_verify'] = '1';
                    }else if($data['signup_type'] == 'linkdin'){
                        $update['linkdin_verify'] = '1';
                    }else if($data['signup_type'] == 'google'){
                        $update['google_verify'] = '1';
                    }
                    
                    Users_Model::where('email','=',$data['email'])->update($update);
    
                    $user_data = array();
                    $user_data['user_id'] = $user->id;
                    $user_data['name'] = $user->name;
                    $user_data['email'] = $user->email;
                    $user_data['country_code'] = $user->country_code;
                    $user_data['reg_type'] = $data['signup_type'];
                    $user_data['referral_code'] = $user->referral_code;
        
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Register Successfully','token'=>$token,'data'=>$user_data,'error'=>[]], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Your Account Is Blocked','error'=>['Your Account Is Blocked'],'data'=>(object)[],'token'=>''], 200);
                }
                
            }
        }else{
            //App
            $user = Users_Model::where('email','=',$data['email'])->first();
            if(empty($user)){
                $register_id = Users_Model::create($data)->id;
                
                $user_data = array();
                $user_data['user_id'] = $register_id;
                $user_data['name'] = $data['name'];
                $user_data['email'] = $data['email'];
                $user_data['country_code'] = $data['country_code'];
                $user_data['reg_type'] = $data['signup_type'];
                $user_data['referral_code'] = $data['referral_code'];
    
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Register Successfully','token'=>$token,'data'=>$user_data,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Email Address Already Taken','token'=>'','data'=>(object)[],'error'=>['Email Address Already Taken']], 200);
            }

        }
    }
    public function user_login(Request $request){
        $data = array();
        $data['email'] = $request->input('email');
        $data['password'] =$request->input('password');
        $data['fcm_token'] = $request->input('fcm_token');
        
        if(Auth::attempt(['email'=>$data['email'],'password'=>$data['password']])){
            $user = Users_Model::where('email','=',$data['email'])->first();
            $user_data = array();
            $user_data['user_id'] = $user->id;
            $user_data['name'] = $user->name;
            $user_data['email'] = $user->email;
            $user_data['country_code'] = $user->country_code;
            $user_data['referral_code'] = $user->referral_code;

            $token = Str::random(80);
            
            if($user->status == '1'){
                Users_Model::where('email','=',$data['email'])->update(['api_token'=>hash('sha256', $token),'fcm_token'=>$data['fcm_token']]);

                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Login Successfully','error'=>[],'data'=>$user_data,'token'=>$token], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Your Account Is Blocked','error'=>['Your Account Is Blocked'],'data'=>(object)[],'token'=>''], 200);
            }
            
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Email / Password','error'=>['Invalid Email / Password'],'data'=>(object)[],'token'=>''], 200);
        }
    }
    public function change_password(Request $request){
        $user_id = $request->input('user_id');
        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');
        $confirm_password = $request->input('confirm_password');

        if($new_password == $confirm_password){
           
            if(Auth::attempt(['id'=>$user_id,'password'=>$old_password])){
                $new_password = Hash::make($request->input('new_password'));
                Users_Model::where('id','=',$user_id)->update(['password'=>$new_password]);
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Password Changed Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Old Password','error'=>['Invalid Old Password']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'New password and Confirm password are not same','error'=>['New password and Confirm password are not same']], 200);
        }
    }
    public function forgot_password(Request $request){
        $email = $request->input('email');
        $user = Users_Model::where('email','=',$email)->first();
        if(!empty($user)){
                $new_pass = Str::random(10);

                //Send Email to user here...
                $email = $user->email;
                $data = array(
        			"email"=>$user->email,
        			"password"=>$new_pass,
        			"name"=>$user->name,
        		);
                
                try{
        		    Mail::send('emails.forgot-password', $data, function ($message) use ($email, $data) {
            			$message->from(env('MAIL_FROM_ADDRESS'))->to($data['email'])->subject('Your Town Buddy Password');
            		});
        		} catch (\Exception $e) {
        		    return response()->json(['status'=>false,'status_code'=>'401','message'=>$e->getMessage(),'error'=>[$e->getMessage()]], 200);
        		}

                $new_password = Hash::make($new_pass);
                Users_Model::where('email','=',$email)->update(['password'=>$new_password]);
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'New Password has been sent to your email address','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Email Address','error'=>['Invalid Email Address']], 200);
        }

    }

    public function check_verification_status(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $data['is_verified'] = '0';
            $data['mobile_number_verify'] = ($user->is_mob_verified == '1')?'true':'false';
            $data['document_verify'] = ($user->is_doc_verified == '1')?'true':(($user->is_doc_verified == '2')?'processing':'false');
            $data['email_verify'] = ($user->is_email_verified == '1')?'true':(($user->is_email_verified == '2')?'processing':'false');
            
            if(($type == 'sender') || ($type == 'traveller')){
                if($user->is_mob_verified == 1){
                    if($user->is_email_verified != 0){ // CHeck Email verification and pending status (email == 1) || (email == 2)
                        if($user->is_doc_verified == 1){
                            if(($user->is_email_verified == '2') ){
                                $data['step'] = 'pending_email_verification';
                                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                            }else{
                                $data['step'] = 'complete';
                                $data['is_verified'] = '1';
                                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                            }
                        }else{
                            //if((($user->document_number != '') && ($user->document_file != '')) || ($user->is_doc_verified == '2') ){
                            
                            if(($user->is_doc_verified == '2') ){
                                if(($user->is_email_verified == '2') ){
                                    $data['step'] = 'pending_email_verification';
                                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                                }else{
                                    $data['step'] = 'pending_document_verification';
                                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                                }
                            }else{
                                $data['step'] = 'document';
                                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                            }
                        }
                    }else{
                        $data['step'] = 'email';
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                    }
                }else{
                    $data['step'] = 'mobile';
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                }

            }else if(($type == 'shopper')){
                if($user->is_mob_verified == 1){
                    if($user->is_doc_verified == 1){
                        $data['step'] = 'complete';
                        $data['is_verified'] = '1';
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                    }else{
                        //if((($user->document_number != '') && ($user->document_file != '')) || ($user->is_doc_verified == '2') ){
                        if(($user->is_doc_verified == '2') ){
                            $data['step'] = 'pending_document_verification';
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                        }else{
                            $data['step'] = 'document';
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                        }
                    }
                }else{
                    $data['step'] = 'mobile';
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type','data'=>(object)[],'error'=>['Invalid Type']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','data'=>(object)[],'error'=>['Invalid User Id']], 200);
        }
    }
    public function send_verification_mail(Request $request){
        $email = $request->input('email');
        $validator = Validator::make(['email' => $email],[
            'email' => 'required|email'
        ]);
        
        if(!($validator->passes())){
            return response()->json(['status'=>false,'status_code'=>'404','message'=>'Invalid Email','code'=>'','error'=>['Invalid Email']], 200);    
        }

        $user = Users_Model::where('email','=',$email)->first();
        if(empty($user)){
            //$otp = Str::random(10);
            $otp = rand(100000,999999);
            //Send verification email here...
            $data = array(
    			"email"=>$email,
    			"name"=>'Test',
    			"mobile_number"=>'1234567890',
    			"otp"=>$otp,
    		);
    		
    		try{
    		    Mail::send('emails.email_verification', $data, function ($message) use ($email, $data) {
        			$message->from(env('MAIL_FROM_ADDRESS'))->to($data['email'])->subject('Your Town Buddy Email OTP');
        		});
    		} catch (\Exception $e) {
    		    return response()->json(['status'=>false,'status_code'=>'404','message'=>'Unable to Send Mail','code'=>'','error'=>[$e->getMessage()]], 200);    
    		}
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','code'=>$otp,'error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Email Address Already Taken','error'=>['Email Address Already Taken']], 200);
        }
    }
    public function check_new_user(Request $request){
        $user_id = $request->input('user_id');
        
        $user = Users_Model::where('id',$user_id)->first();
        
        // true -> Show Popup , false -> Do Not Show Popup
        
        if(!empty($user)){
            $shipment = ShipmentModel::where('created_by',$user_id)->where('is_created','1')->has('user')->has('from')->has('to')->first();
            if(!empty($shipment)){
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>false,'error'=>['success']], 200);
            }else{
                $trip = TripsModel::where('user_id',$user_id)->has('user')->has('from')->has('to')->first();
                if(!empty($trip)){
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>false,'error'=>['success']], 200);
                }else{
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>true,'error'=>['success']], 200);
                }
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','data'=>false,'error'=>['Invalid User Id']], 200);
        }
    }
    public function send_verification_code(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $src = $request->input('src');

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            if($type == 'mobile'){
                $mob_vali = Users_Model::where('id','!=',$user_id)->where('mobile_number',$src)->count();

                if(!($mob_vali > 0)){
                    $otp = rand(1000,9999);
                    //Send Otp to mobile here...
                    try{
                        $fields = json_encode(["OTP"=>$otp]);
                        //Add 91 to mobile number..
                        $mob = '91'.$src;
                        
                        $sms = new SmsController();
                        $sms->sendOtp($mob,'61389f76044067402e4c044c',$fields);
                    } catch (\Exception $e) {
                        //Exception
                    }
                    
                    Users_Model::where('id',$user_id)->update(['is_mob_verified'=>'0','mobile_number'=>$src,'otp'=>$otp,'otp_time'=>date('Y-m-d H:i:s')]);
                    $data['code'] = '';
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Mobile Number Already Taken','data'=>(object)[],'error'=>['Mobile Number Already Taken']], 200);
                }
            }else if(($type == 'email')){
                if(preg_match('(aol.com|disroot.org|gmail.com|gmx.com|hushmail.com|lockbin.com|lycos.com|mail.com|mail.ru|mailfence.com|netcourrier.com|openmailbox.org|outlook.com|protonmail.com|rediff.com|seznam.cz|tutanota.com|yahoo.com|yandex.com|zoho.com|10minutemail.com|arvixe.com|hubspot.com|sendinblue.com|icloud.com)', $src) === 1) { 
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Please Enter Valid Official Email Id','data'=>(object)[],'error'=>['Please Enter Valid Official Email Id']], 200);
                }else{
                        
                    // if($src == $user->official_email){
                        $email_vali = Users_Model::where('id','!=',$user_id)->where('official_email',$src)->first();

                        if($email_vali == null){
                            //$otp = Str::random(10);
                            $otp = rand(100000,999999);
                            //Send verification email here...
                            $email = $src;
                            $data = array(
                    			"email"=>$src,
                    			"name"=>$user->name,
                    			"mobile_number"=>$user->mobile_number,
                    			"otp"=>$otp,
                    		);
                    		
                    		try{
                    		    Mail::send('emails.email_verification', $data, function ($message) use ($email, $data) {
                        			$message->from(env('MAIL_FROM_ADDRESS'))->to($data['email'])->subject('Your Town Buddy Email OTP');
                        		});
                    		} catch (\Exception $e) {
                    		    
                    		}
                    		
                            Users_Model::where('id',$user_id)->update(['is_email_verified'=>'0','verification_code'=>$otp,'official_email'=>$src]);
                            //$data['code'] = $otp;
                            $data = array('code'=>'');
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$data,'error'=>[]], 200);
                        }else{
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Official Email Already Taken','data'=>(object)[],'error'=>['Official Email Already Taken']], 200);
                        }
                    //}else{
                      //  return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Email','data'=>(object)[],'error'=>['Invalid Email']], 200);
                    //}
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type','data'=>(object)[],'error'=>['Invalid Type']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','data'=>(object)[],'error'=>['Invalid User Id']], 200);
        }
    }
    public function verify_verification_code(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $code = $request->input('code');

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            if($type == 'mobile'){
                $otp_date = new \DateTime($user->otp_time);
                $curr_start = $otp_date->diff(new \DateTime(date('Y-m-d H:i:s')));
                if(($curr_start->i <= 2) && ($curr_start->h == 0) && ($curr_start->days == 0) ){
                    if($code == $user->otp){
                        Users_Model::where('id',$user_id)->update(['is_mob_verified'=>'1']);
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>['Invalid Otp']], 200);
                    }
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>['Invalid Otp']], 200);
                }
            }else if(($type == 'email')){
                if($code == $user->verification_code){
                    Users_Model::where('id',$user_id)->update(['is_email_verified'=>'2']);
                    
                    $notification = new NotificationController();
                    $notification->newEmailVerifyAdmin($user);
                    
                    NotificationModel::where('reference_id',$user_id)->where('type','email_unverify')->update(['type'=>'no_action']);
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Verification Code','error'=>['Invalid Verification Code']], 200);
                }
            }else if(($type == 'document')){
                if($user->aadhar_client_id != ''){
                    $valid = $this->isValidAadharOtp($user->aadhar_client_id,$code,$user);
                    if($valid == true){
                        
                        $notification = new NotificationController();
                        $notification->newDocumentVerifyAdmin($user);
                        
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid OTP','error'=>['Invalid OTP']], 200);
                    }
                    
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid OTP','error'=>['Invalid OTP']], 200);
                }
                
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type','error'=>['Invalid Type']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function isValidAadharOtp($aadhar_client_id,$otp,$user){
        $aadhar_varification_key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcmVzaCI6ZmFsc2UsImlhdCI6MTYzMjEzMzQ0MSwianRpIjoiMzljNDIyZTMtMWU3Ny00ZTVjLWJhZTYtZGQ3M2VjOTZlYmRmIiwidHlwZSI6ImFjY2VzcyIsImlkZW50aXR5IjoiZGV2Lm1idG9uQGFhZGhhYXJhcGkuaW8iLCJuYmYiOjE2MzIxMzM0NDEsImV4cCI6MTk0NzQ5MzQ0MSwidXNlcl9jbGFpbXMiOnsic2NvcGVzIjpbInJlYWQiXX19.mlsNn0uahD4_xxv91sRSLrrHRaDFNocznruao-tH8e8';
        try{
            $fields = array(
    				    'client_id'=> $aadhar_client_id,
    				    'otp'=>$otp
    			    );
    	
        	$headers = array
        			(
        				'Authorization: Bearer '.$aadhar_varification_key,
        				'Content-Type: application/json'
        			);
        
            #Send Reponse To KYC AADHAR	
        		$ch = curl_init();
        		curl_setopt( $ch,CURLOPT_URL, 'https://kyc-api.aadhaarkyc.io/api/v1/aadhaar-v2/submit-otp' );
        		curl_setopt( $ch,CURLOPT_POST, true );
        		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        		$result = curl_exec($ch );
        		curl_close( $ch );
        
            #Echo Result Of KYC AADHAR
            $res = json_decode($result,true);
            
            if($res['status_code'] == 200){
                
                Users_Model::where('id',$user->id)->update(['aadhar_response'=>$result,'is_doc_verified'=>'2']);
                
                return true;
            }else{
                return false;
            }
        }catch (\Exception $e) {
    		   //return $e; 
    		   return false;
    	}
        
        
    }
    public function check_step_verification(Request $request){
        $user_id = $request->input('user_id');
        $step = $request->input('step');
        $user = Users_Model::where('id',$user_id)->first();
        if($user != null){
            if($step == 'mobile'){
                if($user->is_mob_verified == 1){
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Mobile Number Verified','error'=>['Mobile Number Verified'],'data'=>'1'], 200);
                }else{
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Mobile Number Not Verified','error'=>['Mobile Number Not Verified'],'data'=>'0'], 200);
                }
            }else if($step == 'email'){
                if($user->is_email_verified == 1){
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Email Verified','error'=>['Email Verified'],'data'=>'1'], 200);
                }else{
                    if(($user->is_email_verified == '2') ){
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Email Is In Processing','error'=>['Email Is In Processing'],'data'=>'2'], 200);
                    }else{
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Email Not Verified','error'=>['Email Not Verified'],'data'=>'0'], 200);
                    }
                }
            }else if($step == 'document'){
                if($user->is_doc_verified == 1){
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Document Verified','error'=>['Document Verified'],'data'=>'1'], 200);
                }else{
                    //if((($user->document_number != '') && ($user->document_file != '')) || ($user->is_doc_verified == '2') ){
                    if(($user->is_doc_verified == '2') ){
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Document Is In Processing','error'=>['Document Is In Processing'],'data'=>'2'], 200);
                    }else{
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Document Not Verified','error'=>['Document Not Verified'],'data'=>'0'], 200);
                    }
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>'0'], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>'0'], 200);
        }
    }
    public function document_verification(Request $request){
        $user_id = $request->input('user_id');
        $document_type = $request->input('document_type');
        $document_number = $request->input('document_number');

        $user = Users_Model::where('id',$user_id)->first();
        if($user != null){
            $document_ver = Users_Model::where('id','!=',$user_id)->where('document_number',$document_number)->count();
            if($document_ver > 0){
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Aadhar Number Already Taken','error'=>['Aadhar Number Already Taken']], 200);
            }else{
                if($document_type == 'Aadhar'){
                    $valid = $this->isAadharValid($document_number);
                    if ($valid != 1) {
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Aadhar Number Is Not Valid','error'=>['Aadhar Number Is Not Valid']], 200);
                    }
                }
                
                $data = array(
                    "document_type"=>$document_type,
                    "document_number"=>$document_number,
                    //"is_doc_verified"=>'2'
                );
                
                if($document_type == 'Aadhar'){
                    $res = $this->sendAadharOtp($document_number,$user);
                }
    
                if($request->file('document_file')){
                    $image = $request->file('document_file');
                    if($image->isValid()){
                        $old = $user->document_file;
                        if(!empty($old)){
                            if(file_exists($old)){
                                unlink($old);
                            }
                        }
    
                        $extension = $image->getClientOriginalExtension();
                        $fileName = rand(100,999999).time().'.'.$extension;
                        $image_path = public_path('assets/documents/');
                        $request->document_file->move($image_path, $fileName);
                        $data['document_file'] = 'assets/documents/'.$fileName;
                    }
                }
    
                if($request->file('document_file_back')){
                    $image = $request->file('document_file_back');
                    if($image->isValid()){
                        $old = $user->document_file_back;
                        if(!empty($old)){
                            if(file_exists($old)){
                                unlink($old);
                            }
                        }
    
                        $extension = $image->getClientOriginalExtension();
                        $fileName = rand(100,999999).time().'.'.$extension;
                        $image_path = public_path('assets/documents/');
                        $request->document_file_back->move($image_path, $fileName);
                        $data['document_file_back'] = 'assets/documents/'.$fileName;
                    }
                }
    
                Users_Model::where('id',$user_id)->update($data);
    
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Sent Successfully','error'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        } 
    }
    public function sendAadharOtp($aadhar_no,$user){
        $aadhar_varification_key = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcmVzaCI6ZmFsc2UsImlhdCI6MTYzMjEzMzQ0MSwianRpIjoiMzljNDIyZTMtMWU3Ny00ZTVjLWJhZTYtZGQ3M2VjOTZlYmRmIiwidHlwZSI6ImFjY2VzcyIsImlkZW50aXR5IjoiZGV2Lm1idG9uQGFhZGhhYXJhcGkuaW8iLCJuYmYiOjE2MzIxMzM0NDEsImV4cCI6MTk0NzQ5MzQ0MSwidXNlcl9jbGFpbXMiOnsic2NvcGVzIjpbInJlYWQiXX19.mlsNn0uahD4_xxv91sRSLrrHRaDFNocznruao-tH8e8';
        try{
            $fields = array(
    				    'id_number'=> $aadhar_no,
    			    );
    	
        	$headers = array
        			(
        				'Authorization: Bearer '.$aadhar_varification_key,
        				'Content-Type: application/json'
        			);
        
            #Send Reponse To KYC AADHAR	
        		$ch = curl_init();
        		curl_setopt( $ch,CURLOPT_URL, 'https://kyc-api.aadhaarkyc.io/api/v1/aadhaar-v2/generate-otp' );
        		curl_setopt( $ch,CURLOPT_POST, true );
        		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        		$result = curl_exec($ch );
        		curl_close( $ch );
        
            #Echo Result Of KYC AADHAR
            $res = json_decode($result,true);
            
            if($res['status_code'] == 200){
                $client_id = $res['data']['client_id'];
                if($client_id != ''){
                    Users_Model::where('id',$user->id)->update(['aadhar_client_id'=>$client_id]);
                }
                
                return true;
            }else{
                return false;
            }
        }catch (\Exception $e) {
    		   //return $e; 
    		   return false;
    	}
    }

    public function get_user_profile(Request $request){
        $user_id = $request->input('user_id');

        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            $city = (($user->city_id != ''))?(($user->city_id != 'None')?$user->city_id:''):'';
            $state = (($user->state != ''))?(($user->state != 'None')?$user->state:''):'';
            $country = (($user->country_code != ''))?(($user->country_code != 'None')?$user->country_code:''):'India';
            if(($city != '') && ($country != '')){
                $city_country = $city.' , '.$country;
            }else{
                $city_country = '';
            }
            /*
            if($user->city != null){
                $city = $user->city->city;
                $city_country = $city.', '.$user->country_code;
            }
            */
            $completed_trips = TripsModel::where('trip_status','1')->where('user_id',$user_id)->has('user')->count();
            $delivered_shipments = ShipmentModel::where('created_by',$user_id)->where('status','5')->count();
            $reviews = RatingModel::where('rate_to',$user_id)->count();
            $withdrawal_pending = PaymentModel::where('user_id',$user->id)->where('type','wallet')->where('payment_status','2')->sum('shipment_quotation');
            
            $data = array(
                "user_id"=>$user->id,
                "name"=>$user->name,
                "email"=>$user->email,
                "official_email"=>($user->official_email != '')?$user->official_email:'',
                "profile_image"=>$user->profile_image_url,
                "profile_reviews"=>$user->profile_review,
                "mobile_number"=>($user->mobile_number != '')?$user->mobile_number:'',
                "working_at"=>($user->working_at != '')?$user->working_at:'',
                "city"=>$city,
                "state"=>$state,
                "country"=>$country,
                "bio"=>($user->bio != '')?$user->bio:'',
                "city_country"=>$city_country,
                "languages"=>($user->language != '')?$user->language:'',
                "fb_id"=>($user->fb_id != '')?$user->fb_id:'',
                "google_id"=>($user->google_id != '')?$user->google_id:'',
                "linkedin_id"=>($user->linkdin_id != '')?$user->linkdin_id:'',
                "mobile_number_verify"=>($user->is_mob_verified == '1')?true:false,
                "document_verify"=>($user->is_doc_verified == '1')?'true':(($user->is_doc_verified == '2')?'processing':'false'),
                "email_verify"=>($user->is_email_verified == '1')?'true':(($user->is_email_verified == '2')?'processing':'false'),
                "fb_verify"=>($user->fb_verify == '1')?true:false,
                "google_verify"=>($user->google_verify == '1')?true:false,
                "linkedin_verify"=>($user->linkdin_verify == '1')?true:false,
                "sender_reviews"=>$user->sender_review,
                "traveller_reviews"=>$user->traveller_review,
                "shopper_reviews"=>$user->shopper_review,
                "buddy_points"=>"25",
                "shipment_delivered"=>str_pad($delivered_shipments, 2, "0", STR_PAD_LEFT),
                "completed_trips"=>str_pad($completed_trips, 2, "0", STR_PAD_LEFT),
                "wallet_amount"=>$user->wallet_amount,
                "bank_name"=>($user->bank_name != '')?$user->bank_name:'',
                "ac_holder_name"=>($user->ac_holder_name != '')?$user->ac_holder_name:'',
                "ac_no"=>($user->ac_no != '')?$user->ac_no:'',
                "bank_swift_code"=>($user->bank_swift_code != '')?$user->bank_swift_code:'',
                "bank_ifsc"=>($user->bank_ifsc != '')?$user->bank_ifsc:'',
                "phone_pay_number"=>($user->phone_pay_number != '')?$user->phone_pay_number:'',
                "google_pay_number"=>($user->google_pay_number != '')?$user->google_pay_number:'',
                "show_socials"=>$user->show_social_details,
                "show_company"=>$user->show_company,
                "document_type"=>($user->document_type != '')?$user->document_type:'',
                "review_count"=>$reviews,
                "pending_withdrawal"=>(string)$withdrawal_pending
            );
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>(object)[]], 200);
        }
    }
    public function update_user_profile(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){

            $email_vali = Users_Model::where('id','!=',$user_id)->where('email',$request->email)->first();

            if($email_vali == null){
                if(!empty($request->official_email)){
                    $official_email_vali = Users_Model::where('id','!=',$user_id)->where('official_email',$request->official_email)->count();
                }else{
                    $official_email_vali = 0;
                }
                
                if(!($official_email_vali > 0)){
                    
                    if(!empty($request->phone)){
                        $mob_vali = Users_Model::where('id','!=',$user_id)->where('mobile_number',$request->phone)->count();
                    }else{
                        $mob_vali = 0;
                    }
                    
                    if(!($mob_vali > 0)){
                        $data = array(
                            "name"=>$request->name,
                            "email"=>$request->email,
                            "official_email"=>$request->official_email,
                            "mobile_number"=>$request->phone,
                            "working_at"=>$request->working_at,
                            "city_id"=>$request->city_id,
                            "state"=>$request->state,
                            "country_code"=>$request->country,
                            "bio"=>$request->bio,
                            "language"=>$request->language,
                            "fb_id"=>$request->fb_id,
                            "google_id"=>$request->google_id,
                            "linkdin_id"=>$request->linkedin_id,
                            //"document_type"=>$request->document_type,
                            //"document_number"=>$request->document_number,
                        );
            
                        if($user->official_email != $request->official_email){
                            $data['is_email_verified'] = '0';
                        }
                        if($user->mobile_number != $request->phone){
                            $data['is_mob_verified'] = '0';
                        }
                        if($user->country_code != $request->country){
                            $data['is_doc_verified'] = '0';
                        }
                        
            
                        if($request->file('profile_image')){
                            $image = $request->file('profile_image');
                            if($image->isValid()){
                                $old = $user->profile_image;
                                if(!empty($old)){
                                    if(file_exists($old)){
                                        unlink($old);
                                    }
                                }
            
                                $extension = $image->getClientOriginalExtension();
                                $fileName = rand(100,999999).time().'.'.$extension;
                                $image_path = public_path('assets/users/');
                                $request->profile_image->move($image_path, $fileName);
                                $data['profile_image'] = 'assets/users/'.$fileName;
                            }
                        }
                        
                        if($request->file('document_file')){
                            $image = $request->file('document_file');
                            if($image->isValid()){
                                $old = $user->document_file;
                                if(!empty($old)){
                                    if(file_exists($old)){
                                        unlink($old);
                                    }
                                }
            
                                $extension = $image->getClientOriginalExtension();
                                $fileName = rand(100,999999).time().'.'.$extension;
                                $image_path = public_path('assets/documents/');
                                $request->document_file->move($image_path, $fileName);
                                $data['document_file'] = 'assets/documents/'.$fileName;
                                $data['is_doc_verified'] = '0';
                            }
                        }
                        if($request->file('document_file_back')){
                            $image = $request->file('document_file_back');
                            if($image->isValid()){
                                $old = $user->document_file_back;
                                if(!empty($old)){
                                    if(file_exists($old)){
                                        unlink($old);
                                    }
                                }
            
                                $extension = $image->getClientOriginalExtension();
                                $fileName = rand(100,999999).time().'.'.$extension;
                                $image_path = public_path('assets/documents/');
                                $request->document_file_back->move($image_path, $fileName);
                                $data['document_file_back'] = 'assets/documents/'.$fileName;
                                $data['is_doc_verified'] = '0';
                            }
                        }
                        
                        Users_Model::where('id',$user_id)->update($data);
            
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Profile Updated Successfully','error'=>[]], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Mobile Number Already Taken','error'=>['Mobile Number Already Taken']], 200);
                    }
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Official Email Already Taken','error'=>['Official Email Already Taken']], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Email Already Taken','error'=>['Email Already Taken']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function update_user_profile_image(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            $data = array();
            if($request->file('profile_image')){
                $image = $request->file('profile_image');
                if($image->isValid()){
                    $old = $user->profile_image;
                    if(!empty($old)){
                        if(file_exists($old)){
                            unlink($old);
                        }
                    }

                    $extension = $image->getClientOriginalExtension();
                    $fileName = rand(100,999999).time().'.'.$extension;
                    $image_path = public_path('assets/users/');
                    $request->profile_image->move($image_path, $fileName);
                    $data['profile_image'] = 'assets/users/'.$fileName;
                    
                    Users_Model::where('id',$user_id)->update($data);
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Profile Image Updated Successfully','error'=>[]], 200);
                }
            }else{
                $old = $user->profile_image;
                if(!empty($old)){
                    if(file_exists($old)){
                        unlink($old);
                    }
                }
                $data['profile_image'] = '';
            
                Users_Model::where('id',$user_id)->update($data);
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Profile Image Updated Successfully','error'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    
    
    public function get_settings(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            $data = array(
                "match_notification"=>$user->match_notification,
                "chat_notification"=>$user->chat_notification,
                "deal_notification"=>$user->deal_notification,
                "show_company"=>$user->show_company,
                "show_socials"=>$user->show_social_details,
            );

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>(object)[]], 200);
        }
    }
    public function get_size_settings(Request $request){
        $setings = SettingsModel::where('id','1')->first();
        if(!empty($setings)){
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Success','error'=>[],'data'=>$setings], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Settings Found','error'=>['No Settings Found'],'data'=>(object)[]], 200);
        }
    }
    public function update_settings(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $value = ($request->input('value') == 1)?'1':'0';

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            if($type == 'match'){
                $user->match_notification = $value;
                $user->save();
            }else if($type == 'chat'){
                $user->chat_notification = $value;
                $user->save();
            }else if($type == 'deal'){
                $user->deal_notification = $value;
                $user->save();
            }else if($type == 'show_socials'){
                $user->show_social_details = $value;
                $user->save();
            }else if($type == 'show_company'){
                $user->show_company = $value;
                $user->save();
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type Parameter','error'=>['Invalid Type Parameter']], 200);
            }
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Settings Updated Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }

    }
    public function my_reviews(Request $request){
        $user_id = $request->input('user_id');
        
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $reviews = RatingModel::where('rate_to',$user_id)->latest()->get();
            if(count($reviews) > 0){
                $data = array();
                foreach($reviews as $review){
                    $data[] = array(
                                "id" => $review->id,
                                "rate_to" => $review->rate_to,
                                "rate_by" => $review->rate_by,
                                "rate_by_user_name" => $review->rate_by_user->name,
                                "rate_by_user_image" => $review->rate_by_user->profile_image_url,
                                "rate_by_user_type" => $review->rate_by_type,
                                "rate_to_user_type" => $review->rate_to_type,
                                "rating" => $review->rating_star,
                                "comment" => $review->comment,
                                "deal_id" => $review->deal_id,
                                "shipment_id" => $review->shipment_id,
                                "trip_id" => $review->trip_id,
                                "date" => date('d M Y',strtotime($review->created_at)),
                        );
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Reviews Found','error'=>['No Reviews Found'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    
    public function social_media_verification(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $status = $request->input('status');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            if(($type == 'facebook') || ($type == 'linkdin') || ($type == 'google')){
                if($type == 'facebook'){
                    $user->fb_verify = '1';
                    $user->save();
                }else if($type == 'linkdin'){
                    $user->linkdin_verify = '1';
                    $user->save();
                }else if($type == 'google'){
                    $user->google_verify = '1';
                    $user->save();
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'User Varified Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type Value','error'=>['Type Value']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function get_otp(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $otp = rand(1000,9999);
            //Send Otp to mobile here...
            
            Users_Model::where('id',$user_id)->update(['otp'=>$otp,'otp_time'=>date('Y-m-d H:i:s')]);
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$otp,'error'=>[]], 200);
            
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function verify_otp(Request $request){
        $user_id = $request->input('user_id');
        $otp = $request->input('otp');
        
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $otp_date = new \DateTime($user->otp_time);
            $curr_start = $otp_date->diff(new \DateTime(date('Y-m-d H:i:s')));
            if(($curr_start->i <= 2) && ($curr_start->h == 0) && ($curr_start->days == 0) ){
                if($otp == $user->otp){
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>['Invalid Otp']], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>['Invalid Otp']], 200);
            }
            
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    
    public function save_feedback(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $subject = $request->input('subject');
        $message = $request->input('message');

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            
            FeedbackModel::create(['user_id'=>$user_id,'type'=>$type,'subject'=>$subject,'message'=>$message,]);
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    
    public function set_withdrawal_pin(Request $request){
        $user_id = $request->input('user_id');
        $pin = $request->input('pin');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $user->user_pin = $pin;
            $user->save();
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Pin Updated Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function get_withdrawal_pin(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$user->user_pin], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>''], 200);
        }
    }
    public function forgot_pin(Request $request){
        $user_id = $request->input('user_id');
        
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $pin = rand(1000,9999);
            $user->user_pin = $pin;
            $user->save();
            
            $email = $user->email;
            $data = array(
    			"email"=>$user->email,
    			"otp"=>$pin,
    			"name"=>$user->name,
    		);
            
            try{
    		    Mail::send('emails.forgot_pin', $data, function ($message) use ($email, $data) {
        			$message->from(env('MAIL_FROM_ADDRESS'))->to($data['email'])->subject('Your Town Buddy Pin');
        		});
    		} catch (\Exception $e) {
    		    return response()->json(['status'=>false,'status_code'=>'401','message'=>$e->getMessage(),'error'=>[$e->getMessage()]], 200);
    		}
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'New Pin Sent Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    
    public function isAadharValid($num) {
        settype($num, "string");
        $expectedDigit = substr($num, -1);
        $actualDigit = $this->CheckSumAadharDigit(substr($num, 0, -1));
        return ($expectedDigit == $actualDigit) ? $expectedDigit == $actualDigit : 0;
    }
    
    public function CheckSumAadharDigit($partial) {
        settype($partial, "string");
        $partial = strrev($partial);
        $digitIndex = 0;
        for ($i = 0; $i < strlen($partial); $i++) {
            $dihedral = $this->dihedral;
            $permutation = $this->permutation;
            $digitIndex = $dihedral[$digitIndex][$permutation[($i + 1) % 8][$partial[$i]]];
        }
        $inverse = $this->inverse;
        return $inverse[$digitIndex];
    }

    public function test(Request $request){
        
        $AadharNo = '785547852695';
        $valid = $this->isAadharValid($AadharNo);
        $isValid = false;
        if ($valid == 1) {
            $isValid = true;
        }
        echo $isValid;
        
        die;
        $email = 'sneh.itegrityproindia@gmail.com';
        $data = array(
			"email"=>'sneh.itegrityproindia@gmail.com',
			"otp"=>123456,
			"name"=>'sneh',
			"mobile_number"=>'89745612123',
		);
		Mail::send('emails.email_verification', $data, function ($message) use ($email, $data) {
			$message->from(env('MAIL_FROM_ADDRESS'))->to($data['email'])->subject('Your Town Buddy Email OTP');
		});
        
        die;
        //echo 'In Function....';
        $browsers = ['Opera', 'Mozilla', 'Firefox', 'Chrome', 'Edge'];

        $userAgent = request()->header('User-Agent');

        $isBrowser = false;

        foreach($browsers as $browser){
            if(strpos($userAgent, $browser) !==  false){
            $isBrowser = true;
            break;
            }
        }

        return ['result' => $isBrowser,'browser'=>$userAgent];
    }





}
