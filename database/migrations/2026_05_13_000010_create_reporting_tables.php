<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('metric', 50); // revenue, orders, gross_profit, inventory_value
            $table->decimal('target_value', 16, 2)->default(0);
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('month')->unsigned(); // 1-12
            $table->timestamps();
            $table->unique(['business_id', 'metric', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
