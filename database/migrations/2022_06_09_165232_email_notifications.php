<?php

use App\Models\EmailNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->index('email_notifications_uuid');
            $table->string('sender');
            $table->string('receiver');
            $table->text('subject');
            $table->longText('body');
            $table->longText('html_body');
            $table->text('file_path');
            $table->enum('status', [
                EmailNotification::POSTED,
                EmailNotification::SENT,
                EmailNotification::FAILED
            ]);
            $table->timestamp('sent_at');
            $table->timestamp('retry_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
