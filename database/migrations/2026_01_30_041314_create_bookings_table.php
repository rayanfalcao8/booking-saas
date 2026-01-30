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
        Schema::create('bookings', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();

            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->restrictOnDelete();

            $table->date('date'); // 2026-02-01
            $table->time('start_time'); // 09:00
            $table->time('end_time');   // 09:30

            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->string('status')->default('confirmed'); // confirmed/canceled
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'date', 'staff_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
