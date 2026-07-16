<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40)->default('vakifbank');
            $table->string('environment', 20)->default('test');
            $table->string('customer_no', 20)->nullable();
            $table->string('account_no', 20)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('corporate_username')->nullable();
            $table->text('corporate_password')->nullable();
            $table->string('service_url')->nullable();
            $table->unsignedSmallInteger('sync_interval_minutes')->default(5);
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->unique(['site_id', 'provider']);
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_integration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40)->default('vakifbank');
            $table->string('bank_transaction_id', 120);
            $table->string('operation_no', 80)->nullable();
            $table->timestamp('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->string('direction', 1)->default('A');
            $table->string('sender_name')->nullable();
            $table->string('sender_iban', 34)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('unmatched');
            $table->foreignId('matched_due_id')->nullable()->constrained('dues')->nullOnDelete();
            $table->foreignId('matched_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('match_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'bank_transaction_id']);
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_integrations');
    }
};
