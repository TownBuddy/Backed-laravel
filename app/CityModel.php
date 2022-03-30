<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CityModel extends Model
{
    protected $table='city';
    protected $primaryKey='id';
    protected $appends = ['country_name'];
    protected $fillable=['city','country','state','status','geonameid'];

    public function products(){
        return $this->hasMany(ProductsModel::class,'city_id');
    }
    public function countryData(){
        return $this->belongsTo(CountryModel::class,'country');
    }
    public function getCountryNameAttribute()
    {
        if(isset($this->countryData)){
            return $this->countryData->country_name;
        }else{
            return '';
        }
        
    }
    
}
