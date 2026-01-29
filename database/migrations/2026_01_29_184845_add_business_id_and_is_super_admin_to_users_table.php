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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('business_id')
                ->nullable()
                ->after('id')
                ->constrained('businesses')
                ->nullOnDelete();

            $table->boolean('is_super_admin')
                ->default(false)
                ->after('business_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
            $table->dropColumn('is_super_admin');
        });
    }
};
