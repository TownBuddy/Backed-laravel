<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table='product_category';
    protected $primaryKey='id';
    protected $fillable=['category_name','category_image','category_price'];

    protected $appends = ['category_image_url'];

    public function getCategoryImageUrlAttribute()
    {
        if($this->category_image != ''){
            return env('ASSET_URL').$this->category_image;
        }else{
            return '';
        }
        
    }
}
