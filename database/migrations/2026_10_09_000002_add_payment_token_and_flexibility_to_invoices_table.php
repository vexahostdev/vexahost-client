<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'payment_token')) {
                $table->string('payment_token', 64)->nullable()->unique()->after('invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->after('project_id');
            }
        });

        // Generate payment_token for existing invoices that don't have one
        $invoices = DB::table('invoices')->whereNull('payment_token')->orWhere('payment_token', '')->get(['id']);
        foreach ($invoices as $inv) {
            DB::table('invoices')->where('id', $inv->id)->update([
                'payment_token' => Str::random(40),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'payment_token')) {
                $table->dropColumn('payment_token');
            }
            if (Schema::hasColumn('invoices', 'subscription_id')) {
                $table->dropColumn('subscription_id');
            }
        });
    }
};
