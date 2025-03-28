<?php
namespace App\Listeners;

use App\Events\CustomerRegistered;
use App\Mail\SendMail;
use App\Mail\SendMailNoQueue;
use Illuminate\Support\Facades\Mail;
use App\Services\Util as ServicesUtil;

class SendCustomerNotifications
{
    public function handle(CustomerRegistered $event)
    {
        $customer = $event->customer;
        $type = $event->type;

        $code = generate_random_number();
        $mailCode = generate_random_number();
        $expired_at = expires_at();
        $customer->update([
            'email_otp' => $mailCode,
            'phone_number_otp' => $code,
            'phone_number_otp_expires_at' => $expired_at,
            'email_otp_expires_at' => $expired_at
        ]);
        
        if($type == 'forAdmin'){
            $user = is_array($customer)?$customer:$customer->toArray();                              
            Mail::to($user['email'])->send(new SendMail('account_created', "Account Created", $user));
            return;
        }

        if(!$customer->phone_number_verified_at && ($type == null || $type == 'sms')){
            ServicesUtil::sendSMS(
                $customer->phone_number,
                'Your OTP code is ' . $customer->phone_number_otp . ' and expires in 10 minutes.',
                'single'
            );
        }

        if(!$customer->email_verified_at && ($type == null || $type == 'email')){
            Mail::to($customer->email)->send(new SendMailNoQueue('otp', 'Kiza Email Verification', [
                'name' => $customer->first_name,
                'otp' => $mailCode,
                'expired_at' => $expired_at,
            ]));
        }
    }
}
