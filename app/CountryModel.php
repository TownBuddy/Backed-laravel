<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryModel extends Model
{
    protected $table='country';
    protected $primaryKey='id';
    protected $appends = ['flag_url'];
    protected $fillable=['country_name','country_flag','country_code','mobile_no_limit'];
    
    public function getFlagUrlAttribute()
    {
        if($this->country_flag != ''){
            return env('ASSET_URL').$this->country_flag;
        }else{
            return '';
        }
        
    }
}
