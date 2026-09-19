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
        Schema::create('depreciations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_book_value', 14, 2)->nullable();
            $table->decimal('depreciation_amount', 14, 2)->nullable();
            $table->decimal('closing_book_value', 14, 2)->nullable();
            $table->string('method')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciations');
    }
};
