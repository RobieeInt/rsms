<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->nullable()->after('unit_price');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->nullable()->after('unit_price');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('retainer_cost', 15, 2)->nullable()->after('monthly_retainer_fee');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('retainer_cost');
        });
    }
};
