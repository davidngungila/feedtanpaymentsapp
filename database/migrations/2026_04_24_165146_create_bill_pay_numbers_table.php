<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bill_pay_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('bill_pay_number')->unique();
            $table->string('bill_description');
            $table->decimal('bill_amount', 10, 2)->nullable();
            $table->string('bill_currency', 3)->default('TZS');
            $table->string('bill_payment_mode', 50)->default('ALLOW_PARTIAL_AND_OVER_PAYMENT');
            $table->string('bill_status', 20)->default('ACTIVE');
            $table->string('bill_type', 20)->default('order'); // order or customer
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('bill_reference')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->timestamp('last_payment_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['bill_pay_number', 'bill_status']);
            $table->index(['bill_status', 'created_at']);
            $table->index(['customer_phone', 'bill_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_pay_numbers');
    }
};
