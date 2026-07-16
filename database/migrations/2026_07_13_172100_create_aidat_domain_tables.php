<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('building_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name', 40);
            $table->timestamps();
        });

        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_block_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40);
            $table->unsignedSmallInteger('floor_no')->nullable();
            $table->string('status', 20)->default('occupied');
            $table->timestamps();
            $table->unique(['building_block_id', 'number']);
        });

        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->string('full_name', 160);
            $table->string('phone', 30)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('resident_type', 20)->default('owner');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['apartment_id', 'period_year', 'period_month']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('due_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method', 20)->default('bank');
            $table->string('receipt_no', 40)->unique();
            $table->timestamp('paid_at');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('category', 80);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('content');
            $table->date('publish_date');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('table_name', 80)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('dues');
        Schema::dropIfExists('residents');
        Schema::dropIfExists('apartments');
        Schema::dropIfExists('building_blocks');
        Schema::dropIfExists('sites');
    }
};
