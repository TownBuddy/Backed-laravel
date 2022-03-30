<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Users_Model;
use App\MessageModel;

class SendChatNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendChatNotification:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It sends hourly notification to users about their missed chats';

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
        $users = Users_Model::where('is_admin','0')->where('fcm_token','!=','')->get();
        if(count($users) > 0){
            foreach($users as $user){
                $msgs = MessageModel::has('sender')->where('seen_status','0')->where('receiver_id',$user->id)->orderBy('id','desc')->get()->unique('sender_id');
                foreach($msgs as $msg){
                    $notification = new \App\Http\Controllers\NotificationController();
                    $notification->missedChatNotification($user,$msg);
                    
                    $this->info('Send Missed Notification to '.$user->id);
                }
            }
        }
    }
}
