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
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->index('created_at');
        });

        Schema::table('work_orders', function (Blueprint $table): void {
            $table->index('asset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table): void {
            $table->dropIndex('work_orders_asset_id_index');
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->dropIndex('activity_logs_created_at_index');
        });
    }
};
