<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // [{"label": "DP", "percent": 30}, ...] — null/empty = bayar penuh sekali.
            $table->json('payment_terms')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('payment_terms');
        });
    }
};
