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
        Schema::table('ticket_escalations', function (Blueprint $table) {
            $table->integer('pending_level1_hours')->nullable()->after('level2_notifications');
            $table->json('pending_level1_users')->nullable()->after('pending_level1_hours');
            $table->json('pending_level1_notifications')->nullable()->after('pending_level1_users');
            $table->integer('pending_level2_hours')->nullable()->after('pending_level1_notifications');
            $table->json('pending_level2_users')->nullable()->after('pending_level2_hours');
            $table->json('pending_level2_notifications')->nullable()->after('pending_level2_users');
        });

        Schema::table('new_ticket_escalation_executions', function (Blueprint $table) {
            $table->string('type')->default('completion')->after('escalation_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_escalations', function (Blueprint $table) {
            $table->dropColumn([
                'pending_level1_hours',
                'pending_level1_users',
                'pending_level1_notifications',
                'pending_level2_hours',
                'pending_level2_users',
                'pending_level2_notifications',
            ]);
        });

        Schema::table('new_ticket_escalation_executions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
