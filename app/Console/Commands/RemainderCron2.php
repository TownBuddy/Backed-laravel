<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DealsModel;
use App\NotificationModel;
use Carbon\Carbon;

class RemainderCron2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remaindercron:second';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Remainder Notifications to Sender/Shopper and Traveller.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'picked_up');
                })->whereHas('trip', function($q){
                    $q->whereDate('departure_datetime','>=', date('Y-m-d',strtotime("-1 days")));
                    $q->whereDate('departure_datetime','<=', date('Y-m-d',strtotime("+1 days")));
                })->orderBy('id','desc')->get();
        
        //echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->departure_datetime))/3600, 0);
                
                //echo $deal->id.'-'.$deal->trip->departure_datetime.'-  '.$timediff.'<br>';
                //if(($timediff >= 0) || ($timediff <= 1) ){
                if(($timediff >= 0)){
                    $min_diff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->departure_datetime))/60, 0);
                    if($min_diff > 20){
                        $notification = NotificationModel::where('reference_id',$deal->id)
                                            ->where('type','deal')
                                            ->where('remainder_type','transit')
                                            ->where('remainder_time','45')
                                            ->whereTime('created_at','>',Carbon::now()->subMinutes(45))
                                            ->where('notification_type','remainders')->get();
                        if(!(count($notification) > 0)){
                            
                            $notification = new \App\Http\Controllers\NotificationController();
                            $notification->checkTransitRemainder($deal);
                            
                            $this->info('45 min after checkTransitRemainder send for '.$deal->id);
                        }
                        
                    }
                }else if($timediff == -4){
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','departure_traveller')
                                        ->where('remainder_time','4')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        // Send About to depart notification
                        $notification = new \App\Http\Controllers\NotificationController();
                        $notification->departureTravellerRemainder($deal);
                        
                        $this->info('4 hour before departureTravellerRemainder send for '.$deal->id);
                    }
                }
            }
        }
    }
}
