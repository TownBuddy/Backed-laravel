<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ShipmentModel extends Model
{
    protected $table='shipments';
    protected $primaryKey='id';
    protected $appends = ['total_reward','total_quotation','sub_total_quotation','country_from_iso','country_to_iso'];
    protected $fillable=['shipment_uniqueid','shipment_from','shipment_to','shipment_from_full_txt','shipment_to_full_txt','shipment_from_area','shipment_to_area','expected_delivery_date','person_name_pickup','person_addr_pickup','person_addr_pickup_lat','person_addr_pickup_lng','person_pincode_pickup','person_mobile_pickup','person_mobile_cc_pickup','person_name_delivery','person_addr_delivery','person_addr_delivery_lat','person_addr_delivery_lng','person_pincode_delivery','person_mobile_delivery','person_mobile_cc_delivery','status','cancel_reason','tracking_status','pickup_otp','delivery_otp','created_by','shipment_user_type','delivery_date','gst_charges','refund','shipment_return','shipment_return_by_id','return_otp','is_created'];

    public function products(){
        return $this->hasMany(ShipmentProductModel::class,'shipment_id','id');
    }
    public function from(){
        return $this->belongsTo(CityModel::class,'shipment_from','id');
    }
    public function to(){
        return $this->belongsTo(CityModel::class,'shipment_to','id');
    }
    public function user(){
        return $this->belongsTo(Users_Model::class,'created_by','id');
    }
    public function deals(){
        return $this->hasMany(DealsModel::class,'shipment_id','id');
    }
    public function payments(){
        return $this->hasMany(PaymentModel::class,'shipment_id','id');
    }
    
    public function getCountryFromIsoAttribute()
    {
        $city = explode(',',$this->shipment_from_full_txt);
        $last_index = count($city) - 1;
        $country = str_replace(' ', '', $city[$last_index]);
        $iso = CountryIsoModel::where('country_name',$country)->first();
        if(!empty($iso)){
            return ','.$iso->iso3;
        }
    }
    public function getCountryToIsoAttribute()
    {
        $city = explode(',',$this->shipment_to_full_txt);
        $last_index = count($city) - 1;
        $country = str_replace(' ', '', $city[$last_index]);
        $iso = CountryIsoModel::where('country_name',$country)->first();
        if(!empty($iso)){
            return ','.$iso->iso3;
        }
    }
    public function getTotalRewardAttribute()
    {
        $reward = $this->products->sum('traveller_reward');
        if($reward != ''){
            //return (string)round($reward,2);
            return (string)round($reward);
        }else{
            return 0;
        }
    }
    public function getSubTotalQuotationAttribute()
    {
        $tb_charges = $this->products->sum('tb_charges');
        //$product_price = $this->products->sum('product_total');
        //$quotation = $product_price + $this->total_reward + $tb_charges;
        $quotation = $this->total_reward + $tb_charges;
        if($quotation != ''){
            return $quotation;
        }else{
            return 0;
        }
    }
    public function getTotalQuotationAttribute()
    {
        $tb_charges = $this->products->sum('tb_charges');
        //$product_price = $this->products->sum('product_total');
        //$quotation = $product_price + $this->total_reward + $tb_charges;
        $quotation = $this->total_reward + $tb_charges + $this->gst_charges;
        if($quotation != ''){
            return $quotation;
        }else{
            return 0;
        }
    }
    

}
