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
        Schema::table('asset_reports', function (Blueprint $table) {
            $table->string('report_type', 100)->change();
            $table->timestamp('requested_at')->nullable()->after('output_uri');
            $table->timestamp('started_at')->nullable()->after('requested_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->text('error_message')->nullable()->after('completed_at');

            $table->index(['report_type', 'status']);
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_reports', function (Blueprint $table) {
            $table->dropIndex(['report_type', 'status']);
            $table->dropIndex(['requested_at']);

            $table->dropColumn([
                'requested_at',
                'started_at',
                'completed_at',
                'error_message',
            ]);

            $table->string('report_type', 255)->change();
        });
    }
};
