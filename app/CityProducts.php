<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CityProducts extends Model
{
    protected $table='city_products';
    protected $primaryKey='id';
    protected $fillable=['city_id','product_id'];

    public function city(){
        return $this->belongsTo(CityModel::class,'city_id','id');
    }
    public function product(){
        return $this->belongsTo(ProductsModel::class,'product_id','id');
    }

}
