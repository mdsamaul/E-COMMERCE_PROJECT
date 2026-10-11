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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_provider', 50)->nullable()->after('payment_status');
            $table->string('courier_tracking_id', 100)->nullable()->index()->after('courier_provider');
            $table->string('courier_consignment_id', 100)->nullable()->index()->after('courier_tracking_id');
            $table->string('courier_status', 50)->nullable()->index()->after('courier_consignment_id');
            $table->text('courier_error')->nullable()->after('courier_status');
            $table->json('courier_response')->nullable()->after('courier_error');
            $table->timestamp('courier_dispatched_at')->nullable()->after('courier_response');
            $table->string('gateway_transaction_id', 100)->nullable()->index()->after('courier_dispatched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'courier_provider',
                'courier_tracking_id',
                'courier_consignment_id',
                'courier_status',
                'courier_error',
                'courier_response',
                'courier_dispatched_at',
                'gateway_transaction_id',
            ]);
        });
    }
};
