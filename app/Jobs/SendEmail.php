<?php

namespace App\Jobs;

use App\Mail\GenericMailer;
use App\Models\EmailNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $maxExceptions = 2;

    private $notification;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EmailNotification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->notification->receiver)->send(new GenericMailer($this->notification));
        $this->notification->markAsSent();
    }

    public function failed(Exception $exception)
    {
        $this->notification->markAsFailed();
    }
}
