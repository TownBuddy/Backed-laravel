<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SettingsModel extends Model
{
    protected $table='settings';
    protected $primaryKey='id';
    protected $fillable=['townbuddy_charges_percent','tb_shipment_price_percent','tb_volume_charges','tb_weight_charges','tb_size_s_length','tb_size_s_width','tb_size_s_height','tb_size_m_length','tb_size_m_width','tb_size_m_height','tb_size_l_length','tb_size_l_width','tb_size_l_height','tb_size_h_length','tb_size_h_width','tb_size_h_height','gst_on_quotation','price_range'];
}
