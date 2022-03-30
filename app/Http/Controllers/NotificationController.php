<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Users_Model;
use App\NotificationModel;
use App\ShipmentModel;
use App\TripsModel;
use App\PaymentModel;
use App\DealsModel;

class NotificationController extends Controller
{
    //Define API ACCESS KEY
    private $API_ACCESS_KEY = 'AAAAPXDoPXQ:APA91bGx4ib4ycrsaevMz4tNkR69UIQ9owBgjCkswaWEPMFsgFn1XtY5M6nArhPXXjRN9lsXc86hOQZKpE43nA8dMZVqhUcnoEm6PR0WPxdB1SIf2_LuniJ-czHN4hMyjtT3FxpJYhUn';
    
    //Api Functions
    
    
    public function orderOtpGenerate($shipment,$type,$otp){
        if($type == 'pickup'){
            $title = 'Otp for pickup shipment';
            $message = 'Otp for pickup shipment is '.$otp;
        }else{
            $title = 'Otp for delivery shipment';
            $message = 'Otp for delivery shipment is '.$otp;
        }
        
        $to = $shipment->user->fcm_token;
        $type = 'tracking';
        $ref_id = $shipment->shipment_uniqueid;
        $data = array(
                    "user_id"=>$shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'deal',
                    "notification_user_type"=>"sender",
                    "type"=>$type,
                    "reference_id"=>$ref_id,
                    "notification_image"=>'',
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    
    public function newRequest($deal_id,$shipment,$trip,$user_type){
        $notification_image = '';
        if($user_type == 'shipment'){
            // Request From Shopper/Sender To Traveller
            $title = 'Requested Deal';
            $message = 'Hey '.$trip->user->name.'! You have a request from '.$shipment->user->name.' to carry their shipment from '.$shipment->shipment_from.' to '.$shipment->shipment_to.'.';
            
            $to = $trip->user->fcm_token;
            $user_id = $trip->user->id;
            $type = 'deal';
            $ref_id = $deal_id;
            $notification_image = $shipment->user->profile_image_url;
            $notification_user_type = "traveler";
            $send_notifi = $trip->user->deal_notification;
            
        }else{
            // Request From Traveller To Shopper/Sender
            $title = 'Requested Deal';
            $message = 'Hey '.$shipment->user->name.'! '.$trip->user->name.' wants to carry your shipment that needs to be delivered from '.$shipment->shipment_from.' to '.$shipment->shipment_to.'.';
            
            $to = $shipment->user->fcm_token;
            $user_id = $shipment->user->id;
            $type = 'deal';
            $ref_id = $deal_id;
            $notification_image = $trip->user->profile_image_url;
            $notification_user_type = "sender";
            $send_notifi = $shipment->user->deal_notification;
        }
        $data = array(
                    "user_id"=>$user_id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'deal',
                    "notification_user_type"=>$notification_user_type,
                    "type"=>$type,
                    "reference_id"=>$ref_id,
                    "notification_image"=>$notification_image,
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        if($send_notifi == '1'){
            $is_sent = $this->send($to,$title,$message,$data);
        }
        
    }
    public function cancelShipment($deals){
        $title = 'Cancelled Shipment';
        $message = $deals->shipment->user->name.' has cancelled the shipment from '.$deals->shipment->shipment_from.' to '.$deals->shipment->shipment_to.' scheduled on '.date('d M Y',strtotime($deals->shipment->expected_delivery_date)).' Reason – '.$deals->shipment->cancel_reason;
        
        $notification_image = $deals->shipment->user->profile_image_url;
        $type = "deal";
        $to = $deals->trip->user->fcm_token;
        $send_notifi = $deals->trip->user->deal_notification;
        $data = array(
                    "user_id"=>$deals->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'deal',
                    "notification_user_type"=>"traveler",
                    "type"=>$type,
                    "reference_id"=>$deals->id,
                    "notification_image"=>$notification_image,
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;

        if($send_notifi == '1'){
            $is_sent = $this->send($to,$title,$message,$data);
        }
        
    }
    
    public function cancelTrip($deals){
        $title = 'Cancelled Trip';
        $message = $deals->trip->user->name.' will not be able to deliver your shipment from '.$deals->shipment->shipment_from.' to '.$deals->shipment->shipment_to.' scheduled on '.date('d M Y',strtotime($deals->trip->departure_datetime)).' Reason – '.$deals->trip->cancel_reason;
        
        $notification_image = $deals->trip->user->profile_image_url;
        $type = "deal";
        $to = $deals->shipment->user->fcm_token;
        $send_notifi = $deals->shipment->user->deal_notification;
        $data = array(
                    "user_id"=>$deals->shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'deal',
                    "type"=>$type,
                    "reference_id"=>$deals->id,
                    "notification_image"=>$notification_image,
                    "notification_user_type"=>"sender",
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;

        if($send_notifi == '1'){
            $is_sent = $this->send($to,$title,$message,$data);
        }
        
    }
    
    public function changeDealStatus($deals,$status){
        $notification_image = '';
        if($status == '1'){
            // Deal Accepted
            $title = 'Accepted Deal';
            
            if($deals->request_to_type == 'traveller'){
                $message = 'Hey '.$deals->request_by_user->name.'! '.$deals->request_to_user->name.' has accepted the offer to carry your shipment!';
                $notification_user_type = "sender";
            }else{
                $message = 'Hey '.$deals->request_by_user->name.'! '.$deals->request_to_user->name.' has accepted your request to carry their shipment!';
                $notification_user_type = "traveler";
            }
            
            $notification_image = $deals->request_to_user->profile_image_url;
            $type = "deal";
        }else{
            // Deal Rejected
            $title = 'Your request is rejected';
            
            if($deals->request_to_type == 'traveller'){
                $message = $deals->request_to_user->name.' has rejected your offer to carry your shipment from '.$deals->shipment->shipment_from.' to '.$deals->shipment->shipment_to.'';
                $notification_user_type = "sender";
            }else{
                $message = $deals->request_to_user->name.' has rejected your request to carry their Shipment from '.$deals->shipment->shipment_from.' to '.$deals->shipment->shipment_to.'';
                $notification_user_type = "traveler";
            }
            $notification_image = $deals->request_to_user->profile_image_url;
            $type = "reject_deal";
        }
        
        $to = $deals->request_by_user->fcm_token;
        $send_notifi = $deals->request_by_user->deal_notification;
        if($status == '1'){
            $data = array(
                "user_id"=>$deals->request_by_user->id,
                "title"=>$title,
                "message"=>$message,
                "notification_type"=>'deal',
                "notification_user_type"=>$notification_user_type,
                "type"=>$type,
                "reference_id"=>$deals->id,
                "notification_image"=>$notification_image,
                "remainder_type"=>'',
                "remainder_time"=>'',
                "remainder_value"=>''
            );
        }else{
            $data = array(
                "user_id"=>$deals->request_by_user->id,
                "title"=>$title,
                "message"=>$message,
                "notification_type"=>'deal',
                "notification_user_type"=>$notification_user_type,
                "type"=>$type,
                "reference_id"=>'',
                "notification_image"=>$notification_image,
                "remainder_type"=>'',
                "remainder_time"=>'',
                "remainder_value"=>''
            );
        }
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        if($send_notifi == '1'){
            $is_sent = $this->send($to,$title,$message,$data);
        }
        
    }
    public function cancelDealNotification($cancel_by_user,$notifi_user,$deals){
        $title = 'Cancelled Deal';
        
        if($deals->cancel_by == 'traveller'){
            $message = 'Your deal with '.$cancel_by_user->name.' to carry your shipment from '.$deals->shipment->shipment_from.' to '.$deals->shipment->shipment_to.' on '.date('d M Y',strtotime($deals->shipment->expected_delivery_date)).' has been cancelled by '.$cancel_by_user->name.'. Reason - '.$deals->cancel_reason.'';
            $notification_user_type = "sender";
        }else{
            $message = 'Your deal with '.$cancel_by_user->name.' to carry their shipment on '.date('d M Y',strtotime($deals->shipment->expected_delivery_date)).' has been cancelled by '.$cancel_by_user->name.'. Reason - '.$deals->cancel_reason.'';
            $notification_user_type = "traveler";
        }
        
        $notification_image = $notifi_user->profile_image_url;
        
        $to = $notifi_user->fcm_token;
        $send_notifi = $notifi_user->deal_notification;
        $data = array(
                "user_id"=>$notifi_user->id,
                "title"=>$title,
                "message"=>$message,
                "notification_type"=>'deal',
                "notification_user_type"=>$notification_user_type,
                "type"=>'deal',
                "reference_id"=>$deals->id,
                "notification_image"=>$notification_image,
                "remainder_type"=>'',
                "remainder_time"=>'',
                "remainder_value"=>''
            );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;

        if($send_notifi == '1'){
            $is_sent = $this->send($to,$title,$message,$data);
        }
        
    }
    public function paymentNotification($shipment,$deal){
        
        $title = 'Booked Deal';
        $message = 'Your payment of ₹'.$shipment->total_reward.' is successful and chat feature is now enabled. Once '.$deal->trip->user->name.' clicks on \'Yes\' in the tracker against \'shipment picked-up\' you will receive an OTP. Kindly share the OTP only with '.$deal->trip->user->name.' for pickup confirmation during shipment pickup.';
        
        $to = $shipment->user->fcm_token;
        $type = 'deal';
        $ref_id = $deal->id;
        $data = array(
                    "user_id"=>$shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'deal',
                    "notification_user_type"=>"sender",
                    "type"=>$type,
                    "reference_id"=>$ref_id,
                    "notification_image"=>'',
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
            
        $is_sent = $this->send($to,$title,$message,$data);
        
        
        $title = 'Booked Deal';
        $message = 'Hey '.$deal->trip->user->name.'! Town Buddy has successfully received the payment from '.$shipment->user->name.' and chat feature is now enabled. To generate the OTP click on \'Yes\' in the tracker against \'shipment picked-up\' and enter the OTP received by '.$shipment->user->name.' for pickup confirmation.';
        
        $to = $deal->trip->user->fcm_token;
        $type = 'deal';
        $ref_id = $deal->id;
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'deal',
                    "notification_user_type"=>"traveler",
                    "type"=>$type,
                    "reference_id"=>$ref_id,
                    "notification_image"=>'',
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
            
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    
    public function matchingShipment($shipment){
        
        $title = 'New Matching Shipment!';
        
        $trips = TripsModel::where('trip_status','0')
            ->where('is_created','1')
            ->where('user_id','!=',$shipment->user->id)
            ->where('trip_from',$shipment->shipment_from)
            ->where('trip_to',$shipment->shipment_to)
            ->whereDate('arrival_datetime','<=',$shipment->expected_delivery_date)
            ->whereDate('arrival_datetime','>=',date('Y-m-d'))
            ->has('user')->get();
        if(count($trips) > 0){
            foreach($trips as $trip){
                
                $message = 'Hey '.$trip->user->name.'! A shipment needs to be shipped from '.$shipment->shipment_from.' to '.$shipment->shipment_to.' before '.date('d M Y',strtotime($shipment->expected_delivery_date)).' Would you like to carry the same?';
                
                $to = $trip->user->fcm_token;
                $type = 'shipment';
                $ref_id = $shipment->id;
                $notification_image = $shipment->user->profile_image_url;
                
                $data = array(
                            "user_id"=>$trip->user->id,
                            "title"=>$title,
                            "message"=>$message,
                            "notification_type"=>'matching_shipment',
                            "notification_user_type"=>"traveler",
                            "type"=>$type,
                            "reference_id"=>$ref_id,
                            "notification_image"=>$notification_image,
                            "remainder_type"=>'',
                            "remainder_time"=>'',
                            "remainder_value"=>''
                        );
                //Save Notification in DB
                $notifi_id = NotificationModel::create($data)->id;
                $data['id'] = $notifi_id;
                
                if($trip->user->match_notification == '1'){
                    $is_sent = $this->send($to,$title,$message,$data);
                }
            }
        }
    }
    public function matchingShipmentOnEdit($shipment){
        
        $title = 'New matching shipment! (Edited)';
        $message = $shipment->user->name.' has updated the Shipment details which needs to be delivered from '.$shipment->shipment_from.' to '.$shipment->shipment_to.' before '.date('d M Y',strtotime($shipment->expected_delivery_date)).'';
        
        $trips = TripsModel::where('trip_status','0')
            ->where('is_created','1')
            ->where('user_id','!=',$shipment->user->id)
            ->where('trip_from',$shipment->shipment_from)
            ->where('trip_to',$shipment->shipment_to)
            ->whereDate('arrival_datetime','<=',$shipment->expected_delivery_date)
            ->whereDate('arrival_datetime','>=',date('Y-m-d'))
            ->has('user')->get();
        if(count($trips) > 0){
            foreach($trips as $trip){
                
                $deal = DealsModel::where('trip_id',$trip->id)->where('shipment_id',$shipment->id)->where('request_status','2')->has('shipment')->has('trip')->first();
                if(empty($deal)){
                    $to = $trip->user->fcm_token;
                    $type = 'shipment';
                    $ref_id = $shipment->id;
                    $notification_image = $shipment->user->profile_image_url;
                    $data = array(
                                "user_id"=>$trip->user->id,
                                "title"=>$title,
                                "message"=>$message,
                                "notification_type"=>'matching_shipment',
                                "notification_user_type"=>"sender",
                                "type"=>$type,
                                "reference_id"=>$ref_id,
                                "notification_image"=>$notification_image,
                                "remainder_type"=>'',
                                "remainder_time"=>'',
                                "remainder_value"=>''
                            );
                    //Save Notification in DB
                    $notifi_id = NotificationModel::create($data)->id;
                    $data['id'] = $notifi_id;
                    
                    if($trip->user->match_notification == '1'){
                        $is_sent = $this->send($to,$title,$message,$data);
                    }
                    
                }
            }
        }
    }
    public function matchingTrip($trip){
        
        $title = 'New Matching Trip!';
        
        $shipments = ShipmentModel::has('products')
            ->where('status','1')
            ->where('is_created','1')
            ->where('created_by','!=',$trip->user->id)
            ->where('shipment_from',$trip->trip_from)
            ->where('shipment_to',$trip->trip_to)
            ->whereDate('expected_delivery_date','>=',date('Y-m-d',strtotime($trip->arrival_datetime)))
            ->whereDate('expected_delivery_date','>=',date('Y-m-d'))
            ->orderBy('id', 'desc')->get();
            
        if(count($shipments) > 0){
            $data = array();
            foreach($shipments as $shipment){
                
                $message = 'Hey '.$shipment->user->name.'! '.$trip->user->name.' is traveling from '.$trip->trip_from.' To '.$trip->trip_to.' on '.date('d M Y',strtotime($trip->departure_datetime)).'. Would you like to send your shipment with '.$trip->user->name.'?';
                
                $to = $shipment->user->fcm_token;
                $type = 'trip';
                $ref_id = $trip->id;
                $notification_image = $trip->user->profile_image_url;
                $data = array(
                            "user_id"=>$shipment->user->id,
                            "title"=>$title,
                            "message"=>$message,
                            "notification_type"=>'matching_trip',
                            "type"=>$type,
                            "reference_id"=>$ref_id,
                            "notification_image"=>$notification_image,
                            "notification_user_type"=>"sender",
                            "remainder_type"=>'',
                            "remainder_time"=>'',
                            "remainder_value"=>''
                        );
                //Save Notification in DB
                $notifi_id = NotificationModel::create($data)->id;
                $data['id'] = $notifi_id;
                
                if($shipment->user->match_notification == '1'){
                    $is_sent = $this->send($to,$title,$message,$data);
                }
                
                
            }
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }
    }
    public function matchingTripOnEdit($trip){
        
        $title = 'New Matching trip! (Edited)';
        $message = ''.$trip->user->name.' has updated the trip details will travel from '.$trip->trip_from.' To '.$trip->trip_to.' on '.date('d M Y',strtotime($trip->departure_datetime)).'';
        
        $shipments = ShipmentModel::has('products')
            ->where('status','1')
            ->where('is_created','1')
            ->where('created_by','!=',$trip->user->id)
            ->where('shipment_from',$trip->trip_from)
            ->where('shipment_to',$trip->trip_to)
            ->whereDate('expected_delivery_date','>=',date('Y-m-d',strtotime($trip->arrival_datetime)))
            ->whereDate('expected_delivery_date','>=',date('Y-m-d'))
            ->orderBy('id', 'desc')->get();
            
        if(count($shipments) > 0){
            $data = array();
            foreach($shipments as $shipment){
                
                $deal = DealsModel::where('trip_id',$trip->id)->where('shipment_id',$shipment->id)->where('request_status','2')->has('shipment')->has('trip')->first();
                if(empty($deal)){
                    $to = $shipment->user->fcm_token;
                    $type = 'trip';
                    $ref_id = $trip->id;
                    $notification_image = $trip->user->profile_image_url;
                    $data = array(
                                "user_id"=>$shipment->user->id,
                                "title"=>$title,
                                "message"=>$message,
                                "notification_type"=>'matching_trip',
                                "type"=>$type,
                                "reference_id"=>$ref_id,
                                "notification_image"=>$notification_image,
                                "notification_user_type"=>"sender",
                                "remainder_type"=>'',
                                "remainder_time"=>'',
                                "remainder_value"=>''
                            );
                    //Save Notification in DB
                    $notifi_id = NotificationModel::create($data)->id;
                    $data['id'] = $notifi_id;

                    if($shipment->user->match_notification == '1'){
                        $is_sent = $this->send($to,$title,$message,$data);
                    }
                }
            }
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }
    }
    public function orderTracking($shipment,$order_status){
        
        if($order_status == 'picked_up'){
            
            $deals = $shipment->deals;
            if(count($deals) > 0){
                foreach($deals as $deal){
                    if($deal->request_status == 1){
                        
                        //To Shipment Owner
                        $title = 'Shipment pick-up successful!';
                        $message = 'Your shipment has been picked up successfully by '.$deal->trip->user->name.' on '.date('d M Y').' at '.date('h.ia').'';
                        
                        $to = $shipment->user->fcm_token;
                        $type = 'tracking';
                        $ref_id = $shipment->shipment_uniqueid;
                        $data = array(
                                    "user_id"=>$shipment->user->id,
                                    "title"=>$title,
                                    "message"=>$message,
                                    "notification_type"=>'deal',
                                    "type"=>$type,
                                    "reference_id"=>$ref_id,
                                    "notification_user_type"=>"sender",
                                    "notification_image"=>'',
                                    "remainder_type"=>'',
                                    "remainder_time"=>'',
                                    "remainder_value"=>''
                                );
                        //Save Notification in DB
                        $notifi_id = NotificationModel::create($data)->id;
                        $data['id'] = $notifi_id;
                            
                        $is_sent = $this->send($to,$title,$message,$data);
                        
                        //To Traveller
                        $title = 'Shipment pick-up successful!';
                        $message = 'You have successfully collected the shipment from '.$shipment->user->name.' on '.date('d M Y').' at '.date('h.ia').'';
                        
                        $to = $deal->trip->user->fcm_token;
                        $type = 'tracking';
                        $ref_id = $shipment->shipment_uniqueid;
                        $data = array(
                                    "user_id"=>$deal->trip->user->id,
                                    "title"=>$title,
                                    "message"=>$message,
                                    "notification_type"=>'deal',
                                    "type"=>$type,
                                    "reference_id"=>$ref_id,
                                    "notification_image"=>'',
                                    "notification_user_type"=>"traveler",
                                    "remainder_type"=>'',
                                    "remainder_time"=>'',
                                    "remainder_value"=>''
                                );
                        //Save Notification in DB
                        $notifi_id = NotificationModel::create($data)->id;
                        $data['id'] = $notifi_id;

                        $is_sent = $this->send($to,$title,$message,$data);
                        
                    }
                }
            }
        }else if($order_status == 'transit'){
            $deals = $shipment->deals;
            if(count($deals) > 0){
                foreach($deals as $deal){
                    if($deal->request_status == 1){
                        
                        //To Shipment Owner
                        $title = 'Shipment left '.$shipment->shipment_from;
                        $message = 'Your shipment left '.$shipment->shipment_from.'. please notify '.$shipment->person_name_delivery.' about the update.';
                        
                        $to = $shipment->user->fcm_token;
                        $type = 'tracking';
                        $ref_id = $shipment->shipment_uniqueid;
                        $data = array(
                                    "user_id"=>$shipment->user->id,
                                    "title"=>$title,
                                    "message"=>$message,
                                    "notification_type"=>'deal',
                                    "notification_user_type"=>"sender",
                                    "type"=>$type,
                                    "reference_id"=>$ref_id,
                                    "notification_image"=>'',
                                    "remainder_type"=>'',
                                    "remainder_time"=>'',
                                    "remainder_value"=>''
                                );
                        //Save Notification in DB
                        $notifi_id = NotificationModel::create($data)->id;
                        $data['id'] = $notifi_id;

                        $is_sent = $this->send($to,$title,$message,$data);
                        
                    }
                }
            }
        }else if($order_status == 'arrived'){
            $deals = $shipment->deals;
            if(count($deals) > 0){
                foreach($deals as $deal){
                    if($deal->request_status == 1){
                        
                        //To Shipment Owner
                        $title = 'Shipment arrived at '.$shipment->shipment_to;
                        $message = 'Your shipment has arrived at '.$shipment->shipment_to.'. please notify '.$shipment->person_name_delivery.' to connect with the traveler to collect the shipment.';
                        
                        $to = $shipment->user->fcm_token;
                        $type = 'tracking';
                        $ref_id = $shipment->shipment_uniqueid;
                        $data = array(
                                    "user_id"=>$shipment->user->id,
                                    "title"=>$title,
                                    "message"=>$message,
                                    "notification_type"=>'deal',
                                    "notification_user_type"=>"sender",
                                    "type"=>$type,
                                    "reference_id"=>$ref_id,
                                    "notification_image"=>'',
                                    "remainder_type"=>'',
                                    "remainder_time"=>'',
                                    "remainder_value"=>''
                                );
                        //Save Notification in DB
                        $notifi_id = NotificationModel::create($data)->id;
                        $data['id'] = $notifi_id;

                        $is_sent = $this->send($to,$title,$message,$data);
                        
                    }
                }
            }
        }
        else if($order_status == 'delivery'){
            $deals = $shipment->deals;
            if(count($deals) > 0){
                foreach($deals as $deal){
                    if($deal->request_status == 1){
                        
                        //To Shipment Owner
                        $title = 'Shipment delivery successful!';
                        $message = 'Your shipment has been delivered successfully to '.$deal->shipment->person_name_delivery.' on '.date('d M Y').' at '.date('h.ia').'';
                        
                        $to = $shipment->user->fcm_token;
                        $type = 'tracking';
                        $ref_id = $shipment->shipment_uniqueid;
                        $data = array(
                                    "user_id"=>$shipment->user->id,
                                    "title"=>$title,
                                    "message"=>$message,
                                    "notification_type"=>'deal',
                                    "notification_user_type"=>"sender",
                                    "type"=>$type,
                                    "reference_id"=>$ref_id,
                                    "notification_image"=>'',
                                    "remainder_type"=>'',
                                    "remainder_time"=>'',
                                    "remainder_value"=>''
                                );
                        //Save Notification in DB
                        $notifi_id = NotificationModel::create($data)->id;
                        $data['id'] = $notifi_id;
                            
                        $is_sent = $this->send($to,$title,$message,$data);
                        
                        
                        //To Traveller
                        $title = 'Shipment delivery successful!';
                        $message = 'You have successfully delivered the shipment to '.$deal->shipment->person_name_delivery.' on '.date('d M Y').' at '.date('h.ia').'';
                        
                        $to = $deal->trip->user->fcm_token;
                        $type = 'tracking';
                        $ref_id = $shipment->shipment_uniqueid;
                        $data = array(
                                    "user_id"=>$deal->trip->user->id,
                                    "title"=>$title,
                                    "message"=>$message,
                                    "notification_type"=>'deal',
                                    "notification_user_type"=>"traveler",
                                    "type"=>$type,
                                    "reference_id"=>$ref_id,
                                    "notification_image"=>'',
                                    "remainder_type"=>'',
                                    "remainder_time"=>'',
                                    "remainder_value"=>''
                                );
                        //Save Notification in DB
                        $notifi_id = NotificationModel::create($data)->id;
                        $data['id'] = $notifi_id;

                        $is_sent = $this->send($to,$title,$message,$data);
                        
                    }
                }
            }
        }
    }
    
    //Remainder Notifications
    public function pickupRemainder($deal,$remainder_time){
        //To Sender
        $title = 'Get your shipment ready!';
        $message = 'Hey '.$deal->shipment->user->name.'! Please handover your shipment to '.$deal->trip->user->name.' before '.date('d M Y',strtotime($deal->trip->departure_datetime)).' at '.date('h.ia',strtotime($deal->trip->departure_datetime)).'.  Once '.$deal->trip->user->name.' clicks on \'Yes\' in the tracker against \'shipment picked-up\' you will receive an OTP. Kindly share the same only with '.$deal->trip->user->name.' for pickup confirmation.';
        
        $to = $deal->shipment->user->fcm_token;
        $notification_image_system = url('assets/system.png');
        $data = array(
                    "user_id"=>$deal->shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "type"=>'deal',
                    "reference_id"=>$deal->shipment->shipment_uniqueid,
                    "notification_user_type"=>"sender",
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'pickup',
                    "remainder_time"=>$remainder_time,
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
        //To Traveller
        $title = 'Pickup Reminder!';
        $message = 'Please collect the shipment from '.$deal->shipment->user->name.' before '.date('d M Y',strtotime($deal->trip->departure_datetime)).' at '.date('h.ia',strtotime($deal->trip->departure_datetime)).'. To generate the OTP click on \'Yes\' in the tracker against \'shipment picked-up\' and enter the OTP received by '.$deal->shipment->user->name.' for pickup confirmation.';
        
        $to = $deal->trip->user->fcm_token;
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"traveler",
                    "type"=>'deal',
                    //"reference_id"=>$deal->id, //old
                    "reference_id"=>$deal->shipment->shipment_uniqueid,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'pickup',
                    "remainder_time"=>$remainder_time,
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;

        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    
    public function departureTravellerRemainder($deal){
        $title = 'Have a safe journey!';
        $message = 'You are about to depart in another 4 hours. Please carry the shipment, Travel safe and Happy journey!';
        
        $to = $deal->trip->user->fcm_token;
        $notification_image_system = url('assets/system.png');
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_user_type"=>"traveler",
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'departure_traveller',
                    "remainder_time"=>'4',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    public function checkTransitRemainder($deal){
        $title = 'Have you boarded ?';
        $message = 'Hi '.$deal->trip->user->name.', have you boarded your '.ucfirst($deal->trip->transport_type).' ?';
        
        $to = $deal->trip->user->fcm_token;
        $notification_image_system = url('assets/system.png');
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"traveler",
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'transit',
                    "remainder_time"=>'45',
                    "remainder_value"=>$deal->shipment->shipment_uniqueid
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    public function checkArrivalRemainder($deal){
        $title = 'Have you arrived ?';
        $message = 'Hey '.$deal->trip->user->name.', hope you had a safe journey! Please confirm your arrival at '.$deal->trip->to->city;
        
        $to = $deal->trip->user->fcm_token;
        $notification_image_system = url('assets/system.png');
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"traveler",
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'arrival',
                    "remainder_time"=>'1',
                    "remainder_value"=>$deal->shipment->shipment_uniqueid
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    public function deliveryRemainder($deal){
        $title = 'Delivery reminder!';
        $message = 'Hey '.$deal->trip->user->name.', Please deliver the shipment to '.$deal->shipment->person_name_delivery.' before '.date('d M Y',strtotime($deal->shipment->expected_delivery_date)).' When you click \'Yes\' on the \'shipment delivered\' in the tracker, '.$deal->shipment->person_name_delivery.' will receive an OTP, and you can confirm the shipment delivery by entering the OTP.';
        
        $to = $deal->trip->user->fcm_token;
        $notification_image_system = url('assets/system.png');
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"traveler",
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'delivery',
                    "remainder_time"=>'3',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    
    public function delayRemainder($deal,$type){
        $title = ucfirst($deal->trip->transport_type).' delayed';
        
        if($type == '1'){
            $message = 'Hey '.$deal->shipment->user->name.', the scheduled departure of '.$deal->trip->transport_type.' has been delayed. We will notify you when the shipment is in transit.';
        }else if($type == '2'){
            $message = 'Hey '.$deal->shipment->user->name.', the scheduled '.$deal->trip->transport_type.' has been cancelled. We will keep you notified with further updates.';
        }else if($type == '3'){
            $message = 'Hey '.$deal->shipment->user->name.', there was a last moment change in '.$deal->trip->user->name.' travel plan. We will keep you notified with future updates.';
        }
        
        $to = $deal->shipment->user->fcm_token;
        $notification_image_system = url('assets/system.png');
        $data = array(
                    "user_id"=>$deal->shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"sender",
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'delay',
                    "remainder_time"=>'1',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    
    public function missedChatNotification($user,$msg){
        $title = $msg->sender->name;
        $message = $msg->message;
        
        $to = $user->fcm_token;
        
        if($user->chat_notification == '1'){
            $data = array(
                    "user_id"=>$user->id,
                    "deal_id"=>$msg->deal_id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'chat',
                    "type"=>'chat',
                    "reference_id"=>$msg->sender->id,
                    "notification_image"=>'',
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        
            $is_sent = $this->send($to,$title,$message,$data);
        }
        
    }
    public function dealCancelBySystem($deal,$reason=''){
        //For traveller
        $title = 'Cancelled Deal By System';
        if($reason == ''){
            $message = 'Your deal with '.$deal->shipment->user->name.' of carrying their shipment from '.$deal->shipment->shipment_from.' to '.$deal->shipment->shipment_to.' has been cancelled. Reason - Shipment handover to traveller before departure date was unsuccessful';
        }else{
            $message = 'Your deal with '.$deal->shipment->user->name.' of carrying their shipment from '.$deal->shipment->shipment_from.' to '.$deal->shipment->shipment_to.' has been cancelled. Reason - '.$reason;
        }
        
        
        $to = $deal->trip->user->fcm_token;
        
        $notification_image_system = url('assets/system.png');
        
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"traveler",
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'cancel_deal_system',
                    "remainder_time"=>'1',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        $is_sent = $this->send($to,$title,$message,$data);
        
        /*
        //For traveller
        $title = 'Cancelled Trip By System';
        $message = 'Your deal with '.$deal->shipment->user->name.' of carrying their shipment from '.$deal->shipment->shipment_from.' to '.$deal->shipment->shipment_to.' has been cancelled. Reason - Shipment handover to traveller before departure date was unsuccessful';
        
        $to = $deal->trip->user->fcm_token;
        $data = array(
                    "user_id"=>$deal->trip->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"traveler",
                    "type"=>'deal',
                    "reference_id"=>$deal->trip->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'cancel_trip_system',
                    "remainder_time"=>'1',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        $is_sent = $this->send($to,$title,$message,$data);
        */
        
        //For Sender/Shopper
        $title = 'Cancelled Deal By System';
        if($reason == ''){
            $message = 'Your deal with '.$deal->trip->user->name.' of carrying your shipment from '.$deal->shipment->shipment_from.' to '.$deal->shipment->shipment_to.' has been cancelled. Reason - Shipment handover to traveller before departure date was unsuccessful';
        }else{
            $message = 'Your deal with '.$deal->trip->user->name.' of carrying your shipment from '.$deal->shipment->shipment_from.' to '.$deal->shipment->shipment_to.' has been cancelled. Reason - '.$reason;
        }
        
        
        $to = $deal->shipment->user->fcm_token;
        $data = array(
                    "user_id"=>$deal->shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"sender",
                    "type"=>'deal',
                    "reference_id"=>$deal->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'cancel_deal_system',
                    "remainder_time"=>'1',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        $is_sent = $this->send($to,$title,$message,$data);
        
        /*
        //For Sender/Shopper
        $title = 'Cancelled Shipment By System';
        $message = 'Your deal with '.$deal->trip->user->name.' of carrying your shipment from '.$deal->shipment->shipment_from.' to '.$deal->shipment->shipment_to.' has been cancelled. Reason - Shipment handover to traveller before departure date was unsuccessful';
        
        $to = $deal->shipment->user->fcm_token;
        $data = array(
                    "user_id"=>$deal->shipment->user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "notification_user_type"=>"sender",
                    "type"=>'deal',
                    "reference_id"=>$deal->shipment->id,
                    "notification_image"=>$notification_image_system,
                    "remainder_type"=>'cancel_shipment_system',
                    "remainder_time"=>'1',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        $is_sent = $this->send($to,$title,$message,$data);
        */
    }
    public function updateDocument($user,$document){
        
        if($document == 1){
            $title = 'Document Verified!';
            $message = 'Hey '.$user->name.', Your documents are verified!';
            $type = "document_verify";
        }else{
            $title = 'Document Unverified!';
            $message = 'Hey '.$user->name.', Your documents are unverified! please upload your correct documents';
            $type = "document_unverify";
        }
        
        $to = $user->fcm_token;
        $data = array(
                    "user_id"=>$user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "type"=>$type,
                    "reference_id"=>$user->id,
                    "notification_user_type"=>"sender",
                    "notification_image"=>'',
                    "remainder_type"=>$type,
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    public function updateEmail($user,$document){
        
        if($document == 1){
            $title = 'Organizational Email ID Verified!';
            $message = 'Hey '.$user->name.', Your office email is verified!';
            $type = "email_verify";
        }else{
            $title = 'Organizational Email ID Unverified!';
            $message = 'Hey '.$user->name.', Your office email id is unverified. Please enter the correct organizational maild ID & verify it again. Thank you!!';
            $type = "email_unverify";
        }
        
        $to = $user->fcm_token;
        $data = array(
                    "user_id"=>$user->id,
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'remainders',
                    "type"=>$type,
                    "reference_id"=>$user->id,
                    "notification_user_type"=>"sender",
                    "notification_image"=>'',
                    "remainder_type"=>$type,
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
        $data['id'] = $notifi_id;
        
        $is_sent = $this->send($to,$title,$message,$data);
        
    }
    
    public function newEmailVerifyAdmin($user){
        $title = 'Organization Email Id Verification';
        $message = 'Kindly review and verify '.$user->name.' office email id.';
        
        $data = array(
                    "user_id"=>'1',
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'admin',
                    "type"=>'user',
                    "reference_id"=>$user->id,
                    "notification_user_type"=>"sender",
                    "notification_image"=>'',
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
    }
    public function newDocumentVerifyAdmin($user){
        $title = 'Document Verification';
        $message = 'Kindly review and verify '.$user->name.' aadhar document.';
        
        $data = array(
                    "user_id"=>'1',
                    "title"=>$title,
                    "message"=>$message,
                    "notification_type"=>'admin',
                    "type"=>'user',
                    "reference_id"=>$user->id,
                    "notification_user_type"=>"sender",
                    "notification_image"=>'',
                    "remainder_type"=>'',
                    "remainder_time"=>'',
                    "remainder_value"=>''
                );
        //Save Notification in DB
        $notifi_id = NotificationModel::create($data)->id;
    }
    
    
    // Send Notification
    public function send($to,$title,$message,$data){
        #prep the bundle
        $msg = array(
    		'body' 	=> $message,
    		"OrganizationId"=>2,
    		"content_available" => true,
    		'title'	=> $title,
    		'subtitle'=>'Subtitle Notifi',
         	'icon'	=> 'myicon',/*Default Icon*/
          	'sound' => 'mySound',/*Default sound*/
          	'click_action'=>'SOMEACTIVITY'
            );

    	$fields = array(
    				'to'		=> $to,
    				'notification'	=> $msg,
    				'data'  => $data
    			    );
    	$headers = array(
    				'Authorization: key=' . $this->API_ACCESS_KEY,
    				'Content-Type: application/json'
    			    );
        #Send Reponse To FireBase Server	
		
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
    
        #Echo Result Of FireBase Server
        $res = json_decode($result,true);
        if($res){
            if($res['success'] == 1){
                return true;
            }else{
                return false;
            }
        }
        
    }
    public function send_chat_notification(){
        //$fcm_token = $request->fcm_token;
        
        $registrationIds = 'fslVoah1RCeJQcleQ9fm0d:APA91bGgUinyb6RLxriX3lmA4xonyS9afvpbWLmENvyooSqLokR8m1QtlRck7vilxLoZx6fGejD-Yvz3rFMDpXbGyQEa9ZTISj-691iFxW7I_b9Icck5yCuw46CFqZE1PmOJJGMFp13s';
        
        #prep the bundle
        $msg = array
              (
    		'body' 	=> 'Check Reminder is Working!',
    		"OrganizationId"=>2,
    		"content_available" => true,
    		'title'	=> 'Check Reminder is Working!',
    		'subtitle'=>'Subtitle Notifi',
         	'icon'	=> 'myicon',/*Default Icon*/
          	'sound' => 'mySound'/*Default sound*/
              );
    
        $data = array(
                    "id"=>'5',
                    "user_id"=>'96',
                    "title"=>'Requested Deal',
                    "message"=>'You have an offer from Tony Stark to carry your Shipment from Indore to Bengaluru.',
                    "notification_type"=>'deal',
                    "type"=>'deal',
                    "reference_id"=>'68',
                    "remainder_type"=>'',
                    "remainder_time"=>''
                );
    	$fields = array
    			(
    				'to'		=> $registrationIds,
    				'notification'	=> $msg,
    				'data'=>$data
    			);
    	
    	$headers = array
    			(
    				'Authorization: key=' . $this->API_ACCESS_KEY,
    				'Content-Type: application/json'
    			);
    
        #Send Reponse To FireBase Server	
    		$ch = curl_init();
    		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    		curl_setopt( $ch,CURLOPT_POST, true );
    		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    		$result = curl_exec($ch );
    		curl_close( $ch );
    
        #Echo Result Of FireBase Server
        $res = json_decode($result,true);
        return response()->json(['status'=>true,'status_code'=>'200','data'=>$res], 200);
       // echo json_encode(array("message"=>"success"));
    }
    public function testNotification(Request $request){
        $fcm_token = $request->fcm_token;
        
        if($fcm_token != ''){
            $registrationIds = $fcm_token;
        }else{
            $registrationIds = 'dqeKZJFQRMilsYfQlRdbNq:APA91bGYAMC9UrhhClFOtzguxb7AFFcE3WMdojqF-s0A0N9FhJe95QJlnBdw6dvchp4EeKbejpzATBj0sErZSDZ0gM2fcVNnlIoKNmGbEYquXcpWqY0t_A-BrUey5FosvfEXWOAnTpRt';
        }
        
        
        #prep the bundle
        $msg = array
              (
    		'body' 	=> 'Town Buddy Test',
    		"OrganizationId"=>2,
    		"content_available" => true,
    		'title'	=> 'Town Buddy Test Notification',
    		'subtitle'=>'Subtitle Notifi',
         	'icon'	=> 'myicon',/*Default Icon*/
          	'sound' => 'mySound',/*Default sound*/
          	'click_action'=>'SOMEACTIVITY'
              );
    
        $data = array(
                    "id"=>'5',
                    "user_id"=>'96',
                    "title"=>'Requested Deal',
                    "message"=>'You have an offer from Tony Stark to carry your Shipment from Indore to Bengaluru.',
                    "notification_type"=>'deal',
                    "type"=>'deal',
                    "reference_id"=>'68',
                    "remainder_type"=>'',
                    "remainder_time"=>''
                );
    	$fields = array
    			(
    				'to'		=> $registrationIds,
    				'notification'	=> $msg,
    				'data'=>$data
    			);
    	
    	$headers = array
    			(
    				'Authorization: key=' . $this->API_ACCESS_KEY,
    				'Content-Type: application/json'
    			);
    
        #Send Reponse To FireBase Server	
    		$ch = curl_init();
    		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    		curl_setopt( $ch,CURLOPT_POST, true );
    		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    		$result = curl_exec($ch );
    		curl_close( $ch );
    
        #Echo Result Of FireBase Server
        $res = json_decode($result,true);
        return response()->json(['status'=>true,'status_code'=>'200','data'=>$res], 200);
       // echo json_encode(array("message"=>"success"));
    }
}
