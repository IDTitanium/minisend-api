<?php

use App\Models\EmailNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_notifications', function (Blueprint $table) {
            $table->dropColumn('file_path');
            $table->dropColumn('status');
        });

        Schema::table('email_notifications', function(Blueprint $table) {
            $table->uuid('uuid')->default(Str::uuid())->change();
            $table->dateTime('sent_at')->nullable()->change();
            $table->dateTime('retry_at')->nullable()->change();
            $table->enum('status', [
                EmailNotification::POSTED,
                EmailNotification::SENT,
                EmailNotification::FAILED
            ])->default(EmailNotification::POSTED);
            $table->longText('html_body')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_notifications', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('email_notifications', function(Blueprint $table) {
            $table->uuid('uuid')->change();
            $table->timestamp('sent_at')->change();
            $table->timestamp('retry_at')->change();
            $table->enum('status', [
                EmailNotification::POSTED,
                EmailNotification::SENT,
                EmailNotification::FAILED
            ]);
            $table->text('file_path');
        });
    }
};
