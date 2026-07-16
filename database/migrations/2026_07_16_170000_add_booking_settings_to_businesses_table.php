<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('is_booking_enabled')->default(true)->after('phone');
            $table->unsignedSmallInteger('booking_min_notice_minutes')->default(60)->after('is_booking_enabled');
            $table->unsignedSmallInteger('booking_max_advance_days')->default(90)->after('booking_min_notice_minutes');
            $table->unsignedTinyInteger('slot_interval_minutes')->default(15)->after('booking_max_advance_days');
            $table->unsignedSmallInteger('cancellation_notice_hours')->default(0)->after('slot_interval_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'is_booking_enabled',
                'booking_min_notice_minutes',
                'booking_max_advance_days',
                'slot_interval_minutes',
                'cancellation_notice_hours',
            ]);
        });
    }
};
