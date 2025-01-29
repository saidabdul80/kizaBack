<?php

namespace App\Jobs;

use App\Mail\SendMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class QueueMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $userType;
    protected $blade;
    protected $subject;
    protected $pdf;
    public function __construct($user,$blade, $subject, $userType =null, $pdf = null)
    {
        $this->user = $user;
        $this->userType = $userType;
        $this->blade = $blade;
        $this->subject = $subject;
        $this->pdf = $pdf;
    }

    public function handle()
    {        
        $user = is_array($this->user)? $this->user : $this->user->toArray();
        $data = $user;        
        Mail::to($user['email'])->send(new SendMail($this->blade, $this->subject, $data, $this->pdf));

    }
}