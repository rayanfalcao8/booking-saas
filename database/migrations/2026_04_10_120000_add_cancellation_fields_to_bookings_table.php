<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('cancellation_token', 64)
                ->nullable()
                ->after('status');
            $table->timestamp('canceled_at')
                ->nullable()
                ->after('cancellation_token');

            $table->index(['business_id', 'cancellation_token']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'cancellation_token']);
            $table->dropColumn(['cancellation_token', 'canceled_at']);
        });
    }
};
