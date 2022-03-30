<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DealsModel;
use App\NotificationModel;
use App\MessageModel;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    
    public function privacy_policy()
    {
        return view('privacy_policy');
    }
    
    public function terms_and_conditions()
    {
        return view('terms_and_conditions');
    }
    
    
    public function cronFunction(Request $request){
        
        
        $msgs = MessageModel::where('seen_status','0')->where('receiver_id','71')->orderBy('id','desc')->get()->unique('sender_id');
        
        foreach($msgs as $msg){
            echo 'Mid -'.$msg->id;
            echo 'Sid -'.$msg->sender->id;
            echo 'Rid -'.$msg->receiver->id;
            echo 'Msg -'.$msg->message;
            echo '<br>';
        }
        
        
        
        
        
        
        die;
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'placed');
                })->whereHas('trip', function($q){
                    $q->whereDate('departure_datetime','>=', date('Y-m-d',strtotime("-1 days")));
                    $q->whereDate('departure_datetime','<=', date('Y-m-d',strtotime("+1 days")));
                })->orderBy('id','desc')->get();
        
        echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->departure_datetime))/3600, 0);
                
                echo $deal->id.'-'.$deal->trip->departure_datetime.'-  '.$timediff.'<br>';
                if($timediff == -24){
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','pickup')
                                        ->where('remainder_time','24')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new NotificationController();
                        $notification->pickupRemainder($deal,'24');
                        
                        //$this->info('24 hour before pickupRemainder send for '.$deal->id);
                    }
                }else if($timediff == -10){
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','pickup')
                                        ->where('remainder_time','10')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new NotificationController();
                        $notification->pickupRemainder($deal,'10');
                        
                        //$this->info('10 hour before pickupRemainder send for '.$deal->id);
                    }
                }else if($timediff == -4){
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','pickup')
                                        ->where('remainder_time','4')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new NotificationController();
                        $notification->pickupRemainder($deal,'4');
                        //$this->info('4 hour before pickupRemainder send for '.$deal->id);
                    }
                    
                    // Send About to depart notification
                    $notification = new NotificationController();
                    $notification->departureTravellerRemainder($deal);
                    
                    //$this->info('4 hour before departureTravellerRemainder send for '.$deal->id);
                }
            }
        }
    }
    public function cronFunction2(Request $request){
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'picked_up');
                })->whereHas('trip', function($q){
                    $q->whereDate('departure_datetime','>=', date('Y-m-d',strtotime("-1 days")));
                    $q->whereDate('departure_datetime','<=', date('Y-m-d',strtotime("+1 days")));
                })->orderBy('id','desc')->get();
        
        echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->departure_datetime))/3600, 0);
                
                echo $deal->id.'-'.$deal->trip->departure_datetime.'-  '.$timediff.'<br>';
                if(($timediff >= 0) || ($timediff <= 1) ){
                    echo $min_diff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->departure_datetime))/60, 0);
                    if($min_diff > 30){
                        $notification = NotificationModel::where('reference_id',$deal->id)
                                            ->where('type','deal')
                                            ->where('remainder_type','transit')
                                            ->where('remainder_time','30')
                                            ->where('notification_type','remainders')->get();
                        if(!(count($notification) > 0)){
                            
                            $notification = new NotificationController();
                            $notification->checkTransitRemainder($deal);
                        }
                        
                    }
                }
            }
        }
    }
    public function cronFunction3(Request $request){
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'transit');
                })->orderBy('id','desc')->get();
        
        echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->arrival_datetime))/3600, 0);
                
                echo $deal->id.'-'.$deal->trip->arrival_datetime.'-  '.$timediff.'<br>';
                if(($timediff >= 1)){
                    
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','arrival')
                                        ->where('remainder_time','1')
                                        ->whereTime('created_at','>',Carbon::now()->subHours(1))
                                        ->where('notification_type','remainders')->get();
                    
                    if(!(count($notification) > 0)){
                        
                        $notification = new NotificationController();
                        $notification->checkArrivalRemainder($deal);
                    }
                }
            }
        }
    }
    public function cronFunction4(Request $request){
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'arrived');
                })->orderBy('id','desc')->get();
        
        //echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->arrival_datetime))/3600, 0);
                
                //echo $deal->id.'-'.$deal->trip->arrival_datetime.'-  '.$timediff.'<br>';
                if(($timediff >= 6)){
                    
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','delivery')
                                        ->where('remainder_time','6')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new NotificationController();
                        $notification->deliveryRemainder($deal);
                    }
                }
            }
        }
    }
    
    
}
