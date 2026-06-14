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
        // template_id column already exists from partial prior run — only add results + FK
        Schema::table('asset_checklists', function (Blueprint $table) {
            $table->json('results')->nullable()->after('template_id');
            $table->foreign('template_id')->references('id')->on('checklist_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_checklists', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn('results');
        });
    }
};
