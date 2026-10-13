<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('mail_noreply_address')->nullable()->after('email_support');
            $table->string('mail_noreply_name')->nullable()->after('mail_noreply_address');
            $table->string('mail_support_name')->nullable()->after('mail_noreply_name');
            $table->string('mail_reply_to_address')->nullable()->after('mail_support_name');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_noreply_address',
                'mail_noreply_name',
                'mail_support_name',
                'mail_reply_to_address',
            ]);
        });
    }
};
