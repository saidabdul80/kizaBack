<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AjoInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ajoName;
    public $inviteEmail;
    public $ajoId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ajoName, $inviteEmail, $ajoId)
    {
        $this->ajoName = $ajoName;
        $this->inviteEmail = $inviteEmail;
        $this->ajoId = $ajoId;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject:'You are invited to join an Ajo',
        );
    }

     /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        
        return new Content(
            view: 'emails.ajo_invite',
            with: [
                'ajoName' => $this->ajoName,
                'inviteEmail' => $this->inviteEmail,    
                'ajoId' => $this->ajoId,
            ],
        );
    }

   
}
