<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\ShipmentModel;
use App\TripsModel;

use App\ShipmentProductModel;
use App\Users_Model;
use App\CityModel;
use App\CategoryModel;
use App\ProductsModel;
use App\SettingsModel;
use App\DealsModel;
use App\RatingModel;
use App\NotificationModel;


class ShipmentController extends Controller
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
    
    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
    
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
    
        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    //Api Functions
    public function create_shipment(Request $request){
        
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        $settings = SettingsModel::where('id','1')->first();
        $townbuddy_charges_percent = ($settings->townbuddy_charges_percent != '')?$settings->townbuddy_charges_percent:'0';
        $tb_shipment_price_percent = ($settings->tb_shipment_price_percent != '')?$settings->tb_shipment_price_percent:'0';
        $tb_volume_charges = ($settings->tb_volume_charges != '')?$settings->tb_volume_charges:'0';
        $tb_weight_charges = ($settings->tb_weight_charges != '')?$settings->tb_weight_charges:'0';
        $gst_charges = ($settings->gst_on_quotation != '')?$settings->gst_on_quotation:'0';
        
        //$tb_size_charges_s = ($settings->tb_size_charges_s != '')?$settings->tb_size_charges_s:'0';
        //$tb_size_charges_m = ($settings->tb_size_charges_m != '')?$settings->tb_size_charges_m:'0';
        //$tb_size_charges_l = ($settings->tb_size_charges_l != '')?$settings->tb_size_charges_l:'0';
        //$tb_size_charges_h = ($settings->tb_size_charges_h != '')?$settings->tb_size_charges_h:'0';
        
        $shipment_from_area = ($request->input('shipment_from_area') != '')?$request->input('shipment_from_area'):'';
        $shipment_to_area = ($request->input('shipment_to_area') != '')?$request->input('shipment_to_area'):'';
        
        $person_addr_pickup_lat = ($request->input('person_addr_pickup_lat') != '')?$request->input('person_addr_pickup_lat'):'';
        $person_addr_pickup_lng = ($request->input('person_addr_pickup_lng') != '')?$request->input('person_addr_pickup_lng'):'';
        $person_addr_delivery_lat = ($request->input('person_addr_delivery_lat') != '')?$request->input('person_addr_delivery_lat'):'';
        $person_addr_delivery_lng = ($request->input('person_addr_delivery_lng') != '')?$request->input('person_addr_delivery_lng'):'';
        
        $traveler_reward_by_shopper = ($request->input('traveler_reward') != '')?$request->input('traveler_reward'):''; //18-11-21
        
        //return response()->json(['status'=>false,'status_code'=>'401','message'=>json_decode($request->input('notes')),'error'=>[],'data'=>''], 200);
        if(!empty($user)){
            //$fromCity = CityModel::where('status','1')->where('id',$request->input('shipment_from'))->first();
            $fromCity = $request->input('shipment_from');
            if(!empty($fromCity)){
                //$toCity = CityModel::where('status','1')->where('id',$request->input('shipment_to'))->first();
                $toCity = $request->input('shipment_to');
                if(!empty($toCity)){
                    try {
                        
                        $distance_reward = 0;
                        if((!empty($person_addr_pickup_lat)) && (!empty($person_addr_pickup_lng)) && (!empty($person_addr_delivery_lat)) && (!empty($person_addr_delivery_lng)) ){
                            $distance = $this->distance($person_addr_pickup_lat,$person_addr_pickup_lng,$person_addr_delivery_lat,$person_addr_delivery_lng,'K');
                            $distance = round($distance,2);
                            $rangee = json_decode($settings->price_range);
                            if(!empty($rangee)){
                                foreach($rangee as $range){
                                    if(($range->from_distance <= $distance) && ($range->to_distance >= $distance) ){
                                        $distance_reward = $range->price;
                                    }
                                }
                            }
                        }

                        $shipment_user_type = 'sender';

                        $productId = $request->input('product_id');
                        $product_quantity = $request->input('product_quantity');
                        $productIdsData = array();
                        if(!empty($productId)){
                            $product = ProductsModel::with('category')->has('category')->where('id',$productId)->where('product_stock_qty','>','0')->first();
                            if(!empty($product)){
                                
                                if($product_quantity <= $product->product_stock_qty){
                                    $reward_prices = array();
                                    //$reward_prices[]['val'] = (($product->product_price*$product_quantity)*$tb_shipment_price_percent)/100;
                                    $reward_prices[]['val'] = (($product->product_length*$product->product_width*$product->product_height)*$tb_volume_charges)*$product_quantity;
                                    
                                    if($product->product_weight_unit == 'kg'){
                                        $reward_prices[]['val'] = ($product->product_weight*($tb_weight_charges*1000))*$product_quantity;
                                    }else if($product->product_weight_unit == 'mg'){
                                        $reward_prices[]['val'] = ($product->product_weight*($tb_weight_charges/1000))*$product_quantity;
                                    }else{
                                        //g
                                        $reward_prices[]['val'] = ($product->product_weight*$tb_weight_charges)*$product_quantity;
                                    }
                                    
                                    
                                    if($product->product_size == 's'){
                                        //$reward_prices[]['val'] = $tb_size_charges_s*$product_quantity;
                                    }
                                    if($product->product_size == 'm'){
                                        //$reward_prices[]['val'] = $tb_size_charges_m*$product_quantity;
                                    }
                                    if($product->product_size == 'l'){
                                        //$reward_prices[]['val'] = $tb_size_charges_l*$product_quantity;
                                    }
                                    if($product->product_size == 'h'){
                                        //$reward_prices[]['val'] = $tb_size_charges_h*$product_quantity;
                                    }
                                    
                                    $reward_prices[]['val'] = $product->category->category_price * $product_quantity;
                                    
                                    if($distance_reward != 0){
                                        $max_traveller_reward = $distance_reward;
                                    }else{
                                        $max_traveller_reward = max(array_column($reward_prices, 'val'));
                                    }
                                    
                                    //$tb_charges = (($product->product_price*$product_quantity)*$townbuddy_charges_percent)/100; //OLD
                                    
                                    if(!empty($traveler_reward_by_shopper)){
                                        $tb_charges = ($traveler_reward_by_shopper * $townbuddy_charges_percent)/100;
                                        $max_traveller_reward = $traveler_reward_by_shopper;
                                    }else{
                                        $tb_charges = ($max_traveller_reward * $townbuddy_charges_percent)/100;
                                    }

                                    $remaining_qty = $product->product_stock_qty - $product_quantity;
                                    $notes = $request->input('notes');
                                    if(!empty($notes)){
                                        $notes = json_decode($request->input('notes'));
                                        if(!(is_array($notes))){
                                            $notes = array();
                                        }else{
                                            $notes = json_encode($notes);
                                        }
                                    }
                                    $shipment_user_type = 'shopper';
                                    $product_total = $product->product_price * $product_quantity;

                                    $productIdsData[] = array('product_id'=>$productId,'product_qty'=>$product_quantity,'product_total'=>$product_total,'tb_charges'=>$tb_charges,'traveller_reward'=>$max_traveller_reward,'remaining_qty'=>$remaining_qty,"notes"=>$notes);
                                }else{
                                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Insufficient Product Quantity','error'=>['Insufficient Product Quantity'],'data'=>''], 200);
                                }
                            }else{
                                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Product Id','error'=>['Invalid Product Id'],'data'=>''], 200);
                            }
                        }
                        
                        $notes = $request->input('notes');
                        if(!empty($notes)){
                            $notes = json_decode($request->input('notes'));
                            if(!(is_array($notes))){
                                $notes = json_encode(array());
                            }else{
                                $notes = json_encode($notes);
                            }
                        }

                        if(!empty($request->input('shipment_id'))){
                            $shipment_id = $request->input('shipment_id');
                            $shipment = ShipmentModel::where('id',$shipment_id)->first();
                            if(!empty($shipment)){
                                $arr = array(
                                    "created_by"=>$request->input('user_id'),
                                    "shipment_from"=>$request->input('shipment_from'),
                                    "shipment_to"=>$request->input('shipment_to'),
                                    "shipment_from_full_txt"=>$request->input('shipment_from_full_txt'),
                                    "shipment_to_full_txt"=>$request->input('shipment_to_full_txt'),
                                    "shipment_from_area"=>$shipment_from_area,
                                    "shipment_to_area"=>$shipment_to_area,
                                    "expected_delivery_date"=>date('Y-m-d',strtotime(str_replace('/','-',$request->input('expected_delivery_date')))),
                                    "person_name_pickup"=>$request->input('person_name_pickup'),
                                    "person_addr_pickup"=>$request->input('person_addr_pickup'),
                                    "person_pincode_pickup"=>$request->input('person_pincode_pickup'),
                                    "person_mobile_pickup"=>$request->input('person_mobile_pickup'),
                                    "person_mobile_cc_pickup"=>($request->input('person_mobile_cc_pickup') != '')?$request->input('person_mobile_cc_pickup'):'',
                                    "person_name_delivery"=>$request->input('person_name_delivery'),
                                    "person_addr_delivery"=>$request->input('person_addr_delivery'),
                                    "person_pincode_delivery"=>$request->input('person_pincode_delivery'),
                                    "person_mobile_delivery"=>$request->input('person_mobile_delivery'),
                                    "person_mobile_cc_delivery"=>($request->input('person_mobile_cc_delivery') != '')?$request->input('person_mobile_cc_delivery'):'',
                                    //"status"=>'0', //Awaiting Approval on sender case
                                    "status"=>'1',
                                );
                                if(!empty($productId)){
                                    $arr['status'] = '1';
                                }

                                ShipmentModel::where('id',$shipment_id)->update($arr);
                            }else{
                                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipment Id','error'=>['Invalid Shipment Id'],'data'=>''], 200);
                            }

                        }else{
                            //$shipmentUid = Str::random(10);
                            $shipmentUid = time();
                            $arr = array(
                                "created_by"=>$request->input('user_id'),
                                "shipment_uniqueid"=>$shipmentUid,
                                "shipment_from"=>$request->input('shipment_from'),
                                "shipment_to"=>$request->input('shipment_to'),
                                "shipment_from_full_txt"=>$request->input('shipment_from_full_txt'),
                                "shipment_to_full_txt"=>$request->input('shipment_to_full_txt'),
                                "shipment_from_area"=>$shipment_from_area,
                                "shipment_to_area"=>$shipment_to_area,
                                "expected_delivery_date"=>date('Y-m-d',strtotime(str_replace('/','-',$request->input('expected_delivery_date')))),
                                "person_name_pickup"=>$request->input('person_name_pickup'),
                                "person_addr_pickup"=>$request->input('person_addr_pickup'),
                                "person_addr_pickup_lat"=>$person_addr_pickup_lat,
                                "person_addr_pickup_lng"=>$person_addr_pickup_lng,
                                "person_pincode_pickup"=>$request->input('person_pincode_pickup'),
                                "person_mobile_pickup"=>$request->input('person_mobile_pickup'),
                                "person_mobile_cc_pickup"=>($request->input('person_mobile_cc_pickup') != '')?$request->input('person_mobile_cc_pickup'):'',
                                "person_name_delivery"=>$request->input('person_name_delivery'),
                                "person_addr_delivery"=>$request->input('person_addr_delivery'),
                                "person_addr_delivery_lat"=>$person_addr_delivery_lat,
                                "person_addr_delivery_lng"=>$person_addr_delivery_lng,
                                "person_pincode_delivery"=>$request->input('person_pincode_delivery'),
                                "person_mobile_delivery"=>$request->input('person_mobile_delivery'),
                                "person_mobile_cc_delivery"=>($request->input('person_mobile_cc_delivery') != '')?$request->input('person_mobile_cc_delivery'):'',
                                //"status"=>'0', //Awaiting Approval on sender case
                                "status"=>'1',
                                "shipment_user_type"=>$shipment_user_type
                            );
                            if(!empty($productId)){
                                $arr['status'] = '1';
                            }
                            
                            $shipment_id = ShipmentModel::create($arr)->id;
                        }

                        if(!empty($productIdsData)){
                            foreach($productIdsData as $productIdData){
                                $productIdData['shipment_id'] = $shipment_id;
                                $remaining_qty = $productIdData['remaining_qty'];
                                unset($productIdData['remaining_qty']);
                                ShipmentProductModel::create($productIdData);

                                ProductsModel::where('id',$productIdData['product_id'])->update(['product_stock_qty'=>$remaining_qty]);
                            }
                        }
                        
                        if(empty($productId)){
                            $prod_category = $request->input('product_category');
                            if(empty($prod_category)){
                                $category = CategoryModel::first();
                                $prod_category = $category->id;
                            }
                            
                            $data = array(
                                "product_name"=>($request->input('product_name') != '')?$request->input('product_name'):'',
                                "product_category"=>$prod_category,
                                "product_unique_id"=>($request->input('product_unique_id') != '')?$request->input('product_unique_id'):'',
                                "product_stock_qty"=>($request->input('product_quantity') != '')?$request->input('product_quantity'):1,
                                "product_weight"=>($request->input('product_weight') != '')?$request->input('product_weight'):'',
                                "product_weight_unit"=>($request->input('product_weight_unit') != '')?$request->input('product_weight_unit'):'g',
                                "product_size"=>($request->input('product_size') != '')?$request->input('product_size'):'',
                                "product_length"=>($request->input('product_length') != '')?$request->input('product_length'):'',
                                "product_width"=>($request->input('product_width') != '')?$request->input('product_width'):'',
                                "product_height"=>($request->input('product_height') != '')?$request->input('product_height'):'',
                                "product_price"=>($request->input('product_value') != '')?$request->input('product_value'):0,
                                "product_mrp"=>($request->input('product_value') != '')?$request->input('product_value'):0,
                                "product_link"=>($request->input('product_link') != '')?$request->input('product_link'):'',
                                "product_status"=>1,
                                "product_author"=>$user_id,
                            );
                            
                            
                            if($request->file('product_image')){
                                $image = $request->file('product_image');
                                if($image->isValid()){
                                    
                                    $extension = $image->getClientOriginalExtension();
                                    $fileName = rand(100,999999).time().'.'.$extension;
                                    $image_path = public_path('assets/products/');
                                    $request->product_image->move($image_path, $fileName);
                                    $data['product_image'] = 'assets/products/'.$fileName;
                                }
                            }

                            $prod_id = ProductsModel::create($data)->id;

                            //Calculate Traveller Reward
                            $reward_prices = array();
                            //$reward_prices[]['val'] = (($data['product_price']*$data['product_stock_qty'])*$tb_shipment_price_percent)/100;
                            $reward_prices[]['val'] = (($data['product_length']*$data['product_width']*$data['product_height'])*$tb_volume_charges)*$data['product_stock_qty'];
                            //$reward_prices[]['val'] = ($data['product_weight']*$tb_weight_charges)*$data['product_stock_qty'];
                            
                            if($data['product_weight_unit'] == 'kg'){
                                $reward_prices[]['val'] = ($data['product_weight']*($tb_weight_charges*1000))*$data['product_stock_qty'];
                            }else if($data['product_weight_unit'] == 'mg'){
                                $reward_prices[]['val'] = ($data['product_weight']*($tb_weight_charges/1000))*$data['product_stock_qty'];
                            }else{
                                //g
                                $reward_prices[]['val'] = ($data['product_weight']*$tb_weight_charges)*$data['product_stock_qty'];
                            }
                            
                            if($data['product_size'] == 's'){
                                //$reward_prices[]['val'] = $tb_size_charges_s*$data['product_stock_qty'];
                            }
                            if($data['product_size'] == 'm'){
                                //$reward_prices[]['val'] = $tb_size_charges_m*$data['product_stock_qty'];
                            }
                            if($data['product_size'] == 'l'){
                                //$reward_prices[]['val'] = $tb_size_charges_l*$data['product_stock_qty'];
                            }
                            if($data['product_size'] == 'h'){
                                //$reward_prices[]['val'] = $tb_size_charges_h*$data['product_stock_qty'];
                            }
                            
                            $prod_category = CategoryModel::where('id',$data['product_category'])->first();
                            if(!empty($prod_category)){
                                $reward_prices[]['val'] = $prod_category->category_price * $data['product_stock_qty'];
                            }else{
                                $reward_prices[]['val'] = 0;
                            }
                            
                            if($distance_reward != 0){
                                $max_traveller_reward = $distance_reward;
                            }else{
                                $max_traveller_reward = max(array_column($reward_prices, 'val'));
                            }
                            
                            //$tb_charges = (($data['product_price']*$data['product_stock_qty'])*$townbuddy_charges_percent)/100; //OLD
                            if(!empty($traveler_reward_by_shopper)){
                                $tb_charges = ($traveler_reward_by_shopper * $townbuddy_charges_percent)/100;
                                $max_traveller_reward = $traveler_reward_by_shopper;
                            }else{
                                $tb_charges = ($max_traveller_reward * $townbuddy_charges_percent)/100;
                            }
                            
                            $product_total = $data['product_price']*$data['product_stock_qty'];
                            
                            $reward_currency = ($request->input('reward_currency') != '')?$request->input('reward_currency'):'rupees';

                            ShipmentProductModel::create(['shipment_id'=>$shipment_id,'product_id'=>$prod_id,'product_qty'=>$data['product_stock_qty'],'product_total'=>$product_total,'tb_charges'=>$tb_charges,'traveller_reward'=>$max_traveller_reward,'reward_currency'=>$reward_currency,"notes"=>$notes]);
                        }
                        
                        //Calculate GST
                        $shipment = ShipmentModel::where('id',$shipment_id)->first();
                        $shipment_gst_charge = ($shipment->sub_total_quotation * $gst_charges) / 100;
                        $shipment->gst_charges = $shipment_gst_charge;
                        $shipment->save();
                        
                        return response()->json(['status'=>true,'status_code'=>'200','message'=>'Shipment Created Successfully','error'=>[],'data'=>$shipment_id], 200);

                    } catch (\Exception $e) {
                        
                        return response()->json(['status'=>false,'status_code'=>'401','message'=>$e->getMessage(),'error'=>[$e->getMessage()],'data'=>''], 200);
                    }

                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid To Ciy','error'=>['Invalid To Ciy'],'data'=>''], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid From Ciy','error'=>['Invalid From Ciy'],'data'=>''], 200);
            }

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>''], 200);
        }
    }
    public function get_shipment_details(Request $request){
        $shipment_id = $request->input('shipment_id');
        $user_id = $request->input('user_id');
        $shipment = ShipmentModel::where('id',$shipment_id)->has('user')->first();
        
        if(!empty($shipment)){
            $ship_deals = $shipment->deals;
            $trip_id = '0';
            $deal_id = '0';
            $trip_user_id = '0';
            $is_requested = '0';
            $status = 'Added';
            if(count($ship_deals) > 0){
                $status = 'Requests';
                foreach($ship_deals as $deal){
                    if($deal->request_status == 1){
                        $status = 'Accepted';
                        $trip_id = $deal->trip_id;
                        $deal_id = $deal->id;
                        $trip_user_id = $deal->trip->user_id;
                    }
                    if(!empty($user_id)){
                        if(($deal->request_by == $user_id) || ($deal->request_to == $user_id) ){
                            $is_requested = '1';
                        }
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
            $tracking_status = ucwords(str_replace("_"," ",$shipment->tracking_status));
            
            $rated = RatingModel::where('rate_by',$user_id)->where('shipment_id',$shipment->id)->first();
            $is_rated = '0';
            if(!empty($rated)){
                $is_rated = '1';
            }
            $currency = '₹';
            
            $data = array(
                "shipment_id"=>$shipment->id,
                "trip_id"=>$trip_id,
                "deal_id"=>(string)$deal_id,
                "trip_user_id"=>(string)$trip_user_id,
                "shipment_uniqueid"=>$shipment->shipment_uniqueid,
                "shipment_user_id"=>$shipment->user->id,
                "shipment_user_name"=>$shipment->user->name,
                "shipment_user_image"=>$shipment->user->profile_image_url,
                "shipment_user_type"=>$shipment->shipment_user_type,
                "shipment_user_rating"=>$shipment->user->profile_review,
                "shipment_from"=>$shipment->shipment_from.$shipment->country_from_iso,
                //"shipment_from_name"=>$shipment->from->city,
                "shipment_from_name"=>$shipment->shipment_from.$shipment->country_from_iso,
                "shipment_to"=>$shipment->shipment_to.$shipment->country_to_iso,
                //"shipment_to_name"=>$shipment->to->city,
                "shipment_to_name"=>$shipment->shipment_to.$shipment->country_to_iso,
                "expected_delivery_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                "person_name_pickup"=>$shipment->person_name_pickup,
                "person_addr_pickup"=>$shipment->person_addr_pickup,
                "person_pincode_pickup"=>$shipment->person_pincode_pickup,
                "person_mobile_pickup"=>$shipment->person_mobile_pickup,
                "person_mobile_cc_pickup"=>$shipment->person_mobile_cc_pickup,
                "person_name_delivery"=>$shipment->person_name_delivery,
                "person_addr_delivery"=>$shipment->person_addr_delivery,
                "person_pincode_delivery"=>$shipment->person_pincode_delivery,
                "person_mobile_delivery"=>$shipment->person_mobile_delivery,
                "person_mobile_cc_delivery"=>$shipment->person_mobile_cc_delivery,
                "shipment_from_area"=>$shipment->shipment_from_area,
                "shipment_to_area"=>$shipment->shipment_to_area,
                "is_requested"=>$is_requested,
                "is_rated"=>$is_rated,
                "status"=>$status,
                "tracking_status"=>$tracking_status
            );
            $data['gst_charges'] = 0;
            $data['sub_quotation'] = '0';
            $data['quotation'] = '0';
            $data['pay_quotation'] = '0';
            $data['total_reward'] = 0;
            $data['total_items'] = count($shipment->products);
            $data['total_weight'] = 0;
            $quotation = 0;
            $total_weight = 0;
            $data['products'] = array();
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
                    
                    //$p_quotation = (string)round($product->product_total + $product->traveller_reward + $product->tb_charges ,2); //OLD
                    $p_quotation = (string)round($product->traveller_reward + $product->tb_charges ,2); //OLD
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
                        "product_weight_unit"=>$product->product->product_weight_unit,
                        "product_size"=>$product->product->product_size,
                        "product_length"=>$product->product->product_length,
                        "product_width"=>$product->product->product_width,
                        "product_height"=>$product->product->product_height,
                        "product_price"=>$product->product->product_price,
                        "product_link"=>($product->product->product_link != '')?$product->product->product_link:'',
                        "product_qty"=>$product->product_qty,
                        "tb_charges"=>$currency.' '.(string)round($product->tb_charges,2),
                        "traveller_reward"=>$currency.' '.(string)round($product->traveller_reward,2),
                        "reward_currency"=>$product->reward_currency,
                        "quotation"=>$currency.' '.$p_quotation,
                        "notes"=>$notes,
                        //"shipment_from_name"=>$shipment->from->city,
                        //"shipment_to_name"=>$shipment->to->city
                        "shipment_from_name"=>$shipment->shipment_from.$shipment->country_from_iso,
                        "shipment_to_name"=>$shipment->shipment_to.$shipment->country_to_iso
                    );
                    //$quotation += $product->product_total+$product->tb_charges+$product->traveller_reward;
                    //$quotation += $product->tb_charges+$product->traveller_reward;
                    $weight_unit = $product->product->product_weight_unit;
                    $weight = $product->product->product_weight;
                    if($weight_unit == 'g'){
                        $weight = $weight/1000;
                    }else if($weight_unit == 'mg'){
                        $weight = ($weight/1000)/1000;
                    }
                    
                    $total_weight += $weight;
                    
                    $data['products'][] = $arr;
               }
            }
            //$data['quotation'] = (string)round($quotation,2);
            $data['gst_charges'] = $currency.' '.(string)round($shipment->gst_charges,2);
            $data['sub_quotation'] = $currency.' '.(string)round($shipment->sub_total_quotation,2);
            $data['quotation'] = $currency.' '.(string)round($shipment->total_quotation,2);
            $data['pay_quotation'] = (string)round($shipment->total_quotation,2);
            $data['total_reward'] = $currency.' '.(string)round($shipment->total_reward,2);
            
            $data['total_weight'] = (string)round($total_weight,2).' kg';
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipments Id','error'=>['Invalid Shipments Id'],'data'=>[]], 200);
        }
    }

    public function get_shipments(Request $request){
        $user_id = (($request->input('user_id') != ''))?$request->input('user_id'):'0';
        $from = $request->input('from');
        $to = $request->input('to');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $currency = '₹';

        $query = ShipmentModel::query();
        if(!empty($start_date)){
            $query->whereDate('expected_delivery_date','>=',$start_date);
        }
        if(!empty($end_date)){
            $query->whereDate('expected_delivery_date','<=',$end_date);
        }
        if(!empty($from)){
            $query->where('shipment_from_full_txt',$from);
        }
        if(!empty($to)){
            $query->where('shipment_to_full_txt',$to);
        }
        if((empty($start_date)) && (empty($end_date))){
            $query->whereDate('expected_delivery_date','>=',date('Y-m-d'));
        }
        
        $query->where('is_created','1');
        if($user_id != 0){
            $shipments = $query->where('status','1')->where('created_by','!=',$user_id)->has('user')->orderBy('id','desc')->get();
        }else{
            $shipments = $query->where('status','1')->has('user')->orderBy('id','desc')->get();
        }
        
        if(count($shipments) > 0){
            $data = array();

            foreach($shipments as $shipment){
                /*
                $reward = 0;
                if(count($shipment->products) > 0){
                    foreach($shipment->products as $product){
                        $reward += $product->traveller_reward;
                    }
                }*/
                $product_name = '';
                $product_url = '';
                if(count($shipment->products) > 0){
                    $product_name = $shipment->products[0]->product->product_name;
                    $product_url = $shipment->products[0]->product->product_image_url;
                    $currency = ($shipment->products[0]->reward_currency == 'rupees')?'₹':'$';
                }
                
                if($user_id != 0){
                    $deal = DealsModel::where(function ($query) use ($user_id) {
                            $query->where('request_by', $user_id)
                                ->orWhere('request_to', $user_id);
                            })->where('shipment_id',$shipment->id)->where('request_status','!=','2')->first();
                    if(empty($deal)){
                        //Logic We dont want to show user those shipments which is related to its current deals.
                        
                        $trips = TripsModel::where('user_id',$user_id)->where('is_created','1')->where('trip_status','4')->where('trip_from',$shipment->shipment_from)->where('trip_to',$shipment->shipment_to)->whereDate('arrival_datetime','>=',date('Y-m-d'))->has('user')->first();
                        
                        if(!empty($trips)){
                            continue;
                        }else{
                            
                            $data[] = array(
                                "shipment_id"=>$shipment->id,
                                //"from"=>$shipment->from->city,
                                //"to"=>$shipment->to->city,
                                "from"=>$shipment->shipment_from,
                                "to"=>$shipment->shipment_to,
                                "title"=>$product_name,
                                "reward"=>$currency.''.$shipment->total_reward,
                                "image"=>$product_url,
                                "date"=>date('d M Y',strtotime($shipment->expected_delivery_date))
                            );
                        }
                    }
                }else{
                    $data[] = array(
                        "shipment_id"=>$shipment->id,
                        //"from"=>$shipment->from->city,
                        //"to"=>$shipment->to->city,
                        "from"=>$shipment->shipment_from,
                        "to"=>$shipment->shipment_to,
                        "title"=>$product_name,
                        "reward"=>$currency.''.$shipment->total_reward,
                        "image"=>$product_url,
                        "date"=>date('d M Y',strtotime($shipment->expected_delivery_date))
                    );
                }
            }
            
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Shipments Found','error'=>['No Shipments Found'],'data'=>[]], 200);
        }

    }
    public function my_shipments(Request $request){
        $user_id = $request->input('user_id');
        $city_from = $request->input('city_from');
        $city_to = $request->input('city_to');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $type = $request->input('type');
        $req_status = $request->input('status');
        $is_tracking = ($request->input('is_tracking') == '1')?'1':'0';
        
        $is_query = 1;

        if($req_status == 'pending'){
            $req_status = '0';
        }else if($req_status == 'added'){
            $req_status = '1';
        }else if($req_status == 'booked'){
            $req_status = '2';
        }else if($req_status == 'cancelled'){
            $req_status = '3';
        }else if($req_status == 'deleted'){
            $req_status = '4';
        }else if($req_status == 'completed'){
            $req_status = '5';
        }else if($req_status == 'Cancelled By System'){
            $req_status = '6';
        }else if($req_status == 'accepted'){
            $req_status = 'accepted';
            $is_query = 0;
        }else if($req_status == 'requested'){
            $req_status = 'requested';
            $is_query = 0;
        }else{
            $req_status = 'all';
            $is_query = 0;
        }

        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $data = array();
            
            if($type != 'traveller'){
                
                $query = ShipmentModel::query();
                if(!empty($start_date)){
                    $query->whereDate('expected_delivery_date','>=',$start_date);
                }
                if(!empty($end_date)){
                    $query->whereDate('expected_delivery_date','<=',$end_date);
                }
                if(!empty($city_from)){
                    $query->where('shipment_from_full_txt',$city_from);
                }
                if(!empty($city_to)){
                    $query->where('shipment_to_full_txt',$city_to);
                }
                if(($type == 'shopper') || ($type == 'sender')){
                    $query->where('shipment_user_type',$type);
                }
                
                if(($is_query == '1')){
                    $query->where('status',$req_status);
                }
                
                //if((empty($start_date)) && (empty($end_date)) ){
                  //  $query->whereDate('expected_delivery_date','>=',date('Y-m-d'));
                //}

                $shipments = $query->where('created_by',$user_id)->where('is_created','1')->has('user')->orderBy('id','desc')->get();
                
                if(count($shipments) > 0){
                    foreach($shipments as $shipment){
                        
                        $product_name = '';
                        $product_url = '';
                        
                        $deal_id = '';
                        $other_user_id = '';
                        
                        if(count($shipment->products) > 0){
                            $product_name = $shipment->products[0]->product->product_name;
                            $product_url = $shipment->products[0]->product->product_image_url;
                        }
                        
                        $ship_deals = $shipment->deals;
                        $status = 'Added';
                        $tracking_status = ucwords(str_replace("_"," ",$shipment->tracking_status));
                        
                        if(count($ship_deals) > 0){
                            
                            foreach($ship_deals as $deal){
                                if($deal->request_status != 2){
                                    if($deal->request_by == $shipment->created_by){
                                        $status = 'Requests';
                                    }
                                }
                                if($deal->request_status == 1){
                                    $status = 'Accepted';
                                    $deal_id = (string)$deal->id;
                                    $other_user_id = (string)$deal->trip->user->id;
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

                        if(($req_status == 'accepted') || ($req_status == 'requested')){
                            
                            if($req_status == 'accepted'){
                                if($status == 'Accepted'){
                                    $data[] = array(
                                        "shipment_id"=>(string)$shipment->id,
                                        "order_id"=>(string)$shipment->shipment_uniqueid,
                                        //"from"=>$shipment->from->city,
                                        //"to"=>$shipment->to->city,
                                        "from"=>$shipment->shipment_from,
                                        "to"=>$shipment->shipment_to,
                                        "from_id"=>$shipment->shipment_from,
                                        "to_id"=>$shipment->shipment_to,
                                        "deal_id"=>$deal_id,
                                        "other_user_id"=>$other_user_id,
                                        "shipment_user_id"=>$shipment->created_by,
                                        "shipment_user_name"=>$shipment->user->name,
                                        "shipment_user_image"=>$shipment->user->profile_image_url,
                                        "shipment_product_name"=>$product_name,
                                        "shipment_product_image"=>$product_url,
                                        "shipment_user_rating"=>$shipment->user->profile_review,
                                        "status"=>$status,
                                        "tracking_status"=>$tracking_status,
                                        "shipment_type"=>$shipment->shipment_user_type,
                                        "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                                    );
                                }
                            }else if($req_status == 'requested'){
                                if($status == 'Requests'){
                                    $data[] = array(
                                        "shipment_id"=>(string)$shipment->id,
                                        "order_id"=>(string)$shipment->shipment_uniqueid,
                                        //"from"=>$shipment->from->city,
                                        //"to"=>$shipment->to->city,
                                        "from"=>$shipment->shipment_from,
                                        "to"=>$shipment->shipment_to,
                                        "from_id"=>$shipment->shipment_from,
                                        "to_id"=>$shipment->shipment_to,
                                        "deal_id"=>$deal_id,
                                        "other_user_id"=>$other_user_id,
                                        "shipment_user_id"=>$shipment->created_by,
                                        "shipment_user_name"=>$shipment->user->name,
                                        "shipment_user_image"=>$shipment->user->profile_image_url,
                                        "shipment_product_name"=>$product_name,
                                        "shipment_product_image"=>$product_url, 
                                        "shipment_user_rating"=>$shipment->user->profile_review,
                                        "status"=>$status,
                                        "tracking_status"=>$tracking_status,
                                        "shipment_type"=>$shipment->shipment_user_type,
                                        "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                                    );
                                }
                            }
                        }else{
                            if($req_status == '1'){
                                
                                if($status == 'Added'){
                                    $data[] = array(
                                        "shipment_id"=>(string)$shipment->id,
                                        "order_id"=>(string)$shipment->shipment_uniqueid,
                                        //"from"=>$shipment->from->city,
                                        //"to"=>$shipment->to->city,
                                        "from"=>$shipment->shipment_from,
                                        "to"=>$shipment->shipment_to,
                                        "from_id"=>$shipment->shipment_from,
                                        "to_id"=>$shipment->shipment_to,
                                        "deal_id"=>$deal_id,
                                        "other_user_id"=>$other_user_id,
                                        "shipment_user_id"=>$shipment->created_by,
                                        "shipment_user_name"=>$shipment->user->name,
                                        "shipment_user_image"=>$shipment->user->profile_image_url,
                                        "shipment_product_name"=>$product_name,
                                        "shipment_product_image"=>$product_url,
                                        "shipment_user_rating"=>$shipment->user->profile_review,
                                        "status"=>$status,
                                        "tracking_status"=>$tracking_status,
                                        "shipment_type"=>$shipment->shipment_user_type,
                                        "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                                    );
                                }
                            }else{
                                if($is_tracking == '1'){
                                    if(($status == 'Booked') || ($status == 'Completed')){
                                        $data[] = array(
                                            "shipment_id"=>(string)$shipment->id,
                                            "order_id"=>(string)$shipment->shipment_uniqueid,
                                            //"from"=>$shipment->from->city,
                                            //"to"=>$shipment->to->city,
                                            "from"=>$shipment->shipment_from,
                                            "to"=>$shipment->shipment_to,
                                            "from_id"=>$shipment->shipment_from,
                                            "to_id"=>$shipment->shipment_to,
                                            "deal_id"=>$deal_id,
                                            "other_user_id"=>$other_user_id,
                                            "shipment_user_id"=>$shipment->created_by,
                                            "shipment_user_name"=>$shipment->user->name,
                                            "shipment_user_image"=>$shipment->user->profile_image_url,
                                            "shipment_product_name"=>$product_name,
                                            "shipment_product_image"=>$product_url,
                                            "shipment_user_rating"=>$shipment->user->profile_review,
                                            "status"=>$status,
                                            "tracking_status"=>$tracking_status,
                                            "shipment_type"=>$shipment->shipment_user_type,
                                            "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                                        );
                                    }
                                }else{
                                    $data[] = array(
                                        "shipment_id"=>(string)$shipment->id,
                                        "order_id"=>(string)$shipment->shipment_uniqueid,
                                        //"from"=>$shipment->from->city,
                                        //"to"=>$shipment->to->city,
                                        "from"=>$shipment->shipment_from,
                                        "to"=>$shipment->shipment_to,
                                        "from_id"=>$shipment->shipment_from,
                                        "to_id"=>$shipment->shipment_to,
                                        "deal_id"=>$deal_id,
                                        "other_user_id"=>$other_user_id,
                                        "shipment_user_id"=>$shipment->created_by,
                                        "shipment_user_name"=>$shipment->user->name,
                                        "shipment_user_image"=>$shipment->user->profile_image_url,
                                        "shipment_product_name"=>$product_name,
                                        "shipment_product_image"=>$product_url,
                                        "shipment_user_rating"=>$shipment->user->profile_review,
                                        "status"=>$status,
                                        "tracking_status"=>$tracking_status,
                                        "shipment_type"=>$shipment->shipment_user_type,
                                        "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                                    );
                                }
                            }
                        }
                    }
                }
            }
            if(($type == 'all') || ($type == 'traveller') ){
                $query = DealsModel::query();
                if( (!empty($start_date)) || (!empty($end_date)) || (!empty($city_from)) || (!empty($city_to)) ){
                    $query->whereHas('shipment', function($q) use ($request,$is_query){

                        //$q->where('created_at', '>=', '2015-01-01 00:00:00');
                        if(!empty($request->start_date)){
                            $q->whereDate('expected_delivery_date','>=',$request->start_date);
                        }
                        if(!empty($request->end_date)){
                            $q->whereDate('expected_delivery_date','<=',$request->end_date);
                        }
                        if(!empty($request->city_from)){
                            $q->where('shipment_from',$request->city_from);
                        }
                        if(!empty($request->city_to)){
                            $q->where('shipment_to',$request->city_to);
                        }
                        if(($is_query == '1')){
                            $q->where('status',$req_status);
                        }
                        //if((empty($request->start_date)) && (empty($request->end_date)) ){
                          //  $q->whereDate('expected_delivery_date','>=',date('Y-m-d'));
                        //}

                    });
                }

                $deals = $query->where('request_by',$user_id)->where('request_by_type','traveller')->where('request_status','1')->orderBy('id','desc')->get();
                
                if(count($deals) > 0){
                    foreach($deals as $deal){
                        $status = 'Accepted';

                        if($deal->shipment->status == 0){
                            $status = 'Awaiting Approval';
                        }else if($deal->shipment->status == 2){
                            $status = 'Accepted'; //OLD Booked
                        }else if($deal->shipment->status == 3){
                            $status = 'Cancelled';
                        }else if($deal->shipment->status == 4){
                            $status = 'Deleted';
                        }else if($deal->shipment->status == 5){
                            $status = 'Completed';
                        }else if($deal->shipment->status == 6){
                            $status = 'Cancelled By System';
                        }
                        $tracking_status = ucwords(str_replace("_"," ",$deal->shipment->tracking_status));
                        
                        
                        $deal_id = (string)$deal->id;
                        $other_user_id = (string)$deal->shipment->user->id;
                        
                        $product_name = '';
                        $product_url = '';
                        if(count($deal->shipment->products) > 0){
                            $product_name = $deal->shipment->products[0]->product->product_name;
                            $product_url = $deal->shipment->products[0]->product->product_image_url;
                        }

                        if(($req_status == 'accepted') || ($req_status == 'requested')){
                            if($req_status == 'accepted'){
                                if($status == 'Accepted'){
                                    $data[] = array(
                                        "shipment_id"=>$deal->shipment_id,
                                        "order_id"=>(string)$deal->shipment->shipment_uniqueid,
                                        //"from"=>$deal->shipment->from->city,
                                        //"to"=>$deal->shipment->to->city,
                                        "from"=>$deal->shipment->shipment_from,
                                        "to"=>$deal->shipment->shipment_to,
                                        "from_id"=>$deal->shipment->shipment_from,
                                        "to_id"=>$deal->shipment->shipment_to,
                                        "deal_id"=>$deal_id,
                                        "other_user_id"=>$other_user_id,
                                        "shipment_user_id"=>$deal->request_to,
                                        "shipment_user_name"=>$deal->request_to_user->name,
                                        "shipment_user_image"=>$deal->request_to_user->profile_image_url,
                                        "shipment_product_name"=>$product_name,
                                        "shipment_product_image"=>$product_url,
                                        "shipment_user_rating"=>$deal->shipment->user->profile_review,
                                        "status"=>$status,
                                        "tracking_status"=>$tracking_status,
                                        "shipment_type"=>"traveller",
                                        "expected_date"=>date('d M Y',strtotime($deal->shipment->expected_delivery_date)),
                                    );
                                }
                            }else if($req_status == 'requested'){
                                if($status == 'Requests'){
                                    $data[] = array(
                                        "shipment_id"=>$deal->shipment_id,
                                        "order_id"=>(string)$deal->shipment->shipment_uniqueid,
                                        //"from"=>$deal->shipment->from->city,
                                        //"to"=>$deal->shipment->to->city,
                                        "from"=>$deal->shipment->shipment_from,
                                        "to"=>$deal->shipment->shipment_to,
                                        "from_id"=>$deal->shipment->shipment_from,
                                        "to_id"=>$deal->shipment->shipment_to,
                                        "deal_id"=>$deal_id,
                                        "other_user_id"=>$other_user_id,
                                        "shipment_user_id"=>$deal->request_to,
                                        "shipment_user_name"=>$deal->request_to_user->name,
                                        "shipment_user_image"=>$deal->request_to_user->profile_image_url,
                                        "shipment_product_name"=>$product_name,
                                        "shipment_product_image"=>$product_url,
                                        "shipment_user_rating"=>$deal->shipment->user->profile_review,
                                        "status"=>$status,
                                        "tracking_status"=>$tracking_status,
                                        "shipment_type"=>"traveller",
                                        "expected_date"=>date('d M Y',strtotime($deal->shipment->expected_delivery_date)),
                                    );
                                }
                            }
                        }else{
                            $data[] = array(
                                    "shipment_id"=>$deal->shipment_id,
                                    "order_id"=>(string)$deal->shipment->shipment_uniqueid,
                                    //"from"=>$deal->shipment->from->city,
                                    //"to"=>$deal->shipment->to->city,
                                    "from"=>$deal->shipment->shipment_from,
                                    "to"=>$deal->shipment->shipment_to,
                                    "from_id"=>$deal->shipment->shipment_from,
                                    "to_id"=>$deal->shipment->shipment_to,
                                    "deal_id"=>$deal_id,
                                    "other_user_id"=>$other_user_id,
                                    "shipment_user_id"=>$deal->request_to,
                                    "shipment_user_name"=>$deal->request_to_user->name,
                                    "shipment_user_image"=>$deal->request_to_user->profile_image_url,
                                    "shipment_product_name"=>$product_name,
                                    "shipment_product_image"=>$product_url,
                                    "shipment_user_rating"=>$deal->shipment->user->profile_review,
                                    "status"=>$status,
                                    "tracking_status"=>$tracking_status,
                                    "shipment_type"=>"traveller",
                                    "expected_date"=>date('d M Y',strtotime($deal->shipment->expected_delivery_date)),
                                );
                        }
                    }
                }
            }

            $shipment_id = array_column($data, 'shipment_id');

            array_multisort($shipment_id, SORT_DESC, $data);

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);

        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }

    public function trending_shipments(Request $request){
        $user_id = (($request->input('user_id') != ''))?$request->input('user_id'):'0';
        if($user_id != 0){
            $shipments = ShipmentModel::has('products')->where('created_by','!=',$user_id)->where('status','1')->where('is_created','1')->withCount('deals')->whereDate('expected_delivery_date','>=',date('Y-m-d'))->orderBy('deals_count', 'desc')->orderBy('id', 'desc')->get();
        }else{
            $shipments = ShipmentModel::has('products')->where('status','1')->where('is_created','1')->withCount('deals')->whereDate('expected_delivery_date','>=',date('Y-m-d'))->orderBy('deals_count', 'desc')->orderBy('id', 'desc')->get();
        }
        $currency = '₹';
        if(count($shipments) > 0){
            $data = array();
            foreach($shipments as $shipment){
                $product_name = '';
                $product_url = '';
                if(count($shipment->products) > 0){
                    $product_name = $shipment->products[0]->product->product_name;
                    $product_url = $shipment->products[0]->product->product_image_url;
                    $currency = ($shipment->products[0]->reward_currency == 'rupees')?'₹':'$';
                }
            
                if($user_id != 0){
                    
                    $deal = DealsModel::where(function ($query) use ($user_id) {
                            $query->where('request_by', $user_id)
                                ->orWhere('request_to', $user_id);
                            })->where('shipment_id',$shipment->id)->first();
                    if(empty($deal)){
                        //Logic We dont want to show user those shipments which is related to its current deals.
                        
                        $trips = TripsModel::where('user_id',$user_id)->where('is_created','1')->where('trip_status','4')->where('trip_from',$shipment->shipment_from)->where('trip_to',$shipment->shipment_to)->whereDate('arrival_datetime','>=',date('Y-m-d'))->has('user')->first();
                        
                        if(!empty($trips)){
                            continue;
                        }else{
                            $data[] = array(
                                "shipment_id"=>(string)$shipment->id,
                                "count"=>$shipment->deals_count,
                                //"from"=>$shipment->from->city,
                                //"to"=>$shipment->to->city,
                                "from"=>$shipment->shipment_from,
                                "to"=>$shipment->shipment_to,
                                "from_id"=>$shipment->shipment_from,
                                "to_id"=>$shipment->shipment_to,
                                "shipment_user_id"=>$shipment->created_by,
                                "shipment_user_name"=>$shipment->user->name,
                                "shipment_user_image"=>$shipment->user->profile_image_url,
                                "shipment_product_name"=>$product_name,
                                "shipment_product_image"=>$product_url,
                                "shipment_user_rating"=>$shipment->user->profile_review,
                                "shipment_type"=>$shipment->shipment_user_type,
                                "traveller_reward"=>$currency.''.$shipment->total_reward,
                                "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                            );
                        }
                    }
                }else{
                    $data[] = array(
                        "shipment_id"=>(string)$shipment->id,
                        "count"=>$shipment->deals_count,
                        //"from"=>$shipment->from->city,
                        //"to"=>$shipment->to->city,
                        "from"=>$shipment->shipment_from,
                        "to"=>$shipment->shipment_to,
                        "from_id"=>$shipment->shipment_from,
                        "to_id"=>$shipment->shipment_to,
                        "shipment_user_id"=>$shipment->created_by,
                        "shipment_user_name"=>$shipment->user->name,
                        "shipment_user_image"=>$shipment->user->profile_image_url,
                        "shipment_product_name"=>$product_name,
                        "shipment_product_image"=>$product_url,
                        "shipment_user_rating"=>$shipment->user->profile_review,
                        "shipment_type"=>$shipment->shipment_user_type,
                        "traveller_reward"=>$currency.''.$shipment->total_reward,
                        "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                    );
                }
                
            }
            return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Shipment Found','error'=>['No Shipment Found'],'data'=>[]], 200);
        }
    }
    public function delivered_shipments(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $shipments = ShipmentModel::where('created_by',$user_id)->where('status','5')->where('is_created','1')->get();
            if(count($shipments) > 0){
                $data = array();
                foreach($shipments as $shipment){
                    
                    $product_name = '';
                    $product_url = '';
                    if(count($shipment->products) > 0){
                        $product_name = $shipment->products[0]->product->product_name;
                        $product_url = $shipment->products[0]->product->product_image_url;
                    }
                    
                    
                    $data[] = array(
                        "shipment_id"=>(string)$shipment->id,
                        "order_id"=>$shipment->shipment_uniqueid,
                        //"from"=>$shipment->from->city,
                        //"to"=>$shipment->to->city,
                        "from"=>$shipment->shipment_from,
                        "to"=>$shipment->shipment_to,
                        "from_id"=>$shipment->shipment_from,
                        "to_id"=>$shipment->shipment_to,
                        "title"=>$product_name,
                        "image"=>$product_url,
                        "shipment_user_id"=>$shipment->created_by,
                        "shipment_user_name"=>$shipment->user->name,
                        "shipment_user_image"=>$shipment->user->profile_image_url,
                        "shipment_user_rating"=>$shipment->user->profile_review,
                        "shipment_type"=>$shipment->shipment_user_type,
                        "total_quotation"=>$shipment->total_quotation,
                        "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                        "delivery_date"=>date('d M Y',strtotime($shipment->delivery_date)),
                        "order_placed"=>date('d M Y',strtotime($shipment->created_at)),
                    );
                }
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Shipment Found','error'=>['No Shipment Found'],'data'=>[]], 200);
            }
        
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    public function get_shipments_for_trip(Request $request){
        $user_id = $request->input('user_id');
        $user = Users_Model::where('id',$user_id)->first();
        if(!empty($user)){
            $trip_id = $request->input('trip_id');
            $trip = TripsModel::where('id',$trip_id)->first();
            if(!empty($trip)){
                $shipments = ShipmentModel::has('products')
                    ->where('status','1')
                    ->where('is_created','1')
                    ->where('created_by',$user_id)
                    ->where('shipment_from',$trip->trip_from)
                    ->where('shipment_to',$trip->trip_to)
                    ->whereDate('expected_delivery_date','>=',date('Y-m-d',strtotime($trip->arrival_datetime)))
                    ->whereDate('expected_delivery_date','>=',date('Y-m-d'))
                    ->orderBy('id', 'desc')->get();
                    
                if(count($shipments) > 0){
                    $data = array();
                    foreach($shipments as $shipment){
                        $deal_status = 0;
                        $ship_deals = $shipment->deals;
                        if(count($ship_deals) > 0){
                            foreach($ship_deals as $deal){
                                if(($deal->request_status == 1)){
                                    $deal_status = 1;
                                }else if(($deal->request_status == 2)){
                                    $curr = time();
                                    $other = strtotime($deal->created_at);
                                    $datediff = $curr - $other;

                                    $diff_days = round($datediff / (60 * 60 * 24));
                                    if($diff_days < 1){
                                        $deal_status = 1;
                                    }
                                }
                            }
                        }
                        
                        if($deal_status == 1){
                            continue;
                        }
                        
                        $product_name = '';
                        $product_url = '';
                        if(count($shipment->products) > 0){
                            $product_name = $shipment->products[0]->product->product_name;
                            $product_url = $shipment->products[0]->product->product_image_url;
                        }

                        $data[] = array(
                            "shipment_id"=>(string)$shipment->id,
                            //"from"=>$shipment->from->city,
                            //"to"=>$shipment->to->city,
                            "from"=>$shipment->shipment_from,
                            "to"=>$shipment->shipment_to,
                            "from_id"=>$shipment->shipment_from,
                            "to_id"=>$shipment->shipment_to,
                            "shipment_user_id"=>$shipment->created_by,
                            "shipment_user_name"=>$shipment->user->name,
                            "shipment_user_image"=>$shipment->user->profile_image_url,
                            "shipment_product_name"=>$product_name,
                            "shipment_product_image"=>$product_url,
                            "shipment_user_rating"=>$shipment->user->profile_review,
                            "shipment_type"=>$shipment->shipment_user_type,
                            "traveller_reward"=>(string)round($shipment->total_reward,2),
                            "expected_date"=>date('d M Y',strtotime($shipment->expected_delivery_date)),
                        );
                    }
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Shipment Found','error'=>['No Shipment Found'],'data'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip Id','error'=>['Invalid Trip Id'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid User Id','error'=>['Invalid User Id'],'data'=>[]], 200);
        }
    }
    public function get_requested_shipments(Request $request){
        $trip_id = $request->input('trip_id');

        $trip = TripsModel::where('id',$trip_id)->where('is_created','1')->has('user')->first();
        $data = array();
        $req_shipments = array();
        if(!empty($trip)){
            if(strtotime($trip->departure_datetime) >= strtotime(date('Y-m-d H:i'))){
                $deals = DealsModel::whereIn('request_status',['0','1'])->where('trip_id',$trip_id)->where('request_by',$trip->user_id)->has('shipment')->has('trip')->get();
                if(count($deals) > 0){
                    foreach($deals as $deal){
                        $product_name = '';
                        $product_img = '';
                        if(count($deal->shipment->products) > 0){
                            $product_name = $deal->shipment->products[0]->product->product_name;
                            $product_img = $deal->shipment->products[0]->product->product_image_url;
                        }
    
                        $data[] = array(
                            "deal_id"=>$deal->id,
                            "shipment_id"=>$deal->shipment_id,
                            "trip_id"=>$trip_id,
                            //"from"=>$deal->shipment->from->city,
                            //"to"=>$deal->shipment->to->city,
                            "from"=>$deal->shipment->shipment_from,
                            "to"=>$deal->shipment->shipment_to,
                            "title"=>$product_name,
                            "image"=>$product_img,
                            "reward"=>$deal->shipment->total_reward,
                            "status"=>($deal->request_status == 0)?'Requested':(($deal->request_status == 1)?'Accepted':(($deal->request_status == 2)?'Cancelled':'')),
                            "is_requested"=>'1'
                        );
                        $req_shipments[] = $deal->shipment_id;
                        
                    }
                }
                
                $deals = DealsModel::where('trip_id',$trip_id)->where('request_to',$trip->user_id)->has('shipment')->has('trip')->get();
                if(count($deals) > 0){
                    foreach($deals as $deal){
                        $req_shipments[] = $deal->shipment_id;
                    }
                }
                
                $shipments = ShipmentModel::has('products')
                    ->where('status','1')
                    ->where('is_created','1')
                    ->where('created_by','!=',$trip->user_id)
                    ->where('shipment_from',$trip->trip_from)
                    ->where('shipment_to',$trip->trip_to)
                    ->whereNotIn('id', $req_shipments)
                    ->whereDate('expected_delivery_date','>=',date('Y-m-d',strtotime($trip->arrival_datetime)))
                    ->whereDate('expected_delivery_date','>=',date('Y-m-d'))
                    ->orderBy('id', 'desc')->get();
                
                //var_dump($shipments);die;    
                if(count($shipments) > 0){
                    foreach($shipments as $shipment){
                        $product_name = '';
                        $product_img = '';
                        if(count($shipment->products) > 0){
                            $product_name = $shipment->products[0]->product->product_name;
                            $product_img = $shipment->products[0]->product->product_image_url;
                        }
                        
                        $deal_status = 0;
                        $ship_deals = $shipment->deals;
                        
                        if(count($ship_deals) > 0){
                            foreach($ship_deals as $deal){
                                
                                if(($deal->request_status == 1)){
                                    $deal_status = 1;
                                }else if(($deal->request_status == 2)){
                                    
                                    // If the deal is between current user and shipment user
                                    if($deal->trip->user->id == $trip->user_id){
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
                        
                        //Logic We dont want to show user those shipments which is related to its current deals.
                        
                        $trips = TripsModel::where('user_id',$trip->user_id)->where('is_created','1')->where('trip_status','4')->where('trip_from',$shipment->shipment_from)->where('trip_to',$shipment->shipment_to)->whereDate('arrival_datetime','>=',date('Y-m-d'))->has('user')->first();
                        
                        if(!empty($trips)){
                            continue;
                        }else{
                            $data[] = array(
                                "deal_id"=>0,
                                "shipment_id"=>$shipment->id,
                                "trip_id"=>$trip_id,
                                //"from"=>$shipment->from->city,
                                //"to"=>$shipment->to->city,
                                "from"=>$shipment->shipment_from,
                                "to"=>$shipment->shipment_to,
                                "title"=>$product_name,
                                "image"=>$product_img,
                                "reward"=>$shipment->total_reward,
                                "status"=>'',
                                "is_requested"=>'0'
                            );
                        }
                    }
                }
                if(count($data) > 0){
                    return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[],'data'=>$data], 200);
                }else{
                    return response()->json(['status'=>false,'status_code'=>'401','message'=>'No Shipments Found','error'=>['No Shipments Found'],'data'=>[]], 200);
                }
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'You have already departed','error'=>['You have already departed'],'data'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip Id','error'=>['Invalid Trip Id'],'data'=>[]], 200);
        }
    }
    public function delete_cancel_shipment(Request $request){
        $shipment_id = $request->input('shipment_id');
        $type = ($request->input('type') == 'delete')?'delete':'cancel';
        $cancel_reason = ($request->input('cancel_reason') != '')?$request->input('cancel_reason'):'';
        
        $shipment = ShipmentModel::where('id',$shipment_id)->where('is_created','1')->has('user')->first();
        if(!empty($shipment)){
            NotificationModel::where('notification_type','matching_shipment')->where('reference_id',$shipment_id)->update(['type'=>'no_action']);
            NotificationModel::where('type','tracking')->where('reference_id',$shipment->shipment_uniqueid)->update(['type'=>'reject_deal']);
            NotificationModel::where('type','deal')->where('reference_id',$shipment->shipment_uniqueid)->update(['type'=>'reject_deal']);
            
            if($type == 'delete'){
                $shipment->status = '4';
                $shipment->tracking_status = 'placed';
                $shipment->save();
                
                $ship_deals = $shipment->deals;
                if(count($ship_deals) > 0){
                    foreach($ship_deals as $deal){
                        if($deal->request_status != 2){
                            DealsModel::where('id',$deal->id)->update(['request_status'=>'2']);
                            
                            NotificationModel::where('type','deal')->where('reference_id',$deal->id)->update(['type'=>'reject_deal']);
                            
                            $notification = new NotificationController();
                            $notification->changeDealStatus($deal,'2');
                        }
                    }
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Shipment Deleted Successfully','error'=>[]], 200);
            }else{
                $shipment->status = '3';
                $shipment->tracking_status = 'placed';
                $shipment->cancel_reason = $cancel_reason;
                $shipment->save();
                
                $ship_deals = $shipment->deals;
                if(count($ship_deals) > 0){
                    foreach($ship_deals as $deal){
                        if($deal->request_status != 2){
                            $arr = array(
                                "request_status"=>'2',
                                "cancel_by"=>$shipment->shipment_user_type,
                                "cancel_by_id"=>$shipment->user->id,
                            );
                            DealsModel::where('id',$deal->id)->update($arr);
                            
                            if($deal->request_status == 1){
                                TripsModel::where('id',$deal->trip->id)->update(['trip_status'=>0]);
                                NotificationModel::where('type','deal')->where('reference_id',$deal->id)->update(['type'=>'reject_deal']);
                                
                                $notification = new NotificationController();
                                $notification->cancelShipment($deal);
                            }else{
                                // Reject Other Deals Notification
                                $notification = new NotificationController();
                                $notification->changeDealStatus($deal,'2');
                            }
                        }
                    }
                }
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Shipment Cancelled Successfully','error'=>[]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipments Id','error'=>['Invalid Shipments Id']], 200);
        }
    }
    public function delete_shipment_product(Request $request){
        $shipment_product_id = $request->input('shipment_product_id');

        $shipment_product = ShipmentProductModel::where('id',$shipment_product_id)->first();
        if(!empty($shipment_product)){
            $shipment_id = $shipment_product->shipment_id;
            if($shipment_product->product->author->is_admin == 0){
                $img = $shipment_product->product->product_image;
                if(!empty($img)){
                    $img = public_path('/').'/'.$img;
                    if(file_exists($img)){
                        unlink($img);
                    }
                }

                ProductsModel::where('id',$shipment_product->product->id)->delete();
            }
            ShipmentProductModel::where('id',$shipment_product_id)->delete();

            $count = ShipmentProductModel::where('shipment_id',$shipment_id)->count();

            if(!($count > 0)){
                ShipmentModel::where('id',$shipment_id)->delete();
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Shipment Deleted Successfully','error'=>[]], 200);
            }

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Shipment Product Deleted Successfully','error'=>[]], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipment Product Id','error'=>['Invalid Shipment Product Id']], 200);
        }
    }
    public function edit_shipment_product(Request $request){
        $shipment_product_id = $request->input('shipment_product_id');

        $shipment_product = ShipmentProductModel::has('shipment')->has('product')->where('id',$shipment_product_id)->first();
        if(!empty($shipment_product)){
            $notes = array();
            if($shipment_product->notes != ''){
                $note = json_decode($shipment_product->notes);
                if(is_array($note)){
                    foreach($note as $not){
                        $notes[] = array('note'=>$not);
                    }
                }
            }

            $data = array(
                "shipment_id"=>$shipment_product->shipment->id,
                "shipment_from"=>$shipment_product->shipment->shipment_from,
                //"shipment_from_name"=>$shipment_product->shipment->shipment_from,
                "shipment_from_name"=>$shipment_product->shipment->shipment_from,
                "shipment_to"=>$shipment_product->shipment->shipment_to,
                //"shipment_to_name"=>$shipment_product->shipment->shipment_to,
                "shipment_to_name"=>$shipment_product->shipment->shipment_to,
                "shipment_from_full_txt"=>$shipment_product->shipment->shipment_from_full_txt,
                "shipment_to_full_txt"=>$shipment_product->shipment->shipment_to_full_txt,
                "shipment_from_area"=>$shipment_product->shipment->shipment_from_area,
                "shipment_to_area"=>$shipment_product->shipment->shipment_to_area,
                "expected_delivery_date"=>date('Y-m-d',strtotime($shipment_product->shipment->expected_delivery_date)),
                "person_name_pickup"=>$shipment_product->shipment->person_name_pickup,
                "person_addr_pickup"=>$shipment_product->shipment->person_addr_pickup,
                "person_pincode_pickup"=>$shipment_product->shipment->person_pincode_pickup,
                "person_mobile_pickup"=>$shipment_product->shipment->person_mobile_pickup,
                "person_mobile_cc_pickup"=>$shipment_product->shipment->person_mobile_cc_pickup,
                "person_name_delivery"=>$shipment_product->shipment->person_name_delivery,
                "person_addr_delivery"=>$shipment_product->shipment->person_addr_delivery,
                "person_pincode_delivery"=>$shipment_product->shipment->person_pincode_delivery,
                "person_mobile_delivery"=>$shipment_product->shipment->person_mobile_delivery,
                "person_mobile_cc_delivery"=>$shipment_product->shipment->person_mobile_cc_delivery,
                "shipment_user_type"=>$shipment_product->shipment->shipment_user_type,
                "sp_id"=>$shipment_product->product->id,
                "product_name"=>$shipment_product->product->product_name,
                "product_category_id"=>$shipment_product->product->product_category,
                "product_category_name"=>$shipment_product->product->category->category_name,
                "product_image"=>$shipment_product->product->product_image_url,
                "product_unique_id"=>$shipment_product->product->product_unique_id,
                "product_qty"=>$shipment_product->product_qty,
                "product_weight"=>$shipment_product->product->product_weight,
                "product_weight_unit"=>$shipment_product->product->product_weight_unit,
                "product_size"=>$shipment_product->product->product_size,
                "product_length"=>$shipment_product->product->product_length,
                "product_width"=>$shipment_product->product->product_width,
                "product_height"=>$shipment_product->product->product_height,
                "product_price"=>$shipment_product->product->product_price,
                "product_link"=>($shipment_product->product->product_link != '')?$shipment_product->product->product_link:'',
                "traveller_reward"=>$shipment_product->shipment->total_reward,
                "reward_currency"=>$shipment_product->reward_currency,
                "notes"=>$notes,
            );

            return response()->json(['status'=>true,'status_code'=>'200','message'=>'Success','error'=>[],'data'=>$data], 200);
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipment Product Id','error'=>['Invalid Shipment Product Id'],'data'=>(object)[]], 200);
        }
    }
    public function update_shipment_product(Request $request){
        $shipment_product_id = $request->input('shipment_product_id');

        $shipment_product = ShipmentProductModel::has('shipment')->has('product')->where('id',$shipment_product_id)->first();
        if(!empty($shipment_product)){
            try{
                $notes = $request->input('notes');
                if(!empty($notes)){
                    $notes = json_decode($request->input('notes'));
                    if((is_array($notes))){
                        $notes = json_encode($notes);
                        ShipmentProductModel::where('id',$shipment_product_id)->update(['notes'=>$notes]);
                    }
                }
                
                $person_addr_pickup_lat = ($request->input('person_addr_pickup_lat') != '')?$request->input('person_addr_pickup_lat'):'';
                $person_addr_pickup_lng = ($request->input('person_addr_pickup_lng') != '')?$request->input('person_addr_pickup_lng'):'';
                $person_addr_delivery_lat = ($request->input('person_addr_delivery_lat') != '')?$request->input('person_addr_delivery_lat'):'';
                $person_addr_delivery_lng = ($request->input('person_addr_delivery_lng') != '')?$request->input('person_addr_delivery_lng'):'';
                
                $settings = SettingsModel::where('id','1')->first();
                
                $distance_reward = 0;
                if((!empty($person_addr_pickup_lat)) && (!empty($person_addr_pickup_lng)) && (!empty($person_addr_delivery_lat)) && (!empty($person_addr_delivery_lng)) ){
                    $distance = $this->distance($person_addr_pickup_lat,$person_addr_pickup_lng,$person_addr_delivery_lat,$person_addr_delivery_lng,'K');
                    $distance = round($distance,2);
                    $rangee = json_decode($settings->price_range);
                    if(!empty($rangee)){
                        foreach($rangee as $range){
                            if(($range->from_distance <= $distance) && ($range->to_distance >= $distance) ){
                                $distance_reward = $range->price;
                            }
                        }
                    }
                }

                $person_name_delivery = $request->input('person_name_delivery');
                $person_addr_delivery = $request->input('person_addr_delivery');
                $person_pincode_delivery = $request->input('person_pincode_delivery');
                $person_mobile_delivery = $request->input('person_mobile_delivery');
                $person_mobile_cc_delivery = $request->input('person_mobile_cc_delivery');

                $expected_delivery_date = date('Y-m-d',strtotime($request->input('expected_delivery_date')));
                
                $shipment_from = $request->input('shipment_from');
                $shipment_to = $request->input('shipment_to');
                
                $shipment_from_area = ($request->input('shipment_from_area') != '')?$request->input('shipment_from_area'):'';
                $shipment_to_area = ($request->input('shipment_to_area') != '')?$request->input('shipment_to_area'):'';
    
                $traveler_reward_by_shopper = ($request->input('traveler_reward') != '')?$request->input('traveler_reward'):''; //18-11-21
        
                if($shipment_product->shipment->shipment_user_type == 'shopper'){
                    $data = array(
                        "person_name_delivery"=>$person_name_delivery,
                        "person_addr_delivery"=>$person_addr_delivery,
                        "person_pincode_delivery"=>$person_pincode_delivery,
                        "person_mobile_delivery"=>$person_mobile_delivery,
                        "person_mobile_cc_delivery"=>$person_mobile_cc_delivery,
                        "expected_delivery_date"=>$expected_delivery_date,
                        "shipment_from"=>$shipment_from,
                        "shipment_to"=>$shipment_to,
                        "shipment_from_full_txt"=>$request->input('shipment_from_full_txt'),
                        "shipment_to_full_txt"=>$request->input('shipment_to_full_txt'),
                        "shipment_from_area"=>$shipment_from_area,
                        "shipment_to_area"=>$shipment_to_area,
                    );
                    ShipmentModel::where('id',$shipment_product->shipment_id)->update($data);

                }else{
                    $person_name_pickup = $request->input('person_name_pickup');
                    $person_addr_pickup = $request->input('person_addr_pickup');
                    $person_pincode_pickup = $request->input('person_pincode_pickup');
                    $person_mobile_pickup = $request->input('person_mobile_pickup');
                    $person_mobile_cc_pickup = $request->input('person_mobile_cc_pickup');

                    
                    $townbuddy_charges_percent = ($settings->townbuddy_charges_percent != '')?$settings->townbuddy_charges_percent:'0';
                    $tb_shipment_price_percent = ($settings->tb_shipment_price_percent != '')?$settings->tb_shipment_price_percent:'0';
                    $tb_volume_charges = ($settings->tb_volume_charges != '')?$settings->tb_volume_charges:'0';
                    $tb_weight_charges = ($settings->tb_weight_charges != '')?$settings->tb_weight_charges:'0';
                    $gst_charges = ($settings->gst_on_quotation != '')?$settings->gst_on_quotation:'0';
                    //$tb_size_charges_s = ($settings->tb_size_charges_s != '')?$settings->tb_size_charges_s:'0';
                    //$tb_size_charges_m = ($settings->tb_size_charges_m != '')?$settings->tb_size_charges_m:'0';
                    //$tb_size_charges_l = ($settings->tb_size_charges_l != '')?$settings->tb_size_charges_l:'0';
                    //$tb_size_charges_h = ($settings->tb_size_charges_h != '')?$settings->tb_size_charges_h:'0';                

                    $data = array(
                        "person_name_delivery"=>$person_name_delivery,
                        "person_addr_delivery"=>$person_addr_delivery,
                        "person_addr_delivery_lat"=>$person_addr_delivery_lat,
                        "person_addr_delivery_lng"=>$person_addr_delivery_lng,
                        "person_pincode_delivery"=>$person_pincode_delivery,
                        "person_mobile_delivery"=>$person_mobile_delivery,
                        "person_mobile_cc_delivery"=>$person_mobile_cc_delivery,
                        "person_name_pickup"=>$person_name_pickup,
                        "person_addr_pickup"=>$person_addr_pickup,
                        "person_addr_pickup_lat"=>$person_addr_pickup_lat,
                        "person_addr_pickup_lng"=>$person_addr_pickup_lng,
                        "person_pincode_pickup"=>$person_pincode_pickup,
                        "person_mobile_pickup"=>$person_mobile_pickup,
                        "person_mobile_cc_pickup"=>$person_mobile_cc_pickup,
                        "expected_delivery_date"=>$expected_delivery_date,
                        "shipment_from"=>$shipment_from,
                        "shipment_to"=>$shipment_to,
                        "shipment_from_full_txt"=>$request->input('shipment_from_full_txt'),
                        "shipment_to_full_txt"=>$request->input('shipment_to_full_txt'),
                        "shipment_from_area"=>$shipment_from_area,
                        "shipment_to_area"=>$shipment_to_area,
                    );
                    ShipmentModel::where('id',$shipment_product->shipment_id)->update($data);
                    
                    $prod_category = $request->input('product_category');
                    if(empty($prod_category)){
                        $category = CategoryModel::first();
                        $prod_category = $category->id;
                    }
                    
                    $data = array(
                        "product_name"=>($request->input('product_name') != '')?$request->input('product_name'):'',
                        "product_category"=>$prod_category,
                        "product_unique_id"=>($request->input('product_unique_id') != '')?$request->input('product_unique_id'):'',
                        "product_stock_qty"=>($request->input('product_quantity') != '')?$request->input('product_quantity'):'',
                        "product_weight"=>($request->input('product_weight') != '')?$request->input('product_weight'):'',
                        "product_weight_unit"=>($request->input('product_weight_unit') != '')?$request->input('product_weight_unit'):'g',
                        "product_size"=>($request->input('product_size') != '')?$request->input('product_size'):'',
                        "product_length"=>($request->input('product_length') != '')?$request->input('product_length'):'',
                        "product_width"=>($request->input('product_width') != '')?$request->input('product_width'):'',
                        "product_height"=>($request->input('product_height') != '')?$request->input('product_height'):'',
                        "product_price"=>($request->input('product_value') != '')?$request->input('product_value'):'',
                        "product_mrp"=>($request->input('product_value') != '')?$request->input('product_value'):'',
                        "product_link"=>($request->input('product_link') != '')?$request->input('product_link'):'',
                        "product_status"=>1,
                    );
                    
                    
                    if($request->file('product_image')){
                        $image = $request->file('product_image');
                        if($image->isValid()){
                            $old_img = $shipment_product->product->product_image;
                            if(!empty($old_img)){
                                if(file_exists(public_path('/').'/'.$old_img)){
                                    unlink(public_path('/').'/'.$old_img);
                                }
                            }

                            $extension = $image->getClientOriginalExtension();
                            $fileName = rand(100,999999).time().'.'.$extension;
                            $image_path = public_path('assets/products/');
                            $request->product_image->move($image_path, $fileName);
                            $data['product_image'] = 'assets/products/'.$fileName;
                        }
                    }

                    $prod_id = $shipment_product->product_id;
                    ProductsModel::where('id',$prod_id)->update($data);

                    //Calculate Traveller Reward
                    $reward_prices = array();
                    //$reward_prices[]['val'] = (($data['product_price']*$data['product_stock_qty'])*$tb_shipment_price_percent)/100;
                    $reward_prices[]['val'] = (($data['product_length']*$data['product_width']*$data['product_height'])*$tb_volume_charges)*$data['product_stock_qty'];
                    
                    if($data['product_weight_unit'] == 'kg'){
                        $reward_prices[]['val'] = ($data['product_weight']*($tb_weight_charges*1000))*$data['product_stock_qty'];
                    }else if($data['product_weight_unit'] == 'mg'){
                        $reward_prices[]['val'] = ($data['product_weight']*($tb_weight_charges/1000))*$data['product_stock_qty'];
                    }else{
                        //g
                        $reward_prices[]['val'] = ($data['product_weight']*$tb_weight_charges)*$data['product_stock_qty'];
                    }
                    
                    
                    if($data['product_size'] == 's'){
                        //$reward_prices[]['val'] = $tb_size_charges_s*$data['product_stock_qty'];
                    }
                    if($data['product_size'] == 'm'){
                        //$reward_prices[]['val'] = $tb_size_charges_m*$data['product_stock_qty'];
                    }
                    if($data['product_size'] == 'l'){
                        //$reward_prices[]['val'] = $tb_size_charges_l*$data['product_stock_qty'];
                    }
                    if($data['product_size'] == 'h'){
                        //$reward_prices[]['val'] = $tb_size_charges_h*$data['product_stock_qty'];
                    }
                    
                    $prod_category = CategoryModel::where('id',$data['product_category'])->first();
                    if(!empty($prod_category)){
                        $reward_prices[]['val'] = $prod_category->category_price * $data['product_stock_qty'];
                    }else{
                        $reward_prices[]['val'] = 0;
                    }
                    
                    if($distance_reward != 0){
                        $max_traveller_reward = $distance_reward;
                    }else{
                        $max_traveller_reward = max(array_column($reward_prices, 'val'));
                    }
                    
                    $reward_currency = ($request->input('reward_currency') != '')?$request->input('reward_currency'):'rupees';
                    
                    //$tb_charges = (($data['product_price']*$data['product_stock_qty'])*$townbuddy_charges_percent)/100; //OLD
                    
                    if(!empty($traveler_reward_by_shopper)){
                        $tb_charges = ($traveler_reward_by_shopper * $townbuddy_charges_percent)/100;
                        $max_traveller_reward = $traveler_reward_by_shopper;
                    }else{
                        $tb_charges = ($max_traveller_reward * $townbuddy_charges_percent)/100;
                    }
                    
                    $product_total = $data['product_price']*$data['product_stock_qty'];

                    ShipmentProductModel::where('id',$shipment_product_id)->update(['product_qty'=>$data['product_stock_qty'],'product_total'=>$product_total,'tb_charges'=>$tb_charges,'traveller_reward'=>$max_traveller_reward,'reward_currency'=>$reward_currency]);
                }
                
                $shipment = ShipmentModel::where('id',$shipment_product->shipment_id)->first();
                if($shipment->is_created == 1){
                    $notification = new NotificationController();
                    $notification->matchingShipmentOnEdit($shipment);
                }
                //Calculate GST
                $shipment_gst_charge = ($shipment->sub_total_quotation * $gst_charges) / 100;
                $shipment->gst_charges = $shipment_gst_charge;
                $shipment->save();

                return response()->json(['status'=>true,'status_code'=>'200','message'=>'Shipment Updated Successfully'.$reward_currency,'error'=>[]], 200);
            } catch (\Exception $e) {

                return response()->json(['status'=>false,'status_code'=>'401','message'=>$e->getMessage(),'error'=>[$e->getMessage()]], 200);
            }
        }else{
            return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipment Product Id','error'=>['Invalid Shipment Product Id']], 200);
        }
    }
    public function change_created_status(Request $request){
        $type = $request->input('type');
        $id = $request->input('id');
        
        if($type == 'shipment'){
            $shipment = ShipmentModel::where('id',$id)->first();
            if(!empty($shipment)){
                $shipment->is_created = '1';
                $shipment->save();
                
                $notification = new NotificationController();
                $notification->matchingShipment($shipment);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
                
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Shipment Id','error'=>['Invalid Shipment Id']], 200);
            }
        }else{
            $trip = TripsModel::where('id',$id)->first();
            if(!empty($trip)){
                $trip->is_created = '1';
                $trip->save();
                
                $notification = new NotificationController();
                $notification->matchingTrip($trip);
                
                return response()->json(['status'=>true,'status_code'=>'200','message'=>'success','error'=>[]], 200);
                
            }else{
                return response()->json(['status'=>false,'status_code'=>'401','message'=>'Invalid Trip Id','error'=>['Invalid Trip Id']], 200);
            }
            
        }
    }
    

}
