<?php

namespace App\Listeners;

use App\Events\EmailVerified;
use App\Mail\SendMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAccountCreatedEmailNotification
{
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
        $user = is_array($event->user)?$event->user:$event->user->toArray();                
        $data = [
            ...$user,
            "user_type" =>'user',
            "name" => $user['full_name']
        ];                
        Mail::to($user['email'])->send(new SendMail('account_created', "Account Created", $data));
    }

}
