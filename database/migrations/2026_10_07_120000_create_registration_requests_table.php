<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('registration_requests')) {
            Schema::create('registration_requests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->string('requested_role');
                $table->string('status')->default('pending');
                $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index(['email', 'status']);
            });

            return;
        }

        $hasLegacyReviewer = Schema::hasColumn('registration_requests', 'reviewed_by');

        Schema::table('registration_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('registration_requests', 'reviewed_by_user_id')) {
                $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('registration_requests', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        if ($hasLegacyReviewer) {
            DB::table('registration_requests')
                ->whereNotNull('reviewed_by')
                ->update(['reviewed_by_user_id' => DB::raw('reviewed_by')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('registration_requests')) {
            return;
        }

        if (Schema::hasColumn('registration_requests', 'reviewed_by')) {
            return;
        }

        Schema::dropIfExists('registration_requests');
    }
};
