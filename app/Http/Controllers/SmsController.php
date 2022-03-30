<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Users_Model;

class SmsController extends Controller
{
    //Define API ACCESS KEY
    private $API_AUTH_KEY = '365949A272dF8gN4S611cd060P1';
    
    
    // Send OTP
    public function sendOtp($mobile,$template,$fields){
        #prep the bundle
        $curl = curl_init();
        $authKey = $this->API_AUTH_KEY;
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/v5/otp?template_id=$template&mobile=$mobile&authkey=$authKey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            //echo "cURL Error #:" . $err;
            return false;
        } else {
            //echo $response;
            return true;
        }
        
    }
    
    // Send SMS
    public function sendSMS($fields){
        #prep the bundle
        $curl = curl_init();
        $authKey = $this->API_AUTH_KEY;
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array(
                "authkey: $authKey",
                "content-type: application/json"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            //echo "cURL Error #:" . $err;
            return false;
        } else {
            //echo $response;
            return true;
        }
        
    }
    
}
