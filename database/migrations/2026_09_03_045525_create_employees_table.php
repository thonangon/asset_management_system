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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('EmployeeCode')->unique();
            $table->string('FirstName');
            $table->string('LastName');
            $table->string('Email')->unique();
            $table->string('Phone')->nullable()->unique();
            $table->enum('Status', ['active', 'inactive','terminated'])->default('active');
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->unsignedBigInteger('DepartmentID');
            $table->foreign('DepartmentID')->references('id')->on('departments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
