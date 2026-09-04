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
            $table->unsignedBigInteger('EmployeeID')->nullable()->after('id');
            $table->foreign('EmployeeID')->references('id')->on('employees')->onDelete('set null');
            $table->string('username')->unique()->after('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('EmployeeID');
            $table->dropColumn('username');
        });
    }
};
