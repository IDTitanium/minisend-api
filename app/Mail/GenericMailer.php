<?php

namespace App\Mail;

use App\Models\EmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMailer extends Mailable
{
    use Queueable, SerializesModels;

    private $notification;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(EmailNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailbuilder = $this->from($this->notification->sender, $this->notification->sender_name)
                        ->subject($this->notification->subject)
                        ->to($this->notification->receiver)
                        ->view('emails.generic_template', ['notification' => $this->notification]);
        if(!empty($this->notification->attachments)) {
            foreach($this->notification->attachments as $attachment) {
                $mailbuilder->attachFromStorage($attachment->file_path);
            }
            return $mailbuilder;
        }
        return $mailbuilder;
    }
}
