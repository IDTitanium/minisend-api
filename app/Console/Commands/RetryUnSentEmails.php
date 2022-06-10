<?php

namespace App\Console\Commands;

use App\Models\EmailNotification;
use Illuminate\Console\Command;

class RetryUnSentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry:failed_emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed emails';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $unSentMails = $this->getAllFailedEmailsPerBatch();
        foreach($unSentMails as $mail) {
            EmailNotification::sendEmail($mail);
        }
    }

    private function getAllFailedEmailsPerBatch()
    {
        return EmailNotification::where('status', EmailNotification::FAILED)->where('retry_at', '<=', now())->limit(200)->get();
    }
}
