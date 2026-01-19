<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->boolean('notify_email')->default(false)->after('email');
            $table->boolean('notify_sms')->default(false)->after('notify_email');
            $table->timestamp('notified_approaching_at')->nullable()->after('notify_sms');
            $table->timestamp('notified_called_at')->nullable()->after('notified_approaching_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn([
                'notify_email',
                'notify_sms',
                'notified_approaching_at',
                'notified_called_at',
            ]);
        });
    }
};
