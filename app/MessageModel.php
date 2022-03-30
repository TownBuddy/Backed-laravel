<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageModel extends Model
{
    protected $table='messages';
    protected $primaryKey='id';
    protected $fillable=['sender_id','receiver_id','deal_id','message','seen_status','msg_time','seen_time','deliver_time'];
	
	public function sender(){
        return $this->belongsTo(Users_Model::class,'sender_id','id');
    }
	public function receiver(){
        return $this->belongsTo(Users_Model::class,'receiver_id','id');
    }
    public function deal(){
        return $this->belongsTo(DealsModel::class,'deal_id','id');
    }
}
