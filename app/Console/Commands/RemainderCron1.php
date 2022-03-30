<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DealsModel;
use App\ShipmentModel;
use App\TripsModel;
use App\NotificationModel;
use Carbon\Carbon;

class RemainderCron1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remaindercron:first';

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
        //$notification = new \App\Http\Controllers\NotificationController();
        //$notification->send_chat_notification();
        //$this->info('Reminder Check'.date('Y-m-d H:i:s'));
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'placed');
                })->whereHas('trip', function($q){
                    $q->whereDate('departure_datetime','>=', date('Y-m-d',strtotime("-1 days")));
                    $q->whereDate('departure_datetime','<=', date('Y-m-d',strtotime("+1 days")));
                })->orderBy('id','desc')->get();
        
        //echo date('Y-m-d H:i:s').'<br>';
        
        if(count($deals) > 0){
            foreach($deals as $deal){
                $timediff = round((strtotime(date('Y-m-d H:i')) - strtotime($deal->trip->departure_datetime))/3600, 0);
                
                //echo $deal->id.'-'.$deal->trip->departure_datetime.'-  '.$timediff.'<br>';
                if($timediff == -24){
                    $notification = NotificationModel::where('reference_id',$deal->shipment->shipment_uniqueid)
                                        ->where('type','deal')
                                        ->where('remainder_type','pickup')
                                        ->where('remainder_time','24')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new \App\Http\Controllers\NotificationController();
                        $notification->pickupRemainder($deal,'24');
                        
                        $this->info('24 hour before pickupRemainder send for '.$deal->shipment->shipment_uniqueid);
                    }
                }else if($timediff == -10){
                    $notification = NotificationModel::where('reference_id',$deal->shipment->shipment_uniqueid)
                                        ->where('type','deal')
                                        ->where('remainder_type','pickup')
                                        ->where('remainder_time','10')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new \App\Http\Controllers\NotificationController();
                        $notification->pickupRemainder($deal,'10');
                        
                        $this->info('10 hour before pickupRemainder send for '.$deal->shipment->shipment_uniqueid);
                    }
                }else if($timediff == -4){
                    $notification = NotificationModel::where('reference_id',$deal->shipment->shipment_uniqueid)
                                        ->where('type','deal')
                                        ->where('remainder_type','pickup')
                                        ->where('remainder_time','4')
                                        ->where('notification_type','remainders')->get();
                    if(!(count($notification) > 0)){
                        
                        $notification = new \App\Http\Controllers\NotificationController();
                        $notification->pickupRemainder($deal,'4');
                        $this->info('4 hour before pickupRemainder send for '.$deal->shipment->shipment_uniqueid);
                    }
                }
            }
        }
        
        // Deal Cancel By System Code
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '2');
                    $q->where('tracking_status', 'placed');
                })->whereHas('trip', function($q){
                    $q->where('arrival_datetime','<', date('Y-m-d H:i:s'));
                })->where('request_status','1')->orderBy('id','desc')->get();
        
        //echo date('Y-m-d H:i:s').'<br>';
        if(count($deals) > 0){
            foreach($deals as $deal){
                $deal->request_status = '2';
                $deal->cancel_by = 'system';
                $deal->cancel_reason = 'Shipment handover to traveller before departure date was unsuccessful.';
                $deal->save();
                
                ShipmentModel::where('id',$deal->shipment_id)->update(['status'=>'6','cancel_reason'=>'Shipment handover to traveller before departure date was unsuccessful.']);
                TripsModel::where('id',$deal->trip_id)->update(['trip_status'=>'5','cancel_reason'=>'Shipment handover to traveller before departure date was unsuccessful.']);
                
                $notification = new \App\Http\Controllers\NotificationController();
                $notification->dealCancelBySystem($deal);
                
            }
        }
        
        return 0;
    }
}
