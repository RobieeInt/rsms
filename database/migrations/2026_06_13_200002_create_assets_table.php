<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('asset_code')->unique();
            $table->string('asset_name');
            $table->enum('asset_type', [
                'desktop_pc', 'laptop', 'printer', 'router', 'switch',
                'access_point', 'cctv', 'nas', 'server', 'other'
            ]);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('cpu')->nullable();
            $table->string('ram')->nullable();
            $table->string('storage')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('location')->nullable();
            $table->year('purchase_year')->nullable();
            $table->text('notes')->nullable();
            $table->enum('health_status', ['good', 'fair', 'poor', 'critical'])->default('good');
            $table->string('qr_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
