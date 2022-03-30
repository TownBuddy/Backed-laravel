<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RatingModel extends Model
{
    protected $table='review_rating';
    protected $primaryKey='id';
    protected $fillable=['rate_to','rate_by','rate_to_type','rate_by_type','deal_id','shipment_id','trip_id','rating_star','comment'];

    public function shipment(){
        return $this->belongsTo(ShipmentModel::class,'shipment_id','id');
    }
    public function trip(){
        return $this->belongsTo(TripsModel::class,'trip_id','id');
    }
    public function deal(){
        return $this->belongsTo(DealsModel::class,'deal_id','id');
    }
    public function rate_by_user(){
        return $this->belongsTo(Users_Model::class,'rate_by','id');
    }
    public function rate_to_user(){
        return $this->belongsTo(Users_Model::class,'rate_to','id');
    }
}
