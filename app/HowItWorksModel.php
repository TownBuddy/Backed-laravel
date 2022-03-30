<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HowItWorksModel extends Model
{
    protected $table='how_it_works';
    protected $primaryKey='id';
    protected $appends = ['icon_url',];
    protected $fillable=['icon','title','content','position','tab'];
    
    public function getIconUrlAttribute()
    {
        if($this->icon != ''){
            return env('ASSET_URL').$this->icon;
        }else{
            return '';
        }
    }
    
}
