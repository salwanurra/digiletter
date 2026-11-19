<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('campus_student_id')->nullable()->unique();
            $table->string('npm', 13)->unique();
            $table->enum('status', ['pending_review', 'verified', 'rejected'])->default('pending_review')->index();
            $table->string('old_name')->nullable();
            $table->string('old_npm', 13)->nullable();
            $table->string('old_email')->nullable();
            $table->string('official_name');
            $table->string('official_email')->nullable();
            $table->decimal('name_similarity', 5, 2)->nullable();
            $table->json('match_reasons')->nullable();
            $table->json('campus_payload');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_identity_verifications');
    }
};
