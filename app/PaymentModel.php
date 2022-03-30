<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    protected $table='payments';
    protected $primaryKey='id';
    protected $fillable=['type','user_id','shipment_id','shipment_quotation','payment_response_id','payment_response','payment_method','pay_ac_detail','payment_status','payment_date'];

    public function shipment(){
        return $this->belongsTo(ShipmentModel::class,'shipment_id','id');
    }
    public function user(){
        return $this->belongsTo(Users_Model::class,'user_id','id');
    }
}
