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
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assetId');
            $table->unsignedBigInteger('assignedToEmployeeId');
            $table->unsignedBigInteger('fromLocationId')->nullable();
            $table->unsignedBigInteger('toLocationId')->nullable();
            $table->date('assignmentDate');
            $table->date('expectedReturnDate')->nullable();
            $table->date('returnDate')->nullable();
            $table->string('status')->default('active');
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('createdByUserId')->nullable();
            $table->foreign('assetId')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('assignedToEmployeeId')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('fromLocationId')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('toLocationId')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('createdByUserId')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
