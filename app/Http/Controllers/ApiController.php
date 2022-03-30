<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Users_Model;
use App\NotificationModel;
use App\ShipmentModel;
use App\PaymentModel;
use App\TripsModel;
use App\RatingModel;
use App\DealsModel;
use App\HowItWorksModel;
use App\FaqModel;
use Validator;
use Mail;

class ApiController extends Controller
{
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
    public function contact_us(Request $request){
        $email = $request->input('email');
        $subject = $request->input('subject');
        $message = $request->input('message');

        DB::table('contact_us')->insert(
            ['email' => $email, 'subject' => $subject, 'message'=>$message,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s' ) ]
        );

        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Message Sent Successfully, We Will Contact You Soon.','error'=>[]], 200);
    }
    public function report_problem(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $message = $request->input('message');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            DB::table('report_problem')->insert(
                ['user_id' => $user_id, 'type' => $type, 'message'=>$message,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s' ) ]
            );
    
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Message Sent Successfully.','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function get_page(Request $request){
        $page = $request->input('page');
        $page_arr = DB::table('app_page')->where('page_slug',$page)->first();
        
        if(!empty($page_arr)){
            unset($page_arr->created_at);
            unset($page_arr->updated_at);
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Message Sent Successfully.','error'=>[],'data'=>$page_arr], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Page Slug','error'=>['Invalid Page Slug'],'data'=>(object)[]], 200);
        }
    }
    public function get_notifications(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            
            if(($type == 'remainders') || ($type == 'matching_shipment') || ($type == 'matching_trip') || ($type == 'deal') || ($type == 'announcement')){
                $notifications = NotificationModel::where('user_id',$user_id)->has('user')->where('notification_type',$type)->orderBy('id','desc')->get();
                $data = array();
                if(count($notifications) > 0){
                    foreach($notifications as $notification){
                        $remainder_value = '';
                        if($notification->remainder_type == 'transit'){
                            $deal = DealsModel::where('id',$notification->reference_id)->first();
                            if(!empty($deal)){
                                $remainder_value = $deal->shipment->shipment_uniqueid;
                            }
                            
                        }else if($notification->remainder_type == 'arrival'){
                            $deal = DealsModel::where('id',$notification->reference_id)->first();
                            if(!empty($deal)){
                                $remainder_value = $deal->shipment->shipment_uniqueid;
                            }
                        }
                        
                        
                        $data[] = array(
                                    "id" => $notification->id,
                                    "title" => $notification->title,
                                    "message" => $notification->message,
                                    "date" => date('d M Y',strtotime($notification->created_at)),
                                    "time" => date('H:i',strtotime($notification->created_at)),
                                    "seen" => $notification->status,
                                    "type" => ($notification->type != '')?$notification->type:'',
                                    "notification_type" => $notification->notification_type,
                                    "notification_user_type"=>$notification->notification_user_type,
                                    "reference_id" => ($notification->reference_id != '')?$notification->reference_id:'',
                                    "remainder_type" => ($notification->remainder_type != '')?$notification->remainder_type:'',
                                    "remainder_value"=>$remainder_value,
                                    "image" => ($notification->notification_image != '')?$notification->notification_image:$notification->user->profile_image_url,
                            );
                    }
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Notifications Found','error'=>['No Notifications Found'],'data'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type Value','error'=>['Invalid Type Value'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    public function get_notification_count(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            $count = NotificationModel::where('user_id',$user_id)->where('status','0')->count();
            
            $deal_count = NotificationModel::where('notification_type','deal')->where('user_id',$user_id)->where('status','0')->count();
            $announcement_count = NotificationModel::where('notification_type','announcement')->where('user_id',$user_id)->where('status','0')->count();
            $remainder_count = NotificationModel::where('notification_type','remainders')->where('user_id',$user_id)->where('status','0')->count();
            $mactching_ship_count = NotificationModel::where('notification_type','matching_shipment')->where('user_id',$user_id)->where('status','0')->count();
            $mactching_trip_count = NotificationModel::where('notification_type','matching_trip')->where('user_id',$user_id)->where('status','0')->count();
            
            $data = array(
                    "all"=>$count,
                    "deal"=>$deal_count,
                    "announcement"=>$announcement_count,
                    "remainders"=>$remainder_count,
                    "matching_shipment"=>$mactching_ship_count,
                    "matching_trip"=>$mactching_trip_count,
                );
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'count'=>$count,'data'=>$data], 200);
            //return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'count'=>$count], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'count'=>'0'], 200);
        }
    }
    public function seen_notification(Request $request){
        $notification_id = $request->input('notification_id');
        $notification = NotificationModel::where('id',$notification_id)->first();
        
        if(!empty($notification)){
            NotificationModel::where('id',$notification_id)->update(['status'=>'1']);

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Notification Seen Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Notification Id','error'=>['Invalid Notification Id']], 200);
        }
    }
    public function delete_notification(Request $request){
        $notification_id = $request->input('notification_id');
        $notification = NotificationModel::where('id',$notification_id)->first();
        
        if(!empty($notification)){
            NotificationModel::where('id',$notification_id)->delete();

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Notification Deleted Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Notification Id','error'=>['Invalid Notification Id']], 200);
        }
    }
    public function save_payment(Request $request){
        $shipment_id = $request->input('shipment_id');
        $user_id = $request->input('user_id');
        $payment_response_id = $request->input('payment_response_id');
        $payment_response = $request->input('payment_response');
        $payment_status = ($request->input('payment_status') == 1)?'1':'0';

        $shipment = ShipmentModel::where('id',$shipment_id)->has('user')->first();
        if(!empty($shipment)){
            $user = Users_Model::where('id',$user_id)->first();
            if(!empty($user)){
                
                $data = array(
                    "user_id"=>$user_id,
                    "shipment_id"=>$shipment_id,
                    "shipment_quotation"=>$shipment->total_quotation,
                    "payment_response_id"=>$payment_response_id,
                    "payment_response"=>$payment_response,
                    "payment_method"=>'razorpay',
                    "payment_status"=>$payment_status,
                    "payment_date"=>date('Y-m-d H:i:s'),
                );
                PaymentModel::create($data);

                if($payment_status == 1){
                    $shipment->status = 2;
                    $shipment->save();
                    
                    $ship_deals = $shipment->deals;
                    $pay_deal = '';
                    if(count($ship_deals) > 0){
                        foreach($ship_deals as $deal){
                            if($deal->request_status == 1){
                                $trip_id = $deal->trip_id;
                                $pay_deal = $deal;
                                TripsModel::where('id',$trip_id)->update(['trip_status'=>'4']);
                            }
                        }
                    }
                    
                    $notification = new NotificationController();
                    $notification->paymentNotification($shipment,$pay_deal);
                    
                }
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Payment Save Successfully','error'=>[]], 200);

            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipment Id','error'=>['Invalid Shipment Id']], 200);
        }
    }
    
    public function payment_history(Request $request){
        $user_id = $request->input('user_id');
        
        if(!empty($user_id)){
            $payments = PaymentModel::has('shipment')->where('user_id',$user_id)->get();
            if(count($payments) > 0){
                $paymentsArr = array();
                foreach($payments as $payment){
                    $title = '';
                    $img = '';
                    if(count($payment->shipment->products) > 0){
                        $title = $payment->shipment->products[0]->product->product_name;
                        $img = $payment->shipment->products[0]->product->product_image_url;
                    }
                    
                    $paymentsArr[] = array(
                                "id"=>(string)$payment->id,
                                "title"=>$title,
                                "image"=>$img,
                                "quotation"=>$payment->shipment_quotation,
                                "order_id"=>$payment->shipment->shipment_uniqueid,
                                "shipment_id"=>$payment->shipment_id,
                                "shipment_from"=>$payment->shipment->shipment_from,
                                "shipment_to"=>$payment->shipment->shipment_to,
                                "order_placed"=>date('d M Y',strtotime($payment->payment_date)),
                                "delivery_date"=>date('d M Y',strtotime($payment->shipment->expected_delivery_date)),
                                "status"=>($payment->payment_status == 1)?'Success':'Failed',
                            );
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$paymentsArr], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Payment history','error'=>['No Payment history'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'User Id is required','error'=>['User Id is required'],'data'=>[]], 200);
        }
    }
    
    public function rate_user(Request $request){
        $rate_to = $request->input('rate_to');
        $rate_by = $request->input('rate_by');
        $deal_id = $request->input('deal_id');
        $rating_star = $request->input('rating_star');
        $comment = $request->input('comment');
        
        if(!empty($rate_to)){
            if(!empty($rate_by)){
                if(!empty($deal_id)){
                    if(!empty($rating_star)){
                        $deal = DealsModel::where('id',$deal_id)->first();
                        if(!empty($deal)){
                            $check_rating = RatingModel::where('rate_to',$rate_to)->where('rate_by',$rate_by)->where('deal_id',$deal_id)->count();
                            if($check_rating > 0){
                                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Already Rated this user','error'=>['Already Rated this user']], 200);
                            }else{
                                $rate_to_type = '';
                                $rate_by_type = '';
                                if($deal->request_by == $rate_by){
                                    $rate_by_type = $deal->request_by_type;
                                    $rate_to_type = $deal->request_to_type;
                                }else{
                                    $rate_by_type = $deal->request_to_type;
                                    $rate_to_type = $deal->request_by_type;
                                }
                                
                                $arr = array(
                                        "rate_to"=>$rate_to,
                                        "rate_by"=>$rate_by,
                                        "deal_id"=>$deal_id,
                                        "shipment_id"=>$deal->shipment_id,
                                        "trip_id"=>$deal->trip_id,
                                        "rate_to_type"=>$rate_to_type,
                                        "rate_by_type"=>$rate_by_type,
                                        "rating_star"=>$rating_star,
                                        "comment"=>$comment,
                                    );
                                RatingModel::create($arr);
                                return response()->json(['status'=>true,'status_code'=>'200','message'=>'User Rated Successfully','error'=>['User Rated Successfully']], 200);
                            }
                        }else{
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid deal id','error'=>['Invalid deal id']], 200);
                        }
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Rating star field is required','error'=>['Rating star field is required']], 200);
                    } 
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Deal Id field is required','error'=>['Deal Id field is required']], 200);
                } 
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Rate By field is required','error'=>['Rate By field is required']], 200);
            }   
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Rate To field is required','error'=>['Rate To field is required']], 200);
        }
    }
    public function add_withdrawal_request(Request $request){
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $bank_name = $request->input('bank_name');
        $ac_holder_name = $request->input('ac_holder_name');
        $ac_no = $request->input('ac_no');
        $bank_swift_code = $request->input('bank_swift_code');
        $bank_ifsc = $request->input('bank_ifsc');
        $phone_pay_number = $request->input('phone_pay_number');
        $google_pay_number = $request->input('google_pay_number');
        
        $inputs = $request->all();
        $rules = [
            'user_id'           => 'required',
            'amount'            => 'required',
        ];
        $message = [
            'user_id.required'          => 'User Id field is required',
            'amount.required'           => 'Amount field is required',
        ];
        
        $validator = Validator::make($inputs, $rules,$message);
        if ($validator->fails()) {
            $errors =  $validator->errors()->all();
            return response()->json(['status'=>false,'status_code'=>'401','message'=>$errors[0],'error'=>[$errors[0]]], 200);
        }
        
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            if(!($amount > $user->wallet_amount)){
                $pay_ac_detail = array(
                            "bank_name"=>$bank_name,
                            "ac_holder_name"=>$ac_holder_name,
                            "ac_no"=>$ac_no,
                            "bank_swift_code"=>$bank_swift_code,
                            "bank_ifsc"=>$bank_ifsc,
                            "phone_pay_number"=>$phone_pay_number,
                            "google_pay_number"=>$google_pay_number,
                        );
                $user->bank_name = $bank_name;
                $user->ac_holder_name = $ac_holder_name;
                $user->ac_no = $ac_no;
                $user->bank_swift_code = $bank_swift_code;
                $user->bank_ifsc = $bank_ifsc;
                $user->phone_pay_number = $phone_pay_number;
                $user->google_pay_number = $google_pay_number;
                
                $user->save();
                
                $data = array(
                    "type"=>'wallet',
                    "user_id"=>$user_id,
                    "payment_method"=>'razorpay',
                    "shipment_quotation"=>$amount,
                    "pay_ac_detail"=>json_encode($pay_ac_detail),
                    "payment_status"=>'2',
                    "payment_date"=>date('Y-m-d H:i:s'),
                );
                PaymentModel::create($data);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Withdrawal Requested Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Amount should be less than wallet amount','error'=>['Amount should be less than wallet amount']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
        }
    }
    public function upload_file(Request $request){
        $type = $request->input('type');
        $img = "";
        if($type == 'product'){
            if($request->file('image')){
                $image = $request->file('image');
                if($image->isValid()){
                    
                    $extension = $image->extension();
                    $fileName = rand(100,999999).time().'.'.$extension;
                    $image_path = public_path('assets/products/');
                    $request->image->move($image_path, $fileName);
                    $img = 'assets/products/'.$fileName;
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'File Uploaded Successfully','url'=>$img], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid File','url'=>''], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'File Not Found','url'=>''], 200);
            }
        }elseif($type == 'images'){
            if($request->file('image')){
                $image = $request->file('image');
                if($image->isValid()){
                    $extension = $image->extension();
                    
                    $fileName = rand(100,999999).time().'.'.$extension;
                    $image_path = public_path('assets/images/');
                    $request->image->move($image_path, $fileName);
                    $img = 'assets/images/'.$fileName;
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'File Uploaded Successfully','url'=>$img], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid File','url'=>''], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'File Not Found','url'=>''], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type','url'=>''], 200);
        }
    }

    public function delete_file(Request $request){
        $url = $request->input('url');
        if(!empty($url)){
            $img = public_path('/').'/'.$url;
            if(file_exists($img)){
                unlink($img);
            }
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'File Deleted Successfully'], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Url is Required'], 200);
        }
    }
    public function send_document_update_notification(Request $request){
        $id = $request->input('id');
        $document = $request->input('document');
        $user = Users_Model::where('id',$id)->first();
        if(!empty($user)){
            NotificationModel::where('reference_id',$user->id)->where('type','document_unverify')->update(['type'=>'no_action']);
            
            if(($document == '1') || ($document == '0') ){
                $notification = new NotificationController();
                $notification->updateDocument($user,$document);
            }
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Notification Sent Successfully'], 200);
        }
    }
    public function send_email_update_notification(Request $request){
        $id = $request->input('id');
        $email = $request->input('email');
        $user = Users_Model::where('id',$id)->first();
        if(!empty($user)){
            NotificationModel::where('reference_id',$user->id)->where('type','email_unverify')->update(['type'=>'no_action']);
            if(($email == '1') || ($email == '0') ){
                $notification = new NotificationController();
                $notification->updateEmail($user,$email);
            }
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Notification Sent Successfully'], 200);
        }
    }
    public function cancel_deal_by_admin(Request $request){
        $id = $request->input('deal_id');
        $staff_id = $request->input('staff_id');
        $reason = $request->input('reason');
        
        $deal = DealsModel::where('id',$id)->first();
        if(!empty($deal)){
            $deal->request_status = '2';
            $deal->cancel_by = 'system';
            $deal->cancel_reason = $reason;
            $deal->cancel_by_staff_id = $staff_id;
            $deal->save();
            
            
            ShipmentModel::where('id',$deal->shipment_id)->update(['status'=>'6','cancel_reason'=>$reason]);
            TripsModel::where('id',$deal->trip_id)->update(['trip_status'=>'5','cancel_reason'=>$reason]);
            
            $notification = new \App\Http\Controllers\NotificationController();
            $notification->dealCancelBySystem($deal,$reason);
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Deal Cancelled Successfully'], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Deal Id'], 200);
        }
    }
    public function get_how_it_works(Request $request){
        $type = $request->input('type');
        $hit = HowItWorksModel::where('tab',$type)->orderBy('position','ASC')->get();
        
        if(count($hit)){
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$hit], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Not Found','data'=>[]], 200);
        }
    }
    public function get_faq(Request $request){
        $type = $request->input('type');
        $faq = FaqModel::where('tab',$type)->orderBy('position','asc')->get();
        
        if(count($faq)){
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$faq], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Not Found','data'=>[]], 200);
        }
    }
    public function send_report_problem_reply_mail(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $name = $user->name;
            $email = $user->email;
            $message = $request->input('message');
            $reply = $request->input('reply');
            
            $data = array(
    			"name"=>$name,
    			"email"=>$email,
    			"msg"=>$message,
    			"reply"=>$reply,
    		);
    		
            try{
        	    Mail::send('emails.reply-report-problem', $data, function ($message) use ($email, $data) {
        			$message->from(env('MAIL_FROM_ADDRESS'))->to($data['email'])->subject('Regarding Reported Issue On Townbuddy');
        		});
        		
        		return response()->json(['status'=>true,'status_code'=>'200','message'=>'success'], 200);
        	} catch (\Exception $e) {
        	    return response()->json(['status'=>false,'status_code'=>'401','message'=>$e->getMessage(),'error'=>[$e->getMessage()]], 200);
        	}
        }
    }
    
}
