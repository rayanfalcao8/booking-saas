<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['service_id', 'staff_id']);
        });

        $assignments = DB::table('services')
            ->join('staff', 'services.business_id', '=', 'staff.business_id')
            ->select([
                'services.id as service_id',
                'staff.id as staff_id',
            ])
            ->get()
            ->map(fn (object $assignment): array => [
                'service_id' => $assignment->service_id,
                'staff_id' => $assignment->staff_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($assignments !== []) {
            DB::table('service_staff')->insert($assignments);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_staff');
    }
};
