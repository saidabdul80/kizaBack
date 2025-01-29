<?php

namespace App\Listeners;


use App\Events\EmailVerified;
use App\Services\Util;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSMS implements ShouldQueue
{

    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EmailVerified $event): void
    {
     
        try {
            
            $user = $event->user;
            $phone_number = $user->phone_number_1;
            $sms = "Your Account has been created. \n Login with: \n Username:$user->g_tin \n Password:".config('default.password'); 
            Util::sendSMS($phone_number, $sms , 'sms');
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending SMS: ' . $e->getMessage());
        }
    
    }
   

    
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

}
