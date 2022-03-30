<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TripsModel extends Model
{
    protected $table='trips';
    protected $primaryKey='id';
    protected $appends = ['ticket_image_url','country_from_iso','country_to_iso'];
    protected $fillable=['user_id','trip_from','trip_to','trip_from_area','trip_to_area','trip_from_full_txt','trip_to_full_txt','departure_datetime','arrival_datetime','transport_type','ticket_image','ticket_number','avail_luggage_weight','avail_luggage_weight_unit','ticket_first_name','ticket_last_name','current_address','current_pincode','destination_address','destination_pincode','reason','notes','category_wont_carry','trip_status','cancel_reason','is_created'];

    public function user(){
        return $this->belongsTo(Users_Model::class,'user_id','id');
    }
    public function from(){
        return $this->belongsTo(CityModel::class,'trip_from','id');
    }
    public function to(){
        return $this->belongsTo(CityModel::class,'trip_to','id');
    }
    public function deal(){
        return $this->hasOne(DealsModel::class,'trip_id','id');
    }
    public function deals(){
        return $this->hasMany(DealsModel::class,'trip_id','id');
    }
    public function getTicketImageUrlAttribute()
    {
        if($this->ticket_image != ''){
            return env('ASSET_URL').$this->ticket_image;
        }else{
            return '';
        }
        
    }
    public function getCountryFromIsoAttribute()
    {
        $city = explode(',',$this->trip_from_full_txt);
        $last_index = count($city) - 1;
        $country = str_replace(' ', '', $city[$last_index]);
        $iso = CountryIsoModel::where('country_name',$country)->first();
        if(!empty($iso)){
            return ','.$iso->iso3;
        }
    }
    public function getCountryToIsoAttribute()
    {
        $city = explode(',',$this->trip_to_full_txt);
        $last_index = count($city) - 1;
        $country = str_replace(' ', '', $city[$last_index]);
        $iso = CountryIsoModel::where('country_name',$country)->first();
        if(!empty($iso)){
            return ','.$iso->iso3;
        }
    }
}
