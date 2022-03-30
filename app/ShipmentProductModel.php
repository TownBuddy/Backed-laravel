<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipmentProductModel extends Model
{
    protected $table='shipment_products';
    protected $primaryKey='id';
    protected $fillable=['shipment_id','product_id','product_qty','product_total','tb_charges','traveller_reward','reward_currency','notes'];

    public function shipment(){
        return $this->belongsTo(ShipmentModel::class,'shipment_id','id');
    }
    public function product(){
        return $this->belongsTo(ProductsModel::class,'product_id','id');
    }

}
