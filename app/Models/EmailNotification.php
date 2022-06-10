<?php

namespace App\Models;

use App\Jobs\SendEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailNotification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $with = ['attachments'];

    const POSTED = 'posted';
    const SENT = 'sent';
    const FAILED = 'failed';

    public function attachments() {
        return $this->hasMany(EmailAttachment::class, 'email_notification_id');
    }

    public static function createNotification(array $data) {
        $notification = null;
        DB::transaction(function() use($data, &$notification) {
            $notification = static::create([
                'uuid' => Str::uuid(),
                'sender' => $data['from'],
                'sender_name' => $data['from_name'],
                'receiver' => $data['to'],
                'subject' => $data['subject'],
                'body' => $data['body'],
                'html_body' => $data['html_body'] ?? null
            ]);
            if(!empty($data['attachments'])) {
                static::createAttachments($notification, $data['attachments']);
            }
        });
        if($notification) {
            static::sendEmail($notification);
        }
        return $notification;
    }

    private static function createAttachments(EmailNotification $notification, $attachments) {
        if(is_array($attachments)) {
          foreach($attachments as $attachment) {
            $path = Storage::putFile('emails/attachments', $attachment);
            if($path) {
               return $notification->attachments()->create(['file_path' => $path]);
            }
          }
        } else {
            $path = Storage::putFile('emails/attachments', $attachments);
            if($path) {
               return $notification->attachments()->create(['file_path' => $path]);
            }
        }
    }

    public static function sendEmail($notification) {
        SendEmail::dispatch($notification);
    }

    public function markAsSent() {
        $this->status = 'sent';
        $this->save();
    }

    public function markAsFailed() {
        $this->status = 'failed';
        $this->save();
    }

    public static function getStats() {
        $countPosted = static::where('status', static::POSTED)->count();
        $countSent = static::where('status', static::SENT)->count();
        $countFailed = static::where('status', static::FAILED)->count();

        return [
            'countPosted' => $countPosted,
            'countSent' => $countSent,
            'countFailed' => $countFailed
        ];
    }
}
