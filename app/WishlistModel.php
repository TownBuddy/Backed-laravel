<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WishlistModel extends Model
{
    protected $table='wishlist';
    protected $primaryKey='id';

    protected $fillable=['product_id','user_id'];

    public function product(){
        return $this->belongsTo(ProductsModel::class,'product_id','id');
    }
    public function user(){
        return $this->belongsTo(Users_Model::class,'user_id','id');
    }
}
