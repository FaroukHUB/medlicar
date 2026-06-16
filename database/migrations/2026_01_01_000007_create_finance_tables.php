<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comptabilité (entrées/sorties) + factures. Repris de ResaDZ,
 * nettoyé des champs de commission marketplace (is_commission, commission_rate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description');
            $table->text('notes')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('DZD');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->date('transaction_date');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();

            $table->index(['type', 'transaction_date']);
            $table->index('vehicle_id');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('DZD');
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->string('pdf')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('expense_categories');
    }
};
