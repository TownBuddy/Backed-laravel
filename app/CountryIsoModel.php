<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryIsoModel extends Model
{
    protected $table='country_iso';
    protected $primaryKey='id';
    protected $fillable=['country_name','iso2','iso3'];
    
}
