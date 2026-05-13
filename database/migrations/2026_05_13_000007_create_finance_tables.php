<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add cost_price to products (used for inventory valuation & HPP)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 14, 2)->default(0)->after('price');
        });

        // HPP method configuration per business
        Schema::create('hpp_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['fifo', 'weighted_average', 'lifo'])->default('weighted_average');
            $table->boolean('is_auto')->default(true); // auto-update cost_price on GRN
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('business_id');
        });

        // Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'cogs', 'expense'])->default('expense');
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->boolean('is_system')->default(false); // built-in accounts
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['business_id', 'code']);
        });

        // Journal Entries (header)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('entry_no');
            $table->string('reference_no')->nullable();
            $table->string('reference_type')->nullable(); // so, po, grn, return, manual
            $table->string('description');
            $table->date('entry_date');
            $table->boolean('is_auto')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['business_id', 'entry_no']);
        });

        // Journal Entry Lines (debit/credit)
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->timestamps();
        });

        // Tax / PPN Configurations
        Schema::create('tax_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);       // e.g. "PPN 11%"
            $table->string('code', 20);        // e.g. "PPN"
            $table->decimal('rate_percent', 6, 2)->default(11); // 11 = 11%
            $table->enum('tax_type', ['ppn', 'pph', 'other'])->default('ppn');
            $table->boolean('is_inclusive')->default(false); // price includes tax
            $table->enum('applies_to', ['sales', 'purchases', 'all'])->default('sales');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Accounting Integration Settings
        Schema::create('accounting_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50); // accurate, jurnal, zahir, custom
            $table->text('api_key')->nullable();
            $table->string('endpoint', 500)->nullable();
            $table->json('settings')->nullable();  // extra provider-specific config
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->unique('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_integrations');
        Schema::dropIfExists('tax_configs');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('hpp_configs');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
