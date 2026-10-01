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
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assetId');
            $table->unsignedBigInteger('fromLocationId')->nullable();
            $table->unsignedBigInteger('toLocationId')->nullable();
            $table->unsignedBigInteger('fromEmployeeId')->nullable();
            $table->unsignedBigInteger('toEmployeeId')->nullable();
            $table->date('TransferDate');
            $table->string('Reason')->nullable();
            $table->string('Status')->default('pending');
            $table->unsignedBigInteger('CreatedByUserID')->nullable();
            $table->timestamp('CreatedAt')->nullable();
            $table->timestamp('UpdatedAt')->nullable();

            $table->foreign('assetId')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('fromLocationId')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('toLocationId')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('fromEmployeeId')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('toEmployeeId')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('CreatedByUserID')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};
