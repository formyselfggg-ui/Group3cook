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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('priority')->default('normal');
            $table->foreignId('assigned_personnel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_assigned')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('work_performed')->nullable();
            $table->text('field_notes')->nullable();
            $table->json('photo_paths')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completion_at')->nullable();
            $table->foreignId('supervisor_approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_personnel_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
