<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FaqModel extends Model
{
    protected $table='faq';
    protected $primaryKey='id';
    protected $fillable=['question','answer','position','tab'];
}
