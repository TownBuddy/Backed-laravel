<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DealsModel;
use App\NotificationModel;
use Carbon\Carbon;

class RemainderCron4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remaindercron:fourth';

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
                    $q->where('tracking_status', 'arrived');
                })->orderBy('id','desc')->get();
        
        //echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->arrival_datetime))/3600, 0);
                
                //echo $deal->id.'-'.$deal->trip->arrival_datetime.'-  '.$timediff.'<br>';
                if(($timediff >= 3)){
                    
                    $notification = NotificationModel::where('reference_id',$deal->id)
                                        ->where('type','deal')
                                        ->where('remainder_type','delivery')
                                        ->where('remainder_time','3')
                                        ->whereTime('created_at','>',Carbon::now()->subHours(1))
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new \App\Http\Controllers\NotificationController();
                        $notification->deliveryRemainder($deal);
                        
                        $this->info('3 hour after deliveryRemainder send for '.$deal->id);
                    }
                }
            }
        }
    }
}
