<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProductsModel;
use App\Users_Model;
use App\ShipmentModel;
use App\ShipmentProductModel;
use App\TripsModel;
use App\DealsModel;
use App\CategoryModel;
use App\MessageModel;
use App\NotificationModel;
use App\RatingModel;

class DealsController extends Controller
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

    //Api functions
    public function add_request(Request $request){
        $shipment_id = $request->input('shipment_id');
        $trip_id = $request->input('trip_id');
        $request_by = $request->input('request_by');
        $user_type = $request->input('user_type');

        $shipment = ShipmentModel::where('id',$shipment_id)->has('user')->first();
        if(!empty($shipment)){
            $trip = TripsModel::where('id',$trip_id)->has('user')->first();

            if(!empty($trip)){
                $user = Users_Model::where('id',$request_by)->first();

                if(!empty($user)){
                    if($user_type == 'shipment'){
                        $deal = DealsModel::where('request_status','!=','2')->where('shipment_id',$shipment_id)->where('trip_id',$trip_id)->where('request_by',$request_by)->where('request_to',$trip->user_id)->count();
                        
                        if($deal > 0){
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Already Requested','error'=>['Already Requested']], 200);
                        }else{
                            $data = array(
                                "shipment_id"=>$shipment_id,
                                "trip_id"=>$trip_id,
                                "request_by"=>$request_by,
                                "request_to"=>$trip->user_id,
                                "request_by_type"=>$shipment->shipment_user_type,
                                "request_to_type"=>'traveller',
                                "status"=>'0',
                            );
                            $deal_id = DealsModel::create($data)->id;
                            
                            $notification = new NotificationController();
                            $notification->newRequest($deal_id,$shipment,$trip,$user_type);
                        
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Requested Successfully','error'=>[]], 200);
                        }

                    }else if($user_type == 'trip'){
                        
                        $deal = DealsModel::where('request_status','!=','2')->where('shipment_id',$shipment_id)->where('trip_id',$trip_id)->where('request_by',$request_by)->where('request_to',$shipment->created_by)->count();
                        
                        if($deal > 0){
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Already Requested','error'=>['Already Requested']], 200);
                        }else{
                            $data = array(
                                "shipment_id"=>$shipment_id,
                                "trip_id"=>$trip_id,
                                "request_by"=>$request_by,
                                "request_to"=>$shipment->created_by,
                                "request_by_type"=>'traveller',
                                "request_to_type"=>$shipment->shipment_user_type,
                                "status"=>'0',
                            );
                            $deal_id = DealsModel::create($data)->id;
                            
                            $notification = new NotificationController();
                            $notification->newRequest($deal_id,$shipment,$trip,$user_type);
                            
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Requested Successfully','error'=>[]], 200);
                        }
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Type','error'=>['Invalid User Type']], 200);
                    }

                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id']], 200);
                }

            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip id','error'=>['Invalid Trip id']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipments Id','error'=>['Invalid Shipments Id']], 200);
        }
    }
    public function get_deals(Request $request){
        $deal_type = $request->input('deal_type');
        $type = $request->input('type');
        $user_id = $request->input('user_id');
        $is_booked = $request->input('is_booked');
        $is_accepted = $request->input('is_accepted');

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            if( ($deal_type == 'request') || ($deal_type == 'accepted') || ($deal_type == 'cancelled') ){
                if( ($type == 'sender') || ($type == 'shopper') || ($type == 'traveller') ){
                    if($deal_type == 'request'){
                        $deals = DealsModel::where('request_to',$user_id)->where('request_status','0')->where('request_by_type',$type)->orderBy('id','desc')->get();
                        $data = array();
                        if(count($deals) > 0){
                            foreach($deals as $deal){
                                $status = 'Requests';
                                
                                if($deal->request_status == 1){
                                    $status = 'Accepted';
                                }
                                if($deal->request_status == 2){
                                    $status = 'Cancelled';
                                }
                                if($deal->shipment->status == 0){
                                    $status = 'Awaiting Approval';
                                }else if($deal->shipment->status == 2){
                                    $status = 'Booked';
                                }else if($deal->shipment->status == 3){
                                    $status = 'Cancelled';
                                }else if($deal->shipment->status == 4){
                                    $status = 'Deleted';
                                }else if($deal->shipment->status == 5){
                                    $status = 'Completed';
                                }else if($deal->shipment->status == 6){
                                    $status = 'Cancelled By System';
                                }
                                
                                $data[] = array(
                                    "deal_id"=>$deal->id,
                                    "other_user_id"=>$deal->request_by,
                                    "image"=>$deal->request_by_user->profile_image_url,
                                    "title"=>$deal->request_by_user->name,
                                    "message"=>'',
                                    "date"=>date('d M Y',strtotime($deal->created_at)),
                                    "time"=>date('H:i',strtotime($deal->created_at)),
                                    "count"=>'0',
                                    "status"=>$status
                                );
                            }
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                        }else{
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deals Found','error'=>['No Deals Found'],'data'=>[]], 200);
                        }

                    }else if($deal_type == 'accepted'){
                        
                        $deals = DealsModel::where(function ($query) use ($user_id){
                            $query->where('request_to',$user_id)->orWhere('request_by',$user_id);
                        })->where('request_status','1')
                        ->where(function ($query) use ($type){
                            $query->where('request_to_type',$type)->orWhere('request_by_type',$type);
                        })->whereHas('shipment', function($q){
                            $q->whereIn('status', ['1','2','5']);
                        })->orderBy('id','desc')->get();
                        $data = array();
                        if(count($deals) > 0){
                            foreach($deals as $deal){
                                $image = '';
                                $title = '';
                                if($deal->request_by == $user_id){
                                    $image = $deal->request_to_user->profile_image_url;
                                    $title = $deal->request_to_user->name;
                                    $other_user_id = $deal->request_to_user->id;
                                    $user_type = $deal->request_to_type;
                                }else{
                                    $image = $deal->request_by_user->profile_image_url;
                                    $title = $deal->request_by_user->name;
                                    $other_user_id = $deal->request_by_user->id;
                                    $user_type = $deal->request_by_type;
                                }

                                $count = MessageModel::where('seen_status','0')->where('deal_id',$deal->id)->where('sender_id',$other_user_id)->where('receiver_id',$user_id)->count();
                                $last_msg = MessageModel::where('deal_id',$deal->id)->where(function ($query) use ($other_user_id){
                                        $query->where('sender_id',$other_user_id)->orWhere('receiver_id',$other_user_id);
                                    })
                                    ->where(function ($query) use ($user_id){
                                        $query->where('sender_id',$user_id)->orWhere('receiver_id',$user_id);
                                    })->orderBy('id','desc')->first();
                                
                                $msg = 'No Messages';
                                if(!empty($last_msg)){
                                    $msg = $last_msg->message; 
                                }
                                
                                $status = 'Requests';
                                
                                if($deal->request_status == 1){
                                    $status = 'Accepted';
                                }
                                if($deal->request_status == 2){
                                    $status = 'Cancelled';
                                }
                                if($deal->shipment->status == 0){
                                    $status = 'Awaiting Approval';
                                }else if($deal->shipment->status == 2){
                                    $status = 'Booked';
                                }else if($deal->shipment->status == 3){
                                    $status = 'Cancelled';
                                }else if($deal->shipment->status == 4){
                                    $status = 'Deleted';
                                }else if($deal->shipment->status == 5){
                                    $status = 'Completed';
                                }else if($deal->shipment->status == 6){
                                    $status = 'Cancelled By System';
                                }
                                
                                if($is_booked == 1){
                                    if($user_type == $type){
                                        if($deal->shipment->status == 2){
                                            $data[] = array(
                                                "deal_id"=>$deal->id,
                                                "other_user_id"=>$other_user_id,
                                                "image"=>$image,
                                                "title"=>$title,
                                                "message"=>$msg,
                                                "date"=>date('d M Y',strtotime($deal->created_at)),
                                                "time"=>date('H:i',strtotime($deal->created_at)),
                                                "count"=>$count,
                                                "status"=>$status
                                            );
                                        }
                                    }
                                }else{
                                    if($user_type == $type){
                                        if($is_accepted == 1){
                                            if($deal->shipment->status == 1){
                                                $data[] = array(
                                                    "deal_id"=>$deal->id,
                                                    "other_user_id"=>$other_user_id,
                                                    "image"=>$image,
                                                    "title"=>$title,
                                                    "message"=>$msg,
                                                    "date"=>date('d M Y',strtotime($deal->created_at)),
                                                    "time"=>date('H:i',strtotime($deal->created_at)),
                                                    "count"=>$count,
                                                    "status"=>$status
                                                );
                                            }
                                        }else{
                                            $data[] = array(
                                                "deal_id"=>$deal->id,
                                                "other_user_id"=>$other_user_id,
                                                "image"=>$image,
                                                "title"=>$title,
                                                "message"=>$msg,
                                                "date"=>date('d M Y',strtotime($deal->created_at)),
                                                "time"=>date('H:i',strtotime($deal->created_at)),
                                                "count"=>$count,
                                                "status"=>$status
                                            );
                                        }
                                    }
                                }
                                
                            }
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                        }else{
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deals Found','error'=>['No Deals Found'],'data'=>[]], 200);
                        }
                    }else if($deal_type == 'cancelled'){
                        $deals = DealsModel::where(function ($query) use ($user_id){
                            $query->where('request_to',$user_id)->orWhere('request_by',$user_id);
                        })->where('request_status','2')
                        ->where(function ($query) use ($type){
                            $query->where('request_to_type',$type)->orWhere('request_by_type',$type);
                        })->orderBy('id','desc')->get();
                        
                        
                        $data = array();
                        if(count($deals) > 0){
                            foreach($deals as $deal){
                                $image = '';
                                $title = '';
                                if($deal->request_by == $user_id){
                                    $image = $deal->request_to_user->profile_image_url;
                                    $title = $deal->request_to_user->name;
                                    $other_user_id = $deal->request_to_user->id;
                                    $user_type = $deal->request_to_type;
                                }else{
                                    $image = $deal->request_by_user->profile_image_url;
                                    $title = $deal->request_by_user->name;
                                    $other_user_id = $deal->request_by_user->id;
                                    $user_type = $deal->request_by_type;
                                }

                                $count = MessageModel::where('seen_status','0')->where('deal_id',$deal->id)->where('sender_id',$other_user_id)->where('receiver_id',$user_id)->count();
                                $last_msg = MessageModel::where('deal_id',$deal->id)->where(function ($query) use ($other_user_id){
                                        $query->where('sender_id',$other_user_id)->orWhere('receiver_id',$other_user_id);
                                    })
                                    ->where(function ($query) use ($user_id){
                                        $query->where('sender_id',$user_id)->orWhere('receiver_id',$user_id);
                                    })->orderBy('id','desc')->first();
                                
                                $msg = 'No Messages';
                                if(!empty($last_msg)){
                                    $msg = $last_msg->message;
                                }
                                
                                $status = 'Requests';
                                
                                if($deal->request_status == 1){
                                    $status = 'Accepted';
                                }
                                if($deal->request_status == 2){
                                    $status = 'Cancelled';
                                }
                                if($deal->shipment->status == 0){
                                    $status = 'Awaiting Approval';
                                }else if($deal->shipment->status == 2){
                                    $status = 'Booked';
                                }else if($deal->shipment->status == 3){
                                    $status = 'Cancelled';
                                }else if($deal->shipment->status == 4){
                                    $status = 'Deleted';
                                }else if($deal->shipment->status == 5){
                                    $status = 'Completed';
                                }else if($deal->shipment->status == 6){
                                    $status = 'Cancelled By System';
                                }
                                
                                if($user_type == $type){
                                    $data[] = array(
                                        "deal_id"=>$deal->id,
                                        "other_user_id"=>$other_user_id,
                                        "image"=>$image,
                                        "title"=>$title,
                                        "message"=>$msg,
                                        "date"=>date('d M Y',strtotime($deal->created_at)),
                                        "time"=>date('H:i',strtotime($deal->created_at)),
                                        "count"=>$count,
                                        "status"=>$status
                                    );
                                }
                            }
                            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                        }else{
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deals Found','error'=>['No Deals Found'],'data'=>[]], 200);
                        }
                    }
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type','error'=>['Invalid Type'],'data'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Deal Type','error'=>['Invalid Deal Type'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    public function get_deal_detail(Request $request){
        $deal_id = $request->input('deal_id');
        $deals = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
        
        if(!empty($deals)){
            $status = 'Requests';
            
            if($deals->request_status == 1){
                $status = 'Accepted';
            }
            if($deals->request_status == 2){
                $status = 'Cancelled';
            }
            if($deals->shipment->status == 0){
                $status = 'Awaiting Approval';
            }else if($deals->shipment->status == 2){
                $status = 'Booked';
            }else if($deals->shipment->status == 3){
                $status = 'Cancelled';
            }else if($deals->shipment->status == 4){
                $status = 'Deleted';
            }else if($deals->shipment->status == 5){
                $status = 'Completed';
            }else if($deals->shipment->status == 6){
                $status = 'Cancelled By System';
            }
            $tracking_status = ucwords(str_replace("_"," ",$deals->shipment->tracking_status));
            $user_id = ($request->input('user_id') != '')?$request->input('user_id'):'';
            
            $currency = '₹';
            
            $is_rated = '0';
            if(!empty($user_id)){
                $rated = RatingModel::where('rate_by',$user_id)->where('deal_id',$deal_id)->first();
                if(!empty($rated)){
                    $is_rated = '1';
                }
            }
            
            $data = array(
                "shipment_id"=>$deals->shipment->id,
                "shipment_uniqueid"=>$deals->shipment->shipment_uniqueid,
                "shipment_user_id"=>$deals->shipment->user->id,
                "shipment_user_name"=>$deals->shipment->user->name,
                "shipment_user_image"=>$deals->shipment->user->profile_image_url,
                "shipment_user_type"=>$deals->shipment->shipment_user_type,
                "shipment_user_rating"=>$deals->shipment->user->profile_review,
                "shipment_from"=>$deals->shipment->shipment_from,
                //"shipment_from_name"=>$deals->shipment->from->city,
                "shipment_from_name"=>$deals->shipment->shipment_from,
                "shipment_to"=>$deals->shipment->shipment_to,
                //"shipment_to_name"=>$deals->shipment->to->city,
                "shipment_to_name"=>$deals->shipment->shipment_to,
                "expected_delivery_date"=>date('d M Y',strtotime($deals->shipment->expected_delivery_date)),
                "person_name_pickup"=>$deals->shipment->person_name_pickup,
                "person_addr_pickup"=>$deals->shipment->person_addr_pickup,
                "person_pincode_pickup"=>$deals->shipment->person_pincode_pickup,
                "person_mobile_pickup"=>$deals->shipment->person_mobile_pickup,
                "person_name_delivery"=>$deals->shipment->person_name_delivery,
                "person_addr_delivery"=>$deals->shipment->person_addr_delivery,
                "person_pincode_delivery"=>$deals->shipment->person_pincode_delivery,
                "person_mobile_delivery"=>$deals->shipment->person_mobile_delivery,
                "status"=>$status,
                "tracking_status"=>$tracking_status,
                "is_rated"=>$is_rated,
                "request_by"=>$deals->request_by,
                "request_to"=>$deals->request_to,
            );
            $data['quotation'] = 0;
            $data['pay_quotation'] = '0';
            $data['traveller_reward'] = 0;
            $data['total_items'] = count($deals->shipment->products);
            $data['total_weight'] = 0;
            $quotation = 0;
            $total_weight = 0;
            $data['products'] = array();
            if(count($deals->shipment->products)){
               foreach($deals->shipment->products as $product){
                    $notes = array();
                    if($product->notes != ''){
                        $note = json_decode($product->notes);
                        if(is_array($note)){
                            foreach($note as $not){
                                $notes[] = array('note'=>$not);
                            }
                        }
                    }
                    $weight_unit = $product->product->product_weight_unit;
                    $weight = $product->product->product_weight;
                    if($weight_unit == 'g'){
                        $weight = $weight/1000;
                    }else if($weight_unit == 'mg'){
                        $weight = ($weight/1000)/1000;
                    }
                    
                    $currency = ($product->reward_currency == 'rupees')?'₹':'$';
                    
                    $arr = array(
                        "sp_id"=>$product->id,
                        "product_id"=>$product->product_id,
                        "product_name"=>$product->product->product_name,
                        "product_category_id"=>$product->product->product_category,
                        "product_category_name"=>(!empty($product->product->category))?$product->product->category->category_name:'',
                        "product_image"=>$product->product->product_image_url,
                        "product_unique_id"=>$product->product->product_unique_id,
                        "product_stock_qty"=>$product->product->product_stock_qty,
                        "product_weight"=>$weight,
                        "product_size"=>$product->product->product_size,
                        "product_length"=>$product->product->product_length,
                        "product_width"=>$product->product->product_width,
                        "product_height"=>$product->product->product_height,
                        "product_price"=>$product->product->product_price,
                        "product_link"=>($product->product->product_link != '')?$product->product->product_link:'',
                        "product_qty"=>$product->product_qty,
                        "tb_charges"=>$currency.''.(string)round($product->tb_charges,2),
                        "traveller_reward"=>$currency.''.(string)round($product->traveller_reward,2),
                        "quotation"=>$currency.''.(string)round($product->product_total + $product->traveller_reward + $product->tb_charges ,2),
                        "notes"=>$notes,
                        //"shipment_from_name"=>$deals->shipment->from->city,
                        //"shipment_to_name"=>$deals->shipment->to->city
                        "shipment_from_name"=>$deals->shipment->shipment_from,
                        "shipment_to_name"=>$deals->shipment->shipment_to
                    );
                    //$quotation += $product->product_total+$product->tb_charges+$product->traveller_reward; //OLD
                    $quotation += $product->tb_charges+$product->traveller_reward;
                    
                    $total_weight += $weight;
                    $data['products'][] = $arr;
               }
            }
            //$data['quotation'] = (string)round($quotation,2);
            $data['quotation'] = $currency.''.(string)round($deals->shipment->total_quotation,2);
            $data['pay_quotation'] = (string)round($deals->shipment->total_quotation,2);
            $data['traveller_reward'] = $currency.''.(string)round($deals->shipment->total_reward,2);
            $data['total_weight'] = (string)round($total_weight,2);

            //Get Trip Detail
            $categories = array();
            if($deals->trip->category_wont_carry != ''){
                $cate = json_decode($deals->trip->category_wont_carry);
                if(is_array($cate)){
                    foreach($cate as $cat){
                        $cat_detail = CategoryModel::where('id',$cat)->first();
                        if($cat_detail != null){
                            $categories[] = array('name'=>$cat_detail->category_name); 
                        }
                    }
                }
            }
            $notes = array();
            if($deals->trip->notes != ''){
                $note = json_decode($deals->trip->notes);
                if(is_array($note)){
                    foreach($note as $not){
                        $notes[] = array('note'=>$not); 
                    }
                }
            }
            $trip_status = 'Added';
            if($deals->trip->trip_status == 1){
                $trip_status = 'Completed';
            }else if($deals->trip->trip_status == 2){
                $trip_status = 'Cancelled';
            }else if($deals->trip->trip_status == 3){
                $trip_status = 'Deleted';
            }
            $trip_arr = array(
                    "trip_id"=>$deals->trip->id,
                    "trip_user_id"=>$deals->trip->user_id,
                    "user_rating"=>$deals->trip->user->profile_review,
                    "ticket_image"=>$deals->trip->ticket_image_url,
                    "user_image"=>$deals->trip->user->profile_image_url,
                    "user_name"=>$deals->trip->user->name,
                    "ticket_name"=>$deals->trip->ticket_first_name.' '.$deals->trip->ticket_last_name,
                    //"from"=>$deals->trip->from->city,
                    //"to"=>$deals->trip->to->city,
                    "from"=>$deals->trip->trip_from,
                    "to"=>$deals->trip->trip_to,
                    "departure_date"=>date('d M Y / h:i A',strtotime($deals->trip->departure_datetime)),
                    "arrival_date"=>date('d M Y / h:i A',strtotime($deals->trip->arrival_datetime)),
                    "transport_type"=>ucfirst($deals->trip->transport_type),
                    "available_weight"=>$deals->trip->avail_luggage_weight,
                    "ticket_number"=>$deals->trip->ticket_number,
                    "reason"=>$deals->trip->reason,
                    "trip_status"=>$trip_status,
                    "categories_wont_carry"=>$categories,
                    "notes"=>$notes,
            );

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data,"trip"=>$trip_arr], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deal Found','error'=>['No Deal Found'],'data'=>[]], 200);
        }

    }
    public function change_deal_status(Request $request){
        $deal_id = $request->input('deal_id');
        $status = $request->input('status');
        $deals = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
        
        if(!empty($deals)){
            if(($status == 1) || ($status == 2) ){
                //Send Notification
                $notification = new NotificationController();
                $notification->changeDealStatus($deals,$status);
                
                if($status == 1){
                    DealsModel::where('id',$deal_id)->update(['request_status'=>$status]);
                    ShipmentModel::where('id',$deals->shipment_id)->update(['status'=>'7']);
                    TripsModel::where('id',$deals->trip_id)->update(['trip_status'=>'6']);
                    
                    $sh_deals = DealsModel::where('shipment_id',$deals->shipment_id)->where('id','!=',$deal_id)->get();
                    foreach($sh_deals as $deal){
                        //$notification->changeDealStatus($deal,'2');
                        DealsModel::where('id',$deal->id)->delete();
                        NotificationModel::where('type','deal')->where('reference_id',$deal->id)->update(['type'=>'reject_deal']);
                    }
                    $tr_deals = DealsModel::where('trip_id',$deals->trip_id)->where('id','!=',$deal_id)->get();
                    foreach($tr_deals as $deal){
                        //$notification->changeDealStatus($deal,'2');
                        DealsModel::where('id',$deal->id)->delete();
                        NotificationModel::where('type','deal')->where('reference_id',$deal->id)->update(['type'=>'reject_deal']);
                    }
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Deal Accepted Successfully','error'=>[]], 200);
                }else{
                    DealsModel::where('id',$deal_id)->delete();
                    
                    NotificationModel::where('type','deal')->where('reference_id',$deal_id)->update(['type'=>'reject_deal']);
                    
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Deal Rejected Successfully','error'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Deal Status','error'=>['Invalid Deal Status']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deal Found','error'=>['No Deal Found']], 200);
        }
    }

    public function track_order(Request $request){
        $order_id = $request->input('order_id');
        $user_id = $request->input('user_id');
        
        $shipment = ShipmentModel::where('shipment_uniqueid',$order_id)->has('user')->orderBy('id','desc')->first();
        $traveller_name = '';
        if(!empty($shipment)){
            $ship_deals = $shipment->deals;
            $trip_id = '0';
            $trip_user_name = '';
            $trip_user_image = '';
            $deal_id = '0';
            $trip_user_id = '0';
            $status = 'Added';
            if(count($ship_deals) > 0){
                $status = 'Requests';
                foreach($ship_deals as $deal){
                    if($deal->request_status == 1){
                        if($deal->request_by == $shipment->user->id){
                            $traveller_name = $deal->request_to_user->name;
                        }else{
                            $traveller_name = $deal->request_by_user->name;
                        }
                        
                        $status = 'Accepted';
                        $trip_id = $deal->trip_id;
                        $deal_id = $deal->id;
                        $trip_user_name = $deal->trip->user->name;
                        $trip_user_image = $deal->trip->user->profile_image_url;
                        $trip_user_id = $deal->trip->user_id;
                    }
                }
            }
            if($shipment->status == 0){
                $status = 'Awaiting Approval';
            }else if($shipment->status == 2){
                $status = 'Booked';
            }else if($shipment->status == 3){
                $status = 'Cancelled';
            }else if($shipment->status == 4){
                $status = 'Deleted';
            }else if($shipment->status == 5){
                $status = 'Completed';
            }else if($shipment->status == 6){
                $status = 'Cancelled By System';
            }
            
            $is_rated = '0';
            
            if(!empty($user_id)){
                $rated = RatingModel::where('rate_by',$user_id)->where('shipment_id',$shipment->id)->first();
                if(!empty($rated)){
                    $is_rated = '1';
                }
            }
            $usr_type = "traveller";
            if($shipment->created_by == $user_id){
                $usr_type = "sender";
            }
            
            $data = array(
                "shipment_id"=>$shipment->id,
                "trip_id"=>$trip_id,
                "deal_id"=>(string)$deal_id,
                "current_user_type"=>$usr_type,
                "trip_user_id"=>(string)$trip_user_id,
                "shipment_uniqueid"=>$shipment->shipment_uniqueid,
                "shipment_user_id"=>$shipment->created_by,
                "shipment_user_name"=>$shipment->user->name,
                "trip_user_name"=>$trip_user_name,
                "shipment_user_image"=>$shipment->user->profile_image_url,
                "trip_user_image"=>$trip_user_image,
                "shipment_user_type"=>$shipment->shipment_user_type,
                "shipment_user_rating"=>'3',
                "shipment_from"=>$shipment->shipment_from,
                //"shipment_from_name"=>$shipment->from->city,
                "shipment_from_name"=>$shipment->shipment_from,
                "shipment_to"=>$shipment->shipment_to,
                //"shipment_to_name"=>$shipment->to->city,
                "shipment_to_name"=>$shipment->shipment_to,
                "expected_delivery_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                "person_name_pickup"=>$shipment->person_name_pickup,
                "person_addr_pickup"=>$shipment->person_addr_pickup,
                "person_pincode_pickup"=>$shipment->person_pincode_pickup,
                "person_mobile_pickup"=>$shipment->person_mobile_pickup,
                "person_name_delivery"=>$shipment->person_name_delivery,
                "person_addr_delivery"=>$shipment->person_addr_delivery,
                "person_pincode_delivery"=>$shipment->person_pincode_delivery,
                "person_mobile_delivery"=>$shipment->person_mobile_delivery,
                "status"=>$status,
                "is_rated"=>$is_rated,
                "tracking_status"=>$shipment->tracking_status,
                "traveller_name"=>$traveller_name
            );
            $data['quotation'] = 0;
            $data['total_items'] = count($shipment->products);
            $data['total_weight'] = 0;
            $quotation = 0;
            $total_weight = 0;
            $data['products'] = array();
            $currency = '₹';
            if(count($shipment->products)){
               foreach($shipment->products as $product){
                    $notes = array();
                    if($product->notes != ''){
                        $note = json_decode($product->notes);
                        if(is_array($note)){
                            foreach($note as $not){
                                $notes[] = array('note'=>$not);
                            }
                        }
                    }
                    $currency = ($product->reward_currency == 'rupees')?'₹':'$';
                    $arr = array(
                        "sp_id"=>$product->id,
                        "product_id"=>$product->product_id,
                        "product_name"=>$product->product->product_name,
                        "product_category_id"=>$product->product->product_category,
                        "product_category_name"=>$product->product->category->category_name,
                        "product_image"=>$product->product->product_image_url,
                        "product_unique_id"=>$product->product->product_unique_id,
                        "product_stock_qty"=>$product->product->product_stock_qty,
                        "product_weight"=>$product->product->product_weight,
                        "product_size"=>$product->product->product_size,
                        "product_length"=>$product->product->product_length,
                        "product_width"=>$product->product->product_width,
                        "product_height"=>$product->product->product_height,
                        "product_price"=>$product->product->product_price,
                        "product_link"=>($product->product->product_link != '')?$product->product->product_link:'',
                        "product_qty"=>$product->product_qty,
                        "tb_charges"=>$currency.(string)round($product->tb_charges,2),
                        "traveller_reward"=>$currency.(string)round($product->traveller_reward,2),
                        "quotation"=>$currency.(string)round($product->traveller_reward + $product->tb_charges ,2),
                        "notes"=>$notes,
                        //"shipment_from_name"=>$shipment->from->city,
                        //"shipment_to_name"=>$shipment->to->city
                        "shipment_from_name"=>$shipment->shipment_from,
                        "shipment_to_name"=>$shipment->shipment_to
                    );
                    $quotation += $product->product_total+$product->tb_charges+$product->traveller_reward;
                    $total_weight += $product->product->product_weight;
                    $data['products'][] = $arr;
               }
            }
            $data['quotation'] = $currency.(string)round($quotation,2);
            $data['total_weight'] = (string)round($total_weight,2);

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id'],'data'=>[]], 200);
        }
    }
    public function change_order_status(Request $request){
        $order_id = $request->input('order_id');
        $order_status = $request->input('order_status');
        $shipment = ShipmentModel::where('shipment_uniqueid',$order_id)->has('user')->orderBy('id','desc')->first();

        if(!empty($shipment)){
            if(($order_status == 'picked_up') || ($order_status == 'transit') || ($order_status == 'arrived')){
                ShipmentModel::where('id',$shipment->id)->update(['tracking_status'=>$order_status]);
                
                $notification = new NotificationController();
                $notification->orderTracking($shipment,$order_status);
                
                if($order_status == 'arrived'){
                    $mob_no = $shipment->person_mobile_delivery;
                    $mob_cc = str_replace('+','',$shipment->person_mobile_cc_delivery);
                    $mob_no = $mob_cc.$mob_no;
                    
                    $ship_deals = $shipment->deals;
                    $traveller_name = '';
                    if(count($ship_deals) > 0){
                        foreach($ship_deals as $deal){
                            if($deal->request_status == 1){
                                if($deal->request_by == $shipment->user->id){
                                    $traveller_name = $deal->request_to_user->name;
                                }else{
                                    $traveller_name = $deal->request_by_user->name;
                                }
                            }
                        }
                    }
                    
                    $fields = json_encode([
                                "flow_id"=>'617bc5f536e9f156b40dc5f3',
                                "sender"=>'TWNBDY',
                                "mobiles"=>$mob_no,
                                "var1"=>$shipment->user->name,
                                "var2"=>$traveller_name,
                            ]);
                    
                    $sms = new SmsController();
                    $sms->sendSMS($fields);
                }
                
                NotificationModel::where('notification_type','remainders')->where('remainder_type','arrival')->where('type','deal')->where('remainder_value',$order_id)->update(['remainder_type'=>'']);
                NotificationModel::where('notification_type','remainders')->where('remainder_type','transit')->where('type','deal')->where('remainder_value',$order_id)->update(['remainder_type'=>'']);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Order Status Updated Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Status','error'=>['Invalid Order Status']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id']], 200);
        }
    }
    public function get_tracking_orders(Request $request){
        $user_id = $request->input('user_id');
        $type = $request->input('type');
        
        if(($type == 'shipment') || ($type == 'trip') ){
            
            $user = Users_Model::where('id',$user_id)->first();
            if(!empty($user)){
                
                if($type == 'shipment'){
                    /*
                    $deals = DealsModel::where(function ($query) use ($user_id) {
                        $query->where('request_by', $user_id)
                            ->orWhere('request_to', $user_id);
                        })->where('request_status','1')->get();
                        */
                    $deals = DealsModel::whereHas('shipment', function($q) use($user_id){
                            $q->where('created_by', $user_id);
                        })->where('request_status','1')->orderBy('id','desc')->get();
                    
                    if(count($deals) > 0){
                        $data = array();
                        foreach($deals as $deal){
                            
                            //$title = $deal->shipment->user->name; //OLD 23-07-21
                            //$image = $deal->shipment->user->profile_image_url; //OLD 23-07-21
                            
                            $title = '';
                            $image = '';
                            if(count($deal->shipment->products) > 0){
                                $title = $deal->shipment->products[0]->product->product_name;
                                $image = $deal->shipment->products[0]->product->product_image_url;
                            }
                            
                            $data[] = array(
                                "order_id"=>$deal->shipment->shipment_uniqueid,
                                "shipment_id"=>$deal->shipment_id,
                                "image"=>$image,
                                "title"=>$title,
                                "rating"=>$deal->shipment->user->profile_review,
                                //"from"=>$deal->shipment->from->city,
                                //"to"=>$deal->shipment->to->city,
                                "from"=>$deal->shipment->shipment_from,
                                "to"=>$deal->shipment->shipment_to,
                                "expected_before"=>date('d M Y',strtotime($deal->shipment->expected_delivery_date)),
                                "status"=>$deal->shipment->tracking_status,
                            );
                        }
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Orders Found','error'=>['No Orders Found'],'data'=>[]], 200);
                    }
                }else{
                    /*
                    $deals = DealsModel::where(function ($query) use ($user_id) {
                        $query->where('request_by', $user_id)
                            ->orWhere('request_to', $user_id);
                        })->where('request_status','1')->get();*/
                        
                    $deals = DealsModel::whereHas('trip', function($q) use($user_id){
                            $q->where('user_id', $user_id);
                        })->where('request_status','1')->orderBy('id','desc')->get();
                        
                    if(count($deals) > 0){
                        $data = array();
                        foreach($deals as $deal){
                            //$title = $deal->shipment->user->name; //OLD 23-07-21
                            //$image = $deal->shipment->user->profile_image_url; //OLD 23-07-21
                            
                            $title = '';
                            $image = '';
                            if(count($deal->shipment->products) > 0){
                                $title = $deal->shipment->products[0]->product->product_name;
                                $image = $deal->shipment->products[0]->product->product_image_url;
                            }
                            
                            $data[] = array(
                                "order_id"=>$deal->shipment->shipment_uniqueid,
                                "shipment_id"=>$deal->shipment_id,
                                "image"=>$image,
                                "title"=>$title,
                                "rating"=>$deal->shipment->user->profile_review,
                                //"from"=>$deal->shipment->from->city,
                                //"to"=>$deal->shipment->to->city,
                                "from"=>$deal->shipment->shipment_from,
                                "to"=>$deal->shipment->shipment_to,
                                "expected_before"=>date('d M Y',strtotime($deal->shipment->expected_delivery_date)),
                                "status"=>$deal->shipment->tracking_status,
                            );
                        }
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Orders Found','error'=>['No Orders Found'],'data'=>[]], 200);
                    }
                }
    
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Type Value','error'=>['Invalid Type Value'],'data'=>[]], 200);
        }

    }
    public function order_otp_verify(Request $request){
        $order_id = $request->input('order_id');
        $type = $request->input('type');
        $otp = $request->input('otp');
        $shipment = ShipmentModel::where('shipment_uniqueid',$order_id)->has('user')->orderBy('id','desc')->first();

        if(!empty($shipment)){
            if(($type == 'pickup') || ($type == 'delivery')){
                if($type == 'pickup'){
                    if($otp == $shipment->pickup_otp){
                        ShipmentModel::where('id',$shipment->id)->update(['tracking_status'=>'picked_up']);
                        
                        $notification = new NotificationController();
                        $notification->orderTracking($shipment,'picked_up');
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Verified Successfully','error'=>[]], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>[]], 200);
                    }
                }else{
                    if($otp == $shipment->delivery_otp){
                        ShipmentModel::where('id',$shipment->id)->update(['tracking_status'=>'delivered','delivery_date'=>date('Y-m-d'),'status'=>'5']);
                        
                        $ship_deals = $shipment->deals;
                        if(count($ship_deals) > 0){
                            foreach($ship_deals as $deal){
                                if($deal->request_status == 1){
                                    $trip_id = $deal->trip_id;
                                    TripsModel::where('id',$trip_id)->update(['trip_status'=>'1']);
                                    
                                    $wallet = $deal->trip->user->wallet_amount;
                                    $wallet = $wallet + $shipment->total_reward;
                                    
                                    Users_Model::where('id',$deal->trip->user->id)->update(['wallet_amount'=>$wallet]);
                                }
                            }
                        }
                        
                        $notification = new NotificationController();
                        $notification->orderTracking($shipment,'delivery');
                        
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Verified Successfully','error'=>[]], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>[]], 200);
                    }
                }
                
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Status','error'=>['Invalid Order Status']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id']], 200);
        }
    }
    public function send_return_shipment_otp(Request $request){
        $shipment_id = $request->input('shipment_id');
        $shipment = ShipmentModel::where('id',$shipment_id)->has('user')->orderBy('id','desc')->first();

        if(!empty($shipment)){
            $otp = rand(1000,9999);
            $fields = json_encode(["OTP"=>$otp]);
            $mob_no = $shipment->person_mobile_pickup;
            $mob_cc = str_replace('+','',$shipment->person_mobile_cc_pickup);
            $mob_no = $mob_cc.$mob_no;
            $sms = new SmsController();
            $sms->sendOtp($mob_no,'613c628f3ec86b13ad09b2e2',$fields);
            ShipmentModel::where('id',$shipment->id)->update(['return_otp'=>$otp]);
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Generated Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id']], 200);
        }
    }
    public function return_shipment_otp_verify(Request $request){
        $shipment_id = $request->input('shipment_id');
        $otp = $request->input('otp');
        $user_id = $request->input('user_id');
        $shipment = ShipmentModel::where('id',$shipment_id)->has('user')->orderBy('id','desc')->first();

        if(!empty($shipment)){
            if($otp == $shipment->return_otp){
                ShipmentModel::where('id',$shipment->id)->update(['shipment_return'=>'1','shipment_return_by_id'=>$user_id]);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Return Otp Verified Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id']], 200);
        }
    }
    public function order_otp_generate(Request $request){
        $order_id = $request->input('order_id');
        $type = $request->input('type');

        $shipment = ShipmentModel::where('shipment_uniqueid',$order_id)->has('user')->orderBy('id','desc')->first();

        if(!empty($shipment)){
            if(($type == 'pickup') || ($type == 'delivery')){
                $otp = rand(1000,9999);
                $fields = json_encode(["OTP"=>$otp]);
                if($type == 'pickup'){
                    $mob_no = $shipment->person_mobile_pickup;
                    $mob_cc = str_replace('+','',$shipment->person_mobile_cc_pickup);
                    $mob_no = $mob_cc.$mob_no;
                    
                    
                    //Pickup Otp
                    
                    $sms = new SmsController();
                    $sms->sendOtp($mob_no,'613c628f3ec86b13ad09b2e2',$fields);
                    ShipmentModel::where('id',$shipment->id)->update(['pickup_otp'=>$otp]);
                    
                }else if($type == 'delivery'){
                    $mob_no = $shipment->person_mobile_delivery;
                    $mob_cc = str_replace('+','',$shipment->person_mobile_cc_delivery);
                    $mob_no = $mob_cc.$mob_no;
                    
                    //Delivery Otp
                    $sms = new SmsController();
                    $sms->sendOtp($mob_no,'613c62e36d6e31725672cd37',$fields);
                    ShipmentModel::where('id',$shipment->id)->update(['delivery_otp'=>$otp]);
                }
                
                //Send App Notification
                //$notification = new NotificationController();
                //$notification->orderOtpGenerate($shipment,$type,$otp);
                
                //return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Generated Successfully '.$otp,'error'=>[]], 200);
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Generated Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Status','error'=>['Invalid Order Status']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id']], 200);
        }
    }
    public function order_otp_submit(Request $request){
        $order_id = $request->input('order_id');
        $type = $request->input('type');
        $otp = $request->input('otp');

        $shipment = ShipmentModel::where('shipment_uniqueid',$order_id)->has('user')->orderBy('id','desc')->first();

        if(!empty($shipment)){
            if(($type == 'pickup') || ($type == 'delivery')){
                if($type == 'pickup'){
                    if($otp == $shipment->pickup_otp){
                        ShipmentModel::where('id',$shipment->id)->update(['tracking_status'=>'picked_up']);
                        
                        $notification = new NotificationController();
                        $notification->orderTracking($shipment,'picked_up');
                        
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>['Invalid Otp']], 200);
                    }
                }else if($type == 'delivery'){
                    if($otp == $shipment->delivery_otp){
                        ShipmentModel::where('id',$shipment->id)->update(['tracking_status'=>'delivered','delivery_date'=>date('Y-m-d'),'status'=>'5']);
                        
                        $ship_deals = $shipment->deals;
                        if(count($ship_deals) > 0){
                            foreach($ship_deals as $deal){
                                if($deal->request_status == 1){
                                    $trip_id = $deal->trip_id;
                                    TripsModel::where('id',$trip_id)->update(['trip_status'=>'1']);
                                    
                                    $wallet = $deal->trip->user->wallet_amount;
                                    $wallet = $wallet + $shipment->total_reward;
                                    
                                    Users_Model::where('id',$deal->trip->user->id)->update(['wallet_amount'=>$wallet]);
                                }
                            }
                        }
                        
                        $notification = new NotificationController();
                        $notification->orderTracking($shipment,'delivery');
                        
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Otp','error'=>['Invalid Otp']], 200);
                    }
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Otp Verified Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Status','error'=>['Invalid Order Status']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Order Id','error'=>['Invalid Order Id']], 200);
        }
    }
    public function cancel_deal(Request $request){
        $deal_id = $request->input('deal_id');
        $user_id = $request->input('user_id');
        $reason = $request->input('reason');
        $deals = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
        
        if(!empty($deals)){
            $traveller_id = '';
            $buyer_id = '';
            $buyer_type = '';
            
            $cancel_by_user = '';
            
            if($deals->request_by_type == 'traveller'){
                $traveller_id = $deals->request_by;
                $buyer_id = $deals->request_to;
                $buyer_type = $deals->request_to_type;
                
                if($traveller_id == $user_id){
                    $cancel_by_user = $deals->request_by_user;
                    $notifi_user = $deals->request_to_user;
                }else{
                    $cancel_by_user = $deals->request_to_user;
                    $notifi_user = $deals->request_by_user;
                }
                
            }else{
                $traveller_id = $deals->request_to;
                $buyer_id = $deals->request_by;
                $buyer_type = $deals->request_by_type;
                
                if($traveller_id == $user_id){
                    $cancel_by_user = $deals->request_to_user;
                    $notifi_user = $deals->request_by_user;
                }else{
                    $cancel_by_user = $deals->request_by_user;
                    $notifi_user = $deals->request_to_user;
                }
            }

            if($traveller_id == $user_id){
                $deals->cancel_by = 'traveller';
                $deals->request_status = '2';
                $deals->cancel_by_id = $user_id;
                $deals->cancel_reason = $reason;
                $deals->save();
                
                $deals->shipment->status = '1';
                $deals->shipment->save();
                
                $deals->trip->trip_status = '0';
                $deals->trip->save();
                
                //DealsModel::where('id',$deal_id)->update(['request_status'=>'2','cancel_by'=>'traveller','cancel_by_id'=>$user_id,'cancel_reason'=>$reason]);
                
                $notification = new NotificationController();
                $notification->cancelDealNotification($cancel_by_user,$notifi_user,$deals);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Deal Cancelled Successfully','error'=>[]], 200);
            }elseif($buyer_id == $user_id){
                $deals->cancel_by = $buyer_type;
                $deals->request_status = '2';
                $deals->cancel_by_id = $user_id;
                $deals->cancel_reason = $reason;
                $deals->save();
                
                $deals->shipment->status = '1';
                $deals->shipment->save();
                
                $deals->trip->trip_status = '0';
                $deals->trip->save();
                
                //DealsModel::where('id',$deal_id)->update(['request_status'=>'2','cancel_by'=>$buyer_type,'cancel_by_id'=>$user_id,'cancel_reason'=>$reason]);
                
                $notification = new NotificationController();
                $notification->cancelDealNotification($cancel_by_user,$notifi_user,$deals);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Deal Cancelled Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'User Not Authorized','error'=>['User Not Authorized']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deal Found','error'=>['No Deal Found']], 200);
        }
    }

    public function cancel_request(Request $request){
        $deal_id = $request->input('deal_id');
        $user_id = $request->input('user_id');
        
        $deals = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
        
        if(!empty($deals)){
            
            if($deals->request_by == $user_id){
                DealsModel::where('id',$deal_id)->delete();
                
                NotificationModel::where('type','deal')->where('reference_id',$deal_id)->update(['type'=>'reject_deal']);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Request Cancelled Successfully','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'User Not Authorized','error'=>['User Not Authorized']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deal Found','error'=>['No Deal Found']], 200);
        }
    }
    public function send_trip_delay_details(Request $request){
        $deal_id = $request->input('deal_id');
        $status = $request->input('status');
        
        if(($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)){
            $deals = DealsModel::where('id',$deal_id)->has('shipment')->has('trip')->first();
        
            if(!empty($deals)){
                if(($status == 1) || ($status == 2) || ($status == 3)){
                    $notification = new NotificationController();
                    $notification->delayRemainder($deals,$status);
                }
                NotificationModel::where('notification_type','remainders')->where('remainder_type','transit')->where('type','deal')->where('reference_id',$deal_id)->update(['remainder_type'=>'']);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Delay Reason Updated','error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Deal Found','error'=>['No Deal Found']], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Status Value','error'=>['Invalid Status Value']], 200);
        }
    }

}
