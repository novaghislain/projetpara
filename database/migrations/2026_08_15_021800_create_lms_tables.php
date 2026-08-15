<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('client_id')->constrained('gel_clients')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('content_url')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->string('category')->default('general');
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();
        });

        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('employee_id'); // ID de l'employé
            $table->string('status')->default('enrolled');
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('courses');
    }
};
