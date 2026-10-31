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
        Schema::create('asset_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('report_type');
            $table->json('parameters')->nullable();
            $table->string('status')->default('pending');
            $table->string('format')->default('json');
            $table->string('output_uri')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_reports');
    }
};
