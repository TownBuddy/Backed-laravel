<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedbackModel extends Model
{
    protected $table='feedback';
    protected $primaryKey='id';
    protected $fillable=['user_id','type','subject','message'];
}
