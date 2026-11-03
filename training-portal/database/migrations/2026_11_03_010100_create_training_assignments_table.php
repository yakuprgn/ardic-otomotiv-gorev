<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name', 201);
            $table->string('employee_email');
            $table->string('employee_department', 120);
            $table->timestamp('assigned_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['training_id', 'employee_id']);
            $table->index(['completed_at', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_assignments');
    }
};
