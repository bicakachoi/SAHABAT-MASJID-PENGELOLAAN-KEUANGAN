<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 20)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->nullable()->constrained('donors')->nullOnDelete();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->enum('method', ['cash', 'transfer', 'qris']);
            $table->timestamp('receipt_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mustahik', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->enum('asnaf', ['fakir', 'miskin', 'amil', 'muallaf', 'riqab', 'gharim', 'fisabilillah', 'ibnu_sabil']);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mustahik_id')->constrained('mustahik');
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->string('program', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->unsignedInteger('account_id');
            $table->date('acquired_at');
            $table->decimal('acquisition_cost', 15, 2);
            $table->unsignedSmallInteger('useful_life_months')->default(60);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->enum('condition', ['good', 'fair', 'damaged'])->default('good');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('year');
            $table->string('title', 120);
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamps();
        });

        Schema::create('budget_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('budget_id');
            $table->unsignedInteger('category_id');
            $table->unsignedTinyInteger('month');
            $table->decimal('planned_amount', 15, 2);
            $table->timestamps();

            $table->foreign('budget_id')->references('id')->on('budgets')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('distributions');
        Schema::dropIfExists('mustahik');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donors');
    }
};
