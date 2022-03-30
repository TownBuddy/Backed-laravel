<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductsModel extends Model
{
    protected $table='products';
    protected $primaryKey='id';
    protected $appends = ['product_image_url'];

    protected $fillable=['product_name','product_category','product_image','product_unique_id','product_stock_qty','product_weight','product_weight_unit','product_size','product_length','product_width','product_height','product_price','product_mrp','product_link','product_author','product_status'];

    public function locations(){
        return $this->hasMany(CityProducts::class,'product_id');
    }
    public function category(){
        return $this->belongsTo(CategoryModel::class,'product_category','id');
    }
    public function author(){
        return $this->belongsTo(Users_Model::class,'product_author','id');
    }
    public function getProductImageUrlAttribute()
    {
        if($this->product_image != ''){
            return env('ASSET_URL').$this->product_image;
        }else{
            return '';
        }
        
    }

}
