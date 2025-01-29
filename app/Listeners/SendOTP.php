<?php

namespace App\Listeners;

use App\Events\AccountCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail; // Make sure to import the Mail facade
use App\Mail\SendMail; // Make sure to import your SendMail class
use App\Services\Util;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
class SendOTP implements ShouldQueue
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
    public function handle(AccountCreated $event): void
    {
        
        $user = $event->user;
        $code = generate_random_number();
        $expired_at = expires_at();
        
       $data =Util::OTPUtils($user,$expired_at, $code);
       
        $user->update(['otp' => $code, 'otp_expires_at' => $expired_at]);
        Mail::to($user->email)->send(new SendMail('otp', "Account Verification", $data));
    }

    
    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;
}
