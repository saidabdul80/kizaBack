<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class SendMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $logo;
    public $filename;
    public $data;
    public $subject;
    public $pdf;
    public function __construct($filename, $subject, $data, array $pdf = null)
    {
       // $this->logo =  Util::getConfigValue('logo');
        $this->filename = $filename;
        $this->subject = $subject;
        $this->data = $data;
        $this->pdf = $pdf;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        return new Content(
            view: 'emails.'.$this->filename,
            with: [
                'logo' => $this->logo,
                'data' => $this->data,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdf && file_exists($this->pdf['path'])) {
            $attachments[] = new \Illuminate\Mail\Mailables\Attachment(
                path: $this->pdf['path'],
                as: $this->pdf['name'],
                mime: 'application/pdf',
            );
        }

        return $attachments;
    }
}
