<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users_Model extends Model
{
    protected $table='users';
    protected $primaryKey='id';
    protected $appends = ['profile_image_url','profile_review','traveller_review','shopper_review','sender_review'];
    protected $fillable=['name','email','password','api_token','profile_image','country_code','signup_type','mobile_number','is_mob_verified','official_email','is_email_verified','document_type','document_number','document_file','document_file_back','is_doc_verified','otp','otp_time','verification_code','working_at','language','city_id','state','fb_id','linkdin_id','google_id','bio','fb_verify','linkdin_verify','google_verify','match_notification','chat_notification','deal_notification','show_company','referral_code','referred_by','fcm_token','wallet_amount','phone_pay_number','google_pay_number','user_pin','aadhar_client_id','aadhar_response'];
    
    public function getProfileImageUrlAttribute()
    {
        if($this->profile_image != ''){
            return env('ASSET_URL').$this->profile_image;
        }else{
            return env('ASSET_URL').'assets/dummy_user.png';
        }
    }
    public function getTravellerReviewAttribute()
    {
        $reviews = RatingModel::where('rate_to',$this->id)->where('rate_to_type','traveller')->get();
        $rating_total = 0;
        if(count($reviews) > 0){
            $count = count($reviews);
            foreach($reviews as $review){
                $rating_total +=  $review->rating_star;
            }
            
            $rating_total = $rating_total / $count;
            return (string)$rating_total;
        }else{
            return '0';
        }
        
    }
    public function getProfileReviewAttribute()
    {
        $reviews = RatingModel::where('rate_to',$this->id)->get();
        $rating_total = 0;
        if(count($reviews) > 0){
            $count = count($reviews);
            foreach($reviews as $review){
                $rating_total +=  $review->rating_star;
            }
            
            $rating_total = $rating_total / $count;
            return (string)$rating_total;
        }else{
            return '0';
        }
    }
    public function getShopperReviewAttribute()
    {
        $reviews = RatingModel::where('rate_to',$this->id)->where('rate_to_type','shopper')->get();
        $rating_total = 0;
        if(count($reviews) > 0){
            $count = count($reviews);
            foreach($reviews as $review){
                $rating_total +=  $review->rating_star;
            }
            
            $rating_total = $rating_total / $count;
            return (string)$rating_total;
        }else{
            return '0';
        }
    }
    public function getSenderReviewAttribute()
    {
        $reviews = RatingModel::where('rate_to',$this->id)->where('rate_to_type','sender')->get();
        $rating_total = 0;
        if(count($reviews) > 0){
            $count = count($reviews);
            foreach($reviews as $review){
                $rating_total +=  $review->rating_star;
            }
            
            $rating_total = $rating_total / $count;
            return (string)$rating_total;
        }else{
            return '0';
        }
    }

    /*public function city(){
        return $this->belongsTo(CityModel::class,'city_id','id');
    }*/
}
