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
        Schema::create('maintenences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assetId');
            $table->date('maintenanceDate');
            $table->string('type')->nullable();
            $table->string('performedBy')->nullable();
            $table->string('description')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('result')->nullable();
            $table->date('nextDueDate')->nullable();
            $table->unsignedBigInteger('createbyUserId')->nullable();

            $table->foreign('assetId')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('createbyUserId')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenences');
    }
};
