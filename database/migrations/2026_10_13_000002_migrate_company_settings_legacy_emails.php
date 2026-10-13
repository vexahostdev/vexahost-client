<?php

use App\Support\MailIdentity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('company_settings')) {
            return;
        }

        DB::table('company_settings')
            ->where('email_support', 'vexahostcloudtech@gmail.com')
            ->update(['email_support' => 'support@vexahostcloud.my.id']);

        DB::table('company_settings')
            ->where('email_company', 'vexahostcloudtech@gmail.com')
            ->update(['email_company' => 'support@vexahostcloud.my.id']);

        DB::table('company_settings')
            ->where('email_internal_alert', 'vexahostcloudtech@gmail.com')
            ->update(['email_internal_alert' => 'support@vexahostcloud.my.id,vexahostcloudtech@gmail.com']);

        MailIdentity::flushCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Idempotent data migration: do not revert to prevent overwriting human changes
    }
};
