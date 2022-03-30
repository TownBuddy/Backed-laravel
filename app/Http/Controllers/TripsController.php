<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TripsModel;
use App\CategoryModel;
use App\ShipmentModel;
use App\Users_Model;
use App\CityModel;
use App\DealsModel;
use App\RatingModel;
use App\NotificationModel;

class TripsController extends Controller
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
    public function get_trips(Request $request){
        $trip_from = $request->input('trip_from');
        $trip_to = $request->input('trip_to');
        $user_id = (($request->input('user_id') != ''))?$request->input('user_id'):'0';
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        //return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$request->all(),'error'=>[]], 200);
        
        $query = TripsModel::query();

        if(!empty($start_date)){
            $query->whereDate('arrival_datetime','>=',$start_date);
        }
        if(!empty($end_date)){
            $query->whereDate('arrival_datetime','<=',$end_date);
        }
        if(!empty($trip_from)){
            $query->where('trip_from_full_txt',$trip_from);
        }
        if(!empty($trip_to)){
            $query->where('trip_to_full_txt',$trip_to);
        }
        if((empty($start_date)) && (empty($end_date))){
            $query->where('departure_datetime','>=',date('Y-m-d H:i'));
        }
        
        $query->where('is_created','1');
        if($user_id != 0){
            $trips = $query->where('trip_status',0)->where('user_id','!=',$user_id)->has('user')->withCount('deals')->orderBy('deals_count', 'desc')->orderBy('id', 'desc')->get();
        }else{
            $trips = $query->where('trip_status',0)->has('user')->withCount('deals')->orderBy('deals_count', 'desc')->orderBy('id', 'desc')->get();
        }
        

        if(count($trips) > 0){
            $trips_arr = array();
            foreach($trips as $trip){
                if($user_id != 0){
                    $deal = DealsModel::where(function ($query) use ($user_id) {
                        $query->where('request_by', $user_id)
                            ->orWhere('request_to', $user_id);
                        })->where('trip_id',$trip->id)->where('request_status','!=','2')->first();
                    if(empty($deal)){
                        
                        $shipment = DealsModel::whereHas('shipment', function($q) use ($trip,$user_id){
                                    
                                    $q->whereDate('expected_delivery_date','>=',date('Y-m-d',strtotime($trip->arrival_datetime)));
                                    $q->where('shipment_from',$trip->trip_from);
                                    $q->where('shipment_to',$trip->trip_to);
                                    $q->where('created_by',$user_id);
                                })->where('request_status','1')->first();
                        if(!empty($shipment)){
                            continue;
                        }else{
                            $arr = array(
                                "trip_id"=>$trip->id,
                                "trip_user_id"=>$trip->user_id,
                                "user_image"=>$trip->user->profile_image_url,
                                "user_name"=>$trip->user->name,
                                //"from"=>$trip->from->city,
                                //"to"=>$trip->to->city,
                                "from"=>$trip->trip_from,
                                "to"=>$trip->trip_to,
                                "departure_date"=>date('d M Y',strtotime($trip->departure_datetime))
                            );
                            $trips_arr[] = $arr;
                        }
                    }
                }else{
                    $arr = array(
                        "trip_id"=>$trip->id,
                        "trip_user_id"=>$trip->user_id,
                        "user_image"=>$trip->user->profile_image_url,
                        "user_name"=>$trip->user->name,
                        //"from"=>$trip->from->city,
                        //"to"=>$trip->to->city,
                        "from"=>$trip->trip_from,
                        "to"=>$trip->trip_to,
                        "departure_date"=>date('d M Y',strtotime($trip->departure_datetime))
                    );
                    $trips_arr[] = $arr;
                }
            }
            if(!empty($trips_arr)){
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$trips_arr,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','data'=>[],'error'=>['No Trips Found']], 200);
            }
            
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','data'=>[],'error'=>['No Trips Found']], 200);
        }

    }

    public function get_trip_details(Request $request){
        $trip_id = $request->input('trip_id');
        $user_id = $request->input('user_id');
        
        if($trip_id != ''){
            $trip = TripsModel::where('is_created','1')->where('id',$trip_id)->has('user')->first();

            if($trip != null){
                $categories = array();
                if($trip->category_wont_carry != ''){
                    $cate = json_decode($trip->category_wont_carry);
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
                if($trip->notes != ''){
                    $note = json_decode($trip->notes);
                    if(is_array($note)){
                        foreach($note as $not){
                            $notes[] = array('note'=>$not); 
                        }
                    }
                }
                $trip_status = 'Added';
                
                $trip_status = 'Added';
                
                $trip_deals = $trip->deals;
                $shipment_id = '0';
                $shipment_unique_id = '0';
                $deal_id = '0';
                $shipment_user_id = '0';
                $is_requested = '0';
                
                $rated = RatingModel::where('rate_by',$user_id)->where('trip_id',$trip->id)->first();
                $is_rated = '0';
                if(!empty($rated)){
                    $is_rated = '1';
                }
                $tracking_status = '';
                
                if(count($trip_deals) > 0){
                    //$trip_status = 'Requests';
                    foreach($trip_deals as $deal){
                        if($deal->request_by == $trip->user_id){
                            $trip_status = 'Requests';
                        }
                        if($deal->request_status == 1){
                            $trip_status = 'Accepted';
                            
                            $shipment_id = $deal->shipment_id;
                            $deal_id = $deal->id;
                            $shipment_user_id = $deal->shipment->created_by;
                            $shipment_unique_id = $deal->shipment->shipment_uniqueid;
                            $tracking_status = ucwords(str_replace("_"," ",$deal->shipment->tracking_status));
                        }
                        
                        if(!empty($user_id)){
                            if(($deal->request_by == $user_id) || ($deal->request_to == $user_id) ){
                                $is_requested = '1';
                            }
                        }
                    }
                }
                if($trip->trip_status == 1){
                    $trip_status = 'Completed';
                }else if($trip->trip_status == 2){
                    $trip_status = 'Cancelled';
                }else if($trip->trip_status == 3){
                    $trip_status = 'Deleted';
                }else if($trip->trip_status == 5){
                    $trip_status = 'Cancelled By System';
                }
                $trip_arr = array(
                        "trip_id"=>$trip->id,
                        "trip_user_id"=>$trip->user_id,
                        "shipment_id"=>(string)$shipment_id,
                        "shipment_uniqueid"=>(string)$shipment_unique_id,
                        "deal_id"=>(string)$deal_id,
                        "shipment_user_id"=>(string)$shipment_user_id,
                        "user_rating"=>$trip->user->profile_review,
                        "ticket_image"=>$trip->ticket_image_url,
                        "user_image"=>$trip->user->profile_image_url,
                        "user_name"=>$trip->user->name,
                        "ticket_name"=>$trip->ticket_first_name.' '.$trip->ticket_last_name,
                        //"from"=>$trip->from->city,
                        //"to"=>$trip->to->city,
                        "from"=>$trip->trip_from.$trip->country_from_iso,
                        "to"=>$trip->trip_to.$trip->country_to_iso,
                        "trip_from"=>$trip->trip_from.$trip->country_from_iso,
                        "trip_to"=>$trip->trip_to.$trip->country_to_iso,
                        "trip_from_area"=>$trip->trip_from_area,
                        "trip_to_area"=>$trip->trip_to_area,
                        "current_address"=>$trip->current_address,
                        "destination_address"=>$trip->destination_address,
                        "current_pincode"=>$trip->current_pincode,
                        "destination_pincode"=>$trip->destination_pincode,
                        "departure_date"=>date('d M Y / h:i A',strtotime($trip->departure_datetime)),
                        "arrival_date"=>date('d M Y / h:i A',strtotime($trip->arrival_datetime)),
                        "transport_type"=>ucfirst($trip->transport_type),
                        "available_weight"=>$trip->avail_luggage_weight,
                        "available_weight_unit"=>$trip->avail_luggage_weight_unit,
                        "ticket_number"=>$trip->ticket_number,
                        "reason"=>$trip->reason,
                        "trip_status"=>$trip_status,
                        "is_requested"=>$is_requested,
                        "is_rated"=>$is_rated,
                        "categories_wont_carry"=>$categories,
                        "notes"=>$notes,
                        "tracking_status"=>$tracking_status
                );

                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$trip_arr,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip id','data'=>(object)[],'error'=>['Invalid Trip id']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Trip id is required','data'=>(object)[],'error'=>['Trip id is required']], 200);
        }
    }
    public function edit_trip(Request $request){
        $trip_id = $request->input('trip_id');

        if($trip_id != ''){
            $trip = TripsModel::where('id',$trip_id)->has('user')->first();

            if($trip != null){
                $categories = array();
                if($trip->category_wont_carry != ''){
                    $cate = json_decode($trip->category_wont_carry);
                    if(is_array($cate)){
                        foreach($cate as $cat){
                            $cat_detail = CategoryModel::where('id',$cat)->first();
                            if($cat_detail != null){
                                $categories[] = array('id'=>$cat_detail->id,'name'=>$cat_detail->category_name); 
                            }
                        }
                    }
                }
                $notes = array();
                if($trip->notes != ''){
                    $note = json_decode($trip->notes);
                    if(is_array($note)){
                        foreach($note as $not){
                            $notes[] = array('note'=>$not); 
                        }
                    }
                }
                $trip_status = 'Added';
                if($trip->trip_status == 1){
                    $trip_status = 'Completed';
                }else if($trip->trip_status == 2){
                    $trip_status = 'Cancelled';
                }else if($trip->trip_status == 3){
                    $trip_status = 'Deleted';
                }else if($trip->trip_status == 5){
                    $trip_status = 'Cancelled By System';
                }
                $trip_arr = array(
                        "trip_id"=>$trip->id,
                        "trip_user_id"=>$trip->user_id,
                        "ticket_first_name"=>$trip->ticket_first_name,
                        "ticket_last_name"=>$trip->ticket_last_name,
                        "from"=>$trip->trip_from,
                        "to"=>$trip->trip_to,
                        "trip_from_full_txt"=>$trip->trip_from_full_txt,
                        "trip_to_full_txt"=>$trip->trip_to_full_txt,
                        //"from_name"=>$trip->from->city,
                        //"to_name"=>$trip->to->city,
                        "from_name"=>$trip->trip_from,
                        "to_name"=>$trip->trip_to,
                        "trip_from_area"=>$trip->trip_from_area,
                        "trip_to_area"=>$trip->trip_to_area,
                        "departure_date"=>date('d-m-Y h:iA',strtotime($trip->departure_datetime)),
                        "arrival_date"=>date('d-m-Y h:iA',strtotime($trip->arrival_datetime)),
                        "transport_type"=>$trip->transport_type,
                        "available_weight"=>($trip->avail_luggage_weight != '')?$trip->avail_luggage_weight:'',
                        "available_weight_unit"=>($trip->avail_luggage_weight_unit != '')?$trip->avail_luggage_weight_unit:'g',
                        "current_address"=>($trip->current_address != '')?$trip->current_address:'',
                        "current_pincode"=>($trip->current_pincode != '')?$trip->current_pincode:'',
                        "destination_address"=>($trip->destination_address != '')?$trip->destination_address:'',
                        "destination_pincode"=>($trip->destination_pincode != '')?$trip->destination_pincode:'',
                        "ticket_number"=>($trip->ticket_number != '')?$trip->ticket_number:'',
                        "categories_wont_carry"=>$categories,
                        "notes"=>$notes,
                );

                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$trip_arr,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip id','data'=>(object)[],'error'=>['Invalid Trip id']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Trip id is required','data'=>(object)[],'error'=>['Trip id is required']], 200);
        }
    }
    public function my_trips(Request $request){
        $user_id = $request->input('user_id');
        $trip_id = $request->input('trip_id');
        $today = $request->input('today');
        $from = $request->input('from');
        $to = $request->input('to');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $type = $request->input('type');

        if($user_id != ''){
            $user = Users_Model::where('id',$user_id)->count();
            if($user > 0){
                if($today == 1){
                    if($trip_id != ''){
                        $trips = TripsModel::where('trip_status','0')->where('id',$trip_id)->where('user_id',$user_id)->whereDate('created_at','=',date('Y-m-d'))->has('user')->get();
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Trip Id is required','data'=>[],'error'=>['Trip Id is required']], 200);
                    }
                    
                }else{
                    $query = TripsModel::query();
                    if(!empty($from)){
                        $query->where('trip_from_full_txt',$from);
                    }
                    if(!empty($to)){
                        $query->where('trip_to_full_txt',$to);
                    }
                    if(!empty($start_date)){
                        $query->whereDate('departure_datetime','>=',$start_date);
                    }
                    if(!empty($end_date)){
                        $query->whereDate('departure_datetime','<=',$end_date);
                    }
                    //if((empty($start_date)) && (empty($end_date)) ){
                      //  $query->whereDate('arrival_datetime','>=',date('Y-m-d'));
                    //}
                    
                    $query->where('is_created','1');
                    $query->where('trip_status','!=','3');
                    
                    $trips = $query->where('user_id',$user_id)->has('user')->orderBy('id','desc')->get();
                }
                $trip_arr = array();
                if(count($trips) > 0){
                    foreach($trips as $trip){
                        $categories = array();
                        if($trip->category_wont_carry != ''){
                            $cate = json_decode($trip->category_wont_carry);
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
                        if($trip->notes != ''){
                            $note = json_decode($trip->notes);
                            if(is_array($note)){
                                foreach($note as $not){
                                    $notes[] = array('note'=>$not); 
                                }
                            }
                        }
                        $trip_status = 'Added';
                        
                        $trip_deals = $trip->deals;
                        
                        if(count($trip_deals) > 0){
                            foreach($trip_deals as $deal){
                                if($deal->request_status != 2){
                                    if($deal->request_by == $trip->user_id){
                                        $trip_status = 'Requests';
                                    }
                                }
                                
                                if($deal->request_status == 1){
                                    $trip_status = 'Accepted';
                                }
                            }
                        }
                        
                        if($trip->trip_status == 1){
                            $trip_status = 'Completed';
                        }else if($trip->trip_status == 2){
                            $trip_status = 'Cancelled';
                        }else if($trip->trip_status == 3){
                            $trip_status = 'Deleted';
                        }else if($trip->trip_status == 4){
                            $trip_status = 'Booked';
                        }else if($trip->trip_status == 5){
                            $trip_status = 'Cancelled By System';
                        }
                        
                        if(!empty($type)){
                            $type = ucwords($type);
                            if($type == $trip_status){
                                $trip_arr[] = array(
                                    "trip_id"=>$trip->id,
                                    "trip_user_id"=>$trip->user_id,
                                    "user_rating"=>$trip->user->profile_review,
                                    "ticket_image"=>$trip->ticket_image_url,
                                    "user_image"=>$trip->user->profile_image_url,
                                    "user_name"=>$trip->user->name,
                                    "ticket_name"=>$trip->ticket_first_name.' '.$trip->ticket_last_name,
                                    //"from"=>$trip->from->city,
                                    //"to"=>$trip->to->city,
                                    "from"=>$trip->trip_from,
                                    "to"=>$trip->trip_to,
                                    "trip_from_area"=>($trip->trip_from_area != '')?$trip->trip_from_area:'',
                                    "trip_to_area"=>($trip->trip_to_area != '')?$trip->trip_to_area:'',
                                    "current_address"=>$trip->current_address,
                                    "destination_address"=>$trip->destination_address,
                                    "departure_date"=>date('d M Y / h:i A',strtotime($trip->departure_datetime)),
                                    "arrival_date"=>date('d M Y / h:i A',strtotime($trip->arrival_datetime)),
                                    "transport_type"=>ucfirst($trip->transport_type),
                                    "available_weight"=>$trip->avail_luggage_weight,
                                    "available_weight_unit"=>$trip->avail_luggage_weight_unit,
                                    "ticket_number"=>$trip->ticket_number,
                                    "trip_status"=>$trip_status,
                                    "categories_wont_carry"=>$categories,
                                    "notes"=>$notes,
                                );
                            }
                        }else{
                            $trip_arr[] = array(
                                "trip_id"=>$trip->id,
                                "trip_user_id"=>$trip->user_id,
                                "user_rating"=>$trip->user->profile_review,
                                "ticket_image"=>$trip->ticket_image_url,
                                "user_image"=>$trip->user->profile_image_url,
                                "user_name"=>$trip->user->name,
                                "ticket_name"=>$trip->ticket_first_name.' '.$trip->ticket_last_name,
                                //"from"=>$trip->from->city,
                                //"to"=>$trip->to->city,
                                "from"=>$trip->trip_from,
                                "to"=>$trip->trip_to,
                                "trip_from_area"=>($trip->trip_from_area != '')?$trip->trip_from_area:'',
                                "trip_to_area"=>($trip->trip_to_area != '')?$trip->trip_to_area:'',
                                "current_address"=>$trip->current_address,
                                "destination_address"=>$trip->destination_address,
                                "departure_date"=>date('d M Y / h:i A',strtotime($trip->departure_datetime)),
                                "arrival_date"=>date('d M Y / h:i A',strtotime($trip->arrival_datetime)),
                                "transport_type"=>ucfirst($trip->transport_type),
                                "available_weight"=>$trip->avail_luggage_weight,
                                "available_weight_unit"=>$trip->avail_luggage_weight_unit,
                                "ticket_number"=>$trip->ticket_number,
                                "trip_status"=>$trip_status,
                                "categories_wont_carry"=>$categories,
                                "notes"=>$notes,
                            );
                        }
                    }
                    
                    if(count($trip_arr) > 0){
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$trip_arr,'error'=>[]], 200);
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','data'=>[],'error'=>['No Trips Found']], 200);
                    }
                    
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','data'=>[],'error'=>['No Trips Found']], 200);
                }

            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','data'=>[],'error'=>['Invalid User Id']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Trip id is required','data'=>[],'error'=>['Trip id is required']], 200);
        }
    }
    public function create_trip(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        
        $trip_from_area = ($request->input('trip_from_area') != '')?$request->input('trip_from_area'):'';
        $trip_to_area = ($request->input('trip_to_area') != '')?$request->input('trip_to_area'):'';
        
        if(!empty($user)){
            //$fromCity = CityModel::where('status','1')->where('id',$request->input('trip_from'))->first();
            $fromCity = $request->input('trip_from');

            if(!empty($fromCity)){
                //$toCity = CityModel::where('status','1')->where('id',$request->input('trip_to'))->first();
                $toCity = $request->input('trip_to');
                if(!empty($toCity)){
                    $cate_wont_carry = json_decode($request->input('category_wont_carry'));
                    
                    if(count($cate_wont_carry) > 0){
                        if(!(is_array($cate_wont_carry))){
                            $cate_wont_carry = json_encode(array());
                        }else{
                            $cate_wont_carry = json_encode($cate_wont_carry);
                        }
                    }else{
                        $cate_wont_carry = json_encode(array());
                    }
                    

                    $notes = json_decode($request->input('notes'));
                    
                    if(count($notes) > 0){
                        if(!(is_array($notes))){
                            $notes = json_encode(array());
                        }else{
                            $notes = json_encode($notes);
                        }
                    }else{
                        $notes = json_encode(array());
                    }

                    $arr = array(
                        "user_id"=>$request->input('user_id'),
                        "trip_from"=>$request->input('trip_from'),
                        "trip_to"=>$request->input('trip_to'),
                        "trip_from_full_txt"=>$request->input('trip_from_full_txt'),
                        "trip_to_full_txt"=>$request->input('trip_to_full_txt'),
                        "trip_from_area"=>$trip_from_area,
                        "trip_to_area"=>$trip_to_area,
                        "departure_datetime"=>date('Y-m-d H:i:s',strtotime(str_replace('/','-',$request->input('departure_datetime')))),
                        "arrival_datetime"=>date('Y-m-d H:i:s',strtotime(str_replace('/','-',$request->input('arrival_datetime')))),
                        "transport_type"=>$request->input('transport_type'),
                        "ticket_number"=>$request->input('ticket_number'),
                        "avail_luggage_weight"=>$request->input('avail_luggage_weight'),
                        "avail_luggage_weight_unit"=>($request->input('avail_luggage_weight_unit') != '')?$request->input('avail_luggage_weight_unit'):'g',
                        "ticket_first_name"=>$request->input('ticket_first_name'),
                        "ticket_last_name"=>$request->input('ticket_last_name'),
                        "current_address"=>$request->input('current_address'),
                        "current_pincode"=>$request->input('current_pincode'),
                        "destination_address"=>$request->input('destination_address'),
                        "destination_pincode"=>$request->input('destination_pincode'),
                        "notes"=>$notes,
                        "category_wont_carry"=>$cate_wont_carry,
                        "trip_status"=>'0',
                    );
                    if($request->file('ticket_image')){
                        $image = $request->file('ticket_image');
                        if($image->isValid()){
                            
                            $extension = $image->getClientOriginalExtension();
                            $fileName = rand(100,999999).time().'.'.$extension;
                            $image_path = public_path('assets/tickets/');
                            $request->ticket_image->move($image_path, $fileName);
                            $arr['ticket_image'] = 'assets/tickets/'.$fileName;
                        }
                    }
                    $trip_id = TripsModel::create($arr)->id;

                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'Trip Created Successfully','trip_id'=>$trip_id,'error'=>[]], 200);

                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid To Ciy','trip_id'=>'','error'=>['Invalid To Ciy']], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid From Ciy','trip_id'=>'','error'=>['Invalid From Ciy']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','trip_id'=>'','error'=>['Invalid User Id']], 200);
        }
    }
    public function update_trip(Request $request){
        $trip_id = $request->input('trip_id');
        $trip = TripsModel::where('id',$trip_id)->first();
        
        $departure_datetime = $request->input('departure_datetime');
        $arrival_datetime = $request->input('arrival_datetime');
        
        if(!empty($trip)){
            //$fromCity = CityModel::where('status','1')->where('id',$request->input('trip_from'))->first();
            $fromCity = $request->input('trip_from');

            if(!empty($fromCity)){
                //$toCity = CityModel::where('status','1')->where('id',$request->input('trip_to'))->first();
                $toCity = $request->input('trip_to');
                if(!empty($toCity)){
                    
                    if(strtotime($departure_datetime) > strtotime(date('Y-m-d H:i:s'))){
                        if(strtotime($arrival_datetime) > strtotime(date('Y-m-d H:i:s'))){
                            if(strtotime($arrival_datetime) > strtotime($departure_datetime)){
                                
                                $cate_wont_carry = json_decode($request->input('category_wont_carry'));
                                if(!empty($cate_wont_carry)){
                                    if(!(is_array($cate_wont_carry))){
                                        $cate_wont_carry = array();
                                    }else{
                                        $cate_wont_carry = json_encode($cate_wont_carry);
                                    }
                                }
                                $notes = json_decode($request->input('notes'));
                                if(!empty($notes)){
                                    if(!(is_array($notes))){
                                        $notes = array();
                                    }else{
                                        $notes = json_encode($notes);
                                    }
                                }
                                
                                $trip_from_area = ($request->input('trip_from_area') != '')?$request->input('trip_from_area'):'';
                                $trip_to_area = ($request->input('trip_to_area') != '')?$request->input('trip_to_area'):'';
                                
                                $arr = array(
                                    "user_id"=>$request->input('user_id'),
                                    "trip_from"=>$request->input('trip_from'),
                                    "trip_to"=>$request->input('trip_to'),
                                    "trip_from_full_txt"=>$request->input('trip_from_full_txt'),
                                    "trip_to_full_txt"=>$request->input('trip_to_full_txt'),
                                    "trip_from_area"=>$trip_from_area,
                                    "trip_to_area"=>$trip_to_area,
                                    "departure_datetime"=>date('Y-m-d H:i:s',strtotime(str_replace('/','-',$request->input('departure_datetime')))),
                                    "arrival_datetime"=>date('Y-m-d H:i:s',strtotime(str_replace('/','-',$request->input('arrival_datetime')))),
                                    "transport_type"=>$request->input('transport_type'),
                                    "ticket_number"=>$request->input('ticket_number'),
                                    "avail_luggage_weight"=>$request->input('avail_luggage_weight'),
                                    "avail_luggage_weight_unit"=>($request->input('avail_luggage_weight_unit') != '')?$request->input('avail_luggage_weight_unit'):'g',
                                    "ticket_first_name"=>$request->input('ticket_first_name'),
                                    "ticket_last_name"=>$request->input('ticket_last_name'),
                                    "current_address"=>$request->input('current_address'),
                                    "current_pincode"=>$request->input('current_pincode'),
                                    "destination_address"=>$request->input('destination_address'),
                                    "destination_pincode"=>$request->input('destination_pincode'),
                                    "notes"=>$notes,
                                    "category_wont_carry"=>$cate_wont_carry,
                                );
                                if($request->file('ticket_image')){
                                    $image = $request->file('ticket_image');
                                    if($image->isValid()){
                                        if(!empty($trip->ticket_image)){
                                            $img = public_path('/').$trip->ticket_image;
                                            if(file_exists($img)){
                                                unlink($img);
                                            }
                                        }
            
                                        $extension = $image->getClientOriginalExtension();
                                        $fileName = rand(100,999999).time().'.'.$extension;
                                        $image_path = public_path('assets/tickets/');
                                        $request->ticket_image->move($image_path, $fileName);
                                        $arr['ticket_image'] = 'assets/tickets/'.$fileName;
                                    }
                                }
                                TripsModel::where('id',$trip_id)->update($arr);
                                
                                $trip = TripsModel::where('id',$trip_id)->first();
                                if($trip->is_created == 1){
                                    $notification = new NotificationController();
                                    $notification->matchingTripOnEdit($trip);
                                }
                                
            
                                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Trip Updated Successfully','error'=>[]], 200);
                            }else{
                                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Arrival Time Should Be Grater Than Departure Time','error'=>['Arrival Time Should Be Grater Than Departure Time']], 200);
                            }
                        }else{
                            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Arrival Time Should Be Grater Than Current Time','error'=>['Arrival Time Should Be Grater Than Current Time']], 200);
                        }
                    }else{
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>'Departure Time Should Be Grater Than Current Time','error'=>['Departure Time Should Be Grater Than Current Time']], 200);
                    }
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid To Ciy Id','error'=>['Invalid To Ciy Id']], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid From Ciy Id','error'=>['Invalid From Ciy Id']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip Id','error'=>['Invalid Trip Id']], 200);
        }
    }

    public function delete_trip(Request $request){
        $trip_id = $request->input('trip_id');
        $type = $request->input('type');
        $cancel_reason = ($request->input('cancel_reason') != '')?$request->input('cancel_reason'):'';
        
        $trip = TripsModel::where('id',$trip_id)->first();

        if(!empty($trip)){
            NotificationModel::where('notification_type','matching_trip')->where('reference_id',$trip_id)->update(['type'=>'no_action']);
            
            if($type == 'cancel'){
                $trip->trip_status = 2;
                $trip->cancel_reason = $cancel_reason;
                $trip->save();
                
                $trip_deals = $trip->deals;
                if(count($trip_deals) > 0){
                    foreach($trip_deals as $deal){
                        if($deal->request_status != 2){
                            $arr = array(
                                "request_status"=>'2',
                                "cancel_by"=>'traveller',
                                "cancel_by_id"=>$trip->user_id,
                            );
                            
                            DealsModel::where('id',$deal->id)->update($arr);
                            
                            if($deal->request_status == 1){
                                ShipmentModel::where('id',$deal->shipment->id)->update(['status'=>1,'tracking_status'=>'placed']);
                                NotificationModel::where('type','deal')->where('reference_id',$deal->id)->update(['type'=>'reject_deal']);
                                NotificationModel::where('type','deal')->where('reference_id',$deal->shipment->shipment_uniqueid)->update(['type'=>'reject_deal']);
                                NotificationModel::where('type','tracking')->where('reference_id',$deal->shipment->shipment_uniqueid)->update(['type'=>'reject_deal']);
                                
                                $notification = new NotificationController();
                                $notification->cancelTrip($deal);
                            }else{
                                // Reject Other Deals Notification
                                $notification = new NotificationController();
                                $notification->changeDealStatus($deal,'2');
                            }
                        }
                    }
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Trip Canceled Successfully','error'=>[]], 200);
            }else{
                TripsModel::where('id',$trip_id)->update(['trip_status'=>3]);
                
                $trip_deals = $trip->deals;
                if(count($trip_deals) > 0){
                    foreach($trip_deals as $deal){
                        if($deal->request_status != 2){
                            $arr = array(
                                "request_status"=>'2',
                                "cancel_by"=>'traveller',
                                "cancel_by_id"=>$trip->user_id,
                            );
                            
                            DealsModel::where('id',$deal->id)->update($arr);
                            
                            if($deal->request_status == 1){
                                ShipmentModel::where('id',$deal->shipment->id)->update(['status'=>1,'tracking_status'=>'placed']);
                                NotificationModel::where('type','deal')->where('reference_id',$deal->id)->update(['type'=>'reject_deal']);
                                NotificationModel::where('type','deal')->where('reference_id',$deal->shipment->shipment_uniqueid)->update(['type'=>'reject_deal']);
                                NotificationModel::where('type','tracking')->where('reference_id',$deal->shipment->shipment_uniqueid)->update(['type'=>'reject_deal']);
                                
                                $notification = new NotificationController();
                                $notification->cancelTrip($deal);
                            }else{
                                // Reject Other Deals Notification
                                $notification = new NotificationController();
                                $notification->changeDealStatus($deal,'2');
                            }
                        }
                    }
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Trip Deleted Successfully','error'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip Id','error'=>['Invalid Trip Id']], 200);
        }
    }
    public function completed_trips(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();

        if(!empty($user)){
            $trips = TripsModel::where('trip_status','1')->where('user_id',$user_id)->has('user')->get();
            if(count($trips) > 0){
                foreach($trips as $trip){
                    $traveller_reward = 0;
                    if(!empty($trip->deal)){
                        if(!empty($trip->deal->shipment)){
                            $traveller_reward = $trip->deal->shipment->total_reward;
                        }
                    }
                    $trip_arr[] = array(
                            "trip_id"=>$trip->id,
                            "trip_user_id"=>$trip->user_id,
                            "user_rating"=>$trip->user->profile_review,
                            "ticket_image"=>$trip->ticket_image_url,
                            "user_image"=>$trip->user->profile_image_url,
                            "user_name"=>$trip->user->name,
                            "ticket_name"=>$trip->ticket_first_name.' '.$trip->ticket_last_name,
                            //"from"=>$trip->from->city,
                            //"to"=>$trip->to->city,
                            "from"=>$trip->trip_from,
                            "to"=>$trip->trip_to,
                            "departure_date"=>date('d M Y',strtotime($trip->departure_datetime)),
                            "arrival_date"=>date('d M Y',strtotime($trip->arrival_datetime)),
                            "transport_type"=>ucfirst($trip->transport_type),
                            "available_weight"=>$trip->avail_luggage_weight,
                            "available_weight_unit"=>$trip->avail_luggage_weight_unit,
                            "ticket_number"=>$trip->ticket_number,
                            "traveller_reward"=>$traveller_reward,
                    );
                }

                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','data'=>$trip_arr,'error'=>[]], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','data'=>[],'error'=>['No Trips Found']], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    public function get_trips_for_shipment(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        
        if(!empty($user)){
            $shipment_id = $request->input('shipment_id');
            $shipment = ShipmentModel::where('is_created','1')->where('id',$shipment_id)->has('user')->first();
            if(!empty($shipment)){
                $trips = TripsModel::where('trip_status','0')->where('user_id',$user_id)
                    ->where('is_created','1')
                    ->where('trip_from',$shipment->shipment_from)
                    ->where('trip_to',$shipment->shipment_to)
                    ->whereDate('arrival_datetime','<=',$shipment->expected_delivery_date)
                    ->where('departure_datetime','>=',date('Y-m-d H:i'))
                    ->has('user')->get();
                if(count($trips) > 0){
                    foreach($trips as $trip){
                        $deal_status = 0;
                        $trip_deals = $trip->deals;
                        
                        if(count($trip_deals) > 0){
                            foreach($trip_deals as $deal){
                                if($deal->request_status == 1){
                                    $deal_status = 1;
                                }else if(($deal->request_status == 2)){
                                    // If the deal is between current user and trip user
                                    if($deal->shipment->user->id == $shipment->created_by){
                                        $curr = time();
                                        $other = strtotime($deal->updated_at);
                                        $datediff = $curr - $other;

                                        $diff_days = round($datediff / (60 * 60 * 24));
                                        if($diff_days < 1){
                                            $deal_status = 1;
                                        }
                                    }
                                }
                            }
                        }
                        
                        if($deal_status == 1){
                            continue;
                        }
                        
                        $data[] = array(
                            "trip_id"=>$trip->id,
                            "trip_user_id"=>$trip->user_id,
                            "user_rating"=>$trip->user->profile_review,
                            "ticket_image"=>$trip->ticket_image_url,
                            "user_image"=>$trip->user->profile_image_url,
                            "user_name"=>$trip->user->name,
                            "ticket_name"=>$trip->ticket_first_name.' '.$trip->ticket_last_name,
                            //"from"=>$trip->from->city,
                            //"to"=>$trip->to->city,
                            "from"=>$trip->trip_from,
                            "to"=>$trip->trip_to,
                            "departure_date"=>date('d M Y',strtotime($trip->departure_datetime)),
                            "arrival_date"=>date('d M Y',strtotime($trip->arrival_datetime)),
                            "transport_type"=>ucfirst($trip->transport_type),
                            "available_weight"=>$trip->avail_luggage_weight,
                            "available_weight_unit"=>$trip->avail_luggage_weight_unit,
                            "ticket_number"=>$trip->ticket_number,
                        );
                    }
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','error'=>['No Trips Found'],'data'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipments Id','error'=>['Invalid Shipments Id'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    public function get_requested_trips(Request $request){
        $shipment_id = $request->input('shipment_id');

        $shipment = ShipmentModel::where('is_created','1')->where('id',$shipment_id)->has('user')->first();
        $data = array();
        $req_trips = array();
        if(!empty($shipment)){
            $deals = DealsModel::whereIn('request_status',['0','1'])->where('shipment_id',$shipment_id)->where('request_by',$shipment->created_by)->has('shipment')->has('trip')->get();
            if(count($deals) > 0){
                foreach($deals as $deal){
                    $data[] = array(
                        "deal_id"=>$deal->id,
                        "shipment_id"=>$shipment_id,
                        "trip_id"=>$deal->trip->id,
                        //"from"=>$deal->trip->from->city,
                        //"to"=>$deal->trip->to->city,
                        "from"=>$deal->trip->trip_from,
                        "to"=>$deal->trip->trip_to,
                        "title"=>$deal->trip->user->name,
                        "image"=>$deal->trip->user->profile_image_url,
                        "date"=>date('d M Y',strtotime($deal->trip->departure_datetime)),
                        "status"=>($deal->request_status == 0)?'Requested':(($deal->request_status == 1)?'Accepted':(($deal->request_status == 2)?'Cancelled':'')),
                        "is_requested"=>'1'
                    );
                    $req_trips[] = $deal->trip->id;
                }
            }
            $deals = DealsModel::whereIn('request_status',['0','1'])->where('shipment_id',$shipment_id)->where('request_to',$shipment->created_by)->has('shipment')->has('trip')->get();
            if(count($deals) > 0){
                foreach($deals as $deal){
                    $req_trips[] = $deal->trip->id;
                }
            }
            
            $trips = TripsModel::where('trip_status','0')
                ->where('user_id','!=',$shipment->created_by)
                ->where('is_created','1')
                ->where('trip_from',$shipment->shipment_from)
                ->where('trip_to',$shipment->shipment_to)
                ->whereNotIn('id', $req_trips)
                ->whereDate('arrival_datetime','<=',$shipment->expected_delivery_date)
                ->where('departure_datetime','>=',date('Y-m-d H:i')) //Change Arrival to departure time 29-10
                ->has('user')->get();
                
                
            if(count($trips) > 0){
                foreach($trips as $trip){
                    $deal_status = 0;
                    
                    $trip_deals = $trip->deals;
                    
                    if(count($trip_deals) > 0){
                        foreach($trip_deals as $deal){
                            if($deal->request_status == 1){
                                $deal_status = 1;
                            }else if(($deal->request_status == 2)){
                                // If the deal is between current user and trip user
                                if($deal->shipment->user->id == $shipment->created_by){
                                    $curr = time();
                                    $other = strtotime($deal->updated_at);
                                    $datediff = $curr - $other;
    
                                    $diff_days = round($datediff / (60 * 60 * 24));
                                    if($diff_days < 1){
                                        $deal_status = 1;
                                    }
                                }
                            }
                        }
                    }
                    
                    if($deal_status == 1){
                        continue;
                    }
                    
                    $data[] = array(
                        "deal_id"=>0,
                        "shipment_id"=>$shipment_id,
                        "trip_id"=>$trip->id,
                        //"from"=>$trip->from->city,
                        //"to"=>$trip->to->city,
                        "from"=>$trip->trip_from,
                        "to"=>$trip->trip_to,
                        "title"=>$trip->user->name,
                        "image"=>$trip->user->profile_image_url,
                        "date"=>date('d M Y',strtotime($trip->departure_datetime)),
                        "status"=>'',
                        "is_requested"=>'0'
                    );
                }
            }
            if(count($data) > 0){
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Trips Found','error'=>['No Trips Found'],'data'=>[]], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipments Id','error'=>['Invalid Shipments Id'],'data'=>[]], 200);
        }
    }

}
