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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_category_id')->nullable();
            $table->unsignedBigInteger('current_location_id')->nullable();
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('purchase_cost', 14, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('status')->default('active');
            $table->foreign('asset_category_id')->references('id')->on('asset_categories')->nullOnDelete();
            $table->foreign('current_location_id')->references('id')->on('locations')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
