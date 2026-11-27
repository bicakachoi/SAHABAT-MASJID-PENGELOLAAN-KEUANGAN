<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->increments('id');
            $table->char('period', 7)->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trx_number', 30)->unique();
            $table->date('trx_date');
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->unsignedTinyInteger('fund_id');
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('cash_account_id');
            $table->unsignedInteger('to_cash_account_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('description', 255);
            $table->string('counterparty', 120)->nullable();
            $table->enum('status', ['draft', 'posted', 'void'])->default('posted');
            $table->string('void_reason', 255)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->char('row_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['trx_date', 'type'], 'idx_date_type');
            $table->index('fund_id', 'idx_fund');

            $table->foreign('fund_id')->references('id')->on('funds');
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('cash_account_id')->references('id')->on('cash_accounts');
            $table->foreign('to_cash_account_id')->references('id')->on('cash_accounts')->nullOnDelete();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained('transactions')->cascadeOnDelete();
            $table->date('entry_date');
            $table->unsignedTinyInteger('fund_id');
            $table->unsignedInteger('accounting_period_id')->nullable();
            $table->string('memo', 255)->nullable();
            $table->timestamps();

            $table->foreign('fund_id')->references('id')->on('funds');
            $table->foreign('accounting_period_id')->references('id')->on('accounting_periods')->nullOnDelete();
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->unsignedInteger('account_id');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();

            $table->index('account_id', 'idx_account');
            $table->foreign('account_id')->references('id')->on('accounts');
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('accounting_periods');
    }
};
