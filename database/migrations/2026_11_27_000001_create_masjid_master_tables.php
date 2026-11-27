<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->enum('restriction', ['unrestricted', 'restricted']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 10)->unique();
            $table->string('name', 120);
            $table->enum('type', ['asset', 'liability', 'net_asset', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('accounts')->nullOnDelete();
        });

        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->enum('type', ['cash', 'bank', 'ewallet']);
            $table->unsignedInteger('account_id');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->enum('type', ['income', 'expense']);
            $table->unsignedInteger('account_id');
            $table->unsignedTinyInteger('default_fund_id')->nullable();
            $table->string('icon', 50)->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('default_fund_id')->references('id')->on('funds')->nullOnDelete();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('cash_accounts');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('funds');
    }
};
