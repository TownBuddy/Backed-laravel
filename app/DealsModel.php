<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DealsModel extends Model
{
    protected $table='deals';
    protected $primaryKey='id';
    protected $fillable=['shipment_id','trip_id','request_by','request_to','request_by_type','request_to_type','request_status','cancel_by','cancel_by_id','cancel_by_staff_id','cancel_reason'];

    public function shipment(){
        return $this->belongsTo(ShipmentModel::class,'shipment_id','id');
    }
    public function trip(){
        return $this->belongsTo(TripsModel::class,'trip_id','id');
    }
    public function request_by_user(){
        return $this->belongsTo(Users_Model::class,'request_by','id');
    }
    public function request_to_user(){
        return $this->belongsTo(Users_Model::class,'request_to','id');
    }
}
