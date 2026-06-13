<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('pic_name');
            $table->string('pic_email');
            $table->string('pic_phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('monthly_retainer_fee', 15, 2)->default(0);
            $table->integer('invoice_due_date')->default(30)->comment('Days after invoice creation');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->decimal('health_score', 5, 2)->default(100);
            $table->enum('health_status', ['healthy', 'warning', 'critical'])->default('healthy');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
