<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_number')->unique();
            $table->text('summary')->nullable();
            $table->text('overall_notes')->nullable();
            $table->longText('technician_signature')->nullable();
            $table->longText('client_signature')->nullable();
            $table->string('client_signed_by')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->enum('status', ['draft', 'completed', 'signed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('asset_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->enum('storage_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('storage_check_notes')->nullable();
            $table->enum('ram_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('ram_check_notes')->nullable();
            $table->enum('temp_files_cleanup', ['passed', 'failed', 'na'])->default('na');
            $table->string('temp_files_cleanup_notes')->nullable();
            $table->enum('ssd_health_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('ssd_health_check_notes')->nullable();
            $table->enum('windows_update_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('windows_update_check_notes')->nullable();
            $table->enum('driver_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('driver_check_notes')->nullable();
            $table->enum('virus_scan', ['passed', 'failed', 'na'])->default('na');
            $table->string('virus_scan_notes')->nullable();
            $table->enum('printer_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('printer_check_notes')->nullable();
            $table->enum('hardware_cleaning', ['passed', 'failed', 'na'])->default('na');
            $table->string('hardware_cleaning_notes')->nullable();
            $table->text('general_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('network_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_report_id')->constrained()->cascadeOnDelete();
            $table->enum('internet_connectivity', ['passed', 'failed', 'na'])->default('na');
            $table->string('internet_connectivity_notes')->nullable();
            $table->enum('speed_test', ['passed', 'failed', 'na'])->default('na');
            $table->string('speed_test_notes')->nullable();
            $table->string('download_speed')->nullable();
            $table->string('upload_speed')->nullable();
            $table->enum('router_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('router_check_notes')->nullable();
            $table->enum('lan_cable_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('lan_cable_check_notes')->nullable();
            $table->enum('ip_conflict_check', ['passed', 'failed', 'na'])->default('na');
            $table->string('ip_conflict_check_notes')->nullable();
            $table->text('general_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_checklists');
        Schema::dropIfExists('asset_checklists');
        Schema::dropIfExists('visit_reports');
    }
};
