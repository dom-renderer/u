<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('particular_id')->constrained('particulars');
            $table->foreignId('issue_id')->constrained('issues');
            $table->unsignedInteger('level1_hours');
            $table->json('level1_users');
            $table->json('level1_notifications');
            $table->unsignedInteger('level2_hours');
            $table->json('level2_users');
            $table->json('level2_notifications');
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['department_id','particular_id','issue_id'], 'uniq_ticket_escalation_combo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_escalations');
    }
};
