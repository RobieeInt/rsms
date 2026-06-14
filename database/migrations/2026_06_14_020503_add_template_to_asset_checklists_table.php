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
        Schema::table('asset_checklists', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_checklists', 'template_id')) {
                $table->foreignId('template_id')->nullable()->after('asset_id')
                    ->constrained('checklist_templates')->nullOnDelete();
            } else {
                $table->foreign('template_id')->references('id')->on('checklist_templates')->nullOnDelete();
            }
            $table->json('results')->nullable()->after('template_id');
        });
    }

    public function down(): void
    {
        Schema::table('asset_checklists', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'results']);
        });
    }
};
