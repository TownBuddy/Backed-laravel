<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DealsModel;
use App\ShipmentModel;
use App\TripsModel;
use App\NotificationModel;
use Carbon\Carbon;

class RemainderCron5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remaindercron:fifth';

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
        
        // Deal Cancel By System Code
        //Get all shipments that need to pickup
        $deals = DealsModel::whereHas('shipment', function($q){
                    $q->where('status', '1');
                    $q->where('tracking_status', 'placed');
                })->whereHas('trip', function($q){
                    $q->where('departure_datetime','<', date('Y-m-d H:i:s'));
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
