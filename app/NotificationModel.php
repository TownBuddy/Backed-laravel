<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    protected $table='notifications';
    protected $primaryKey='id';
    protected $fillable=['user_id','title','message','reference_id','notification_user_type','type','notification_type','notification_image','remainder_type','remainder_value','remainder_time','status'];

    public function user(){
        return $this->belongsTo(Users_Model::class,'user_id','id');
    }
}
