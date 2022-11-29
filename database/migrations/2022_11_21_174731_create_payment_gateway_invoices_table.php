<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_invoices', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('gateway_id')->nullable();
            $table->string('gateway_slug');
            $table->string('url')->nullable();
            $table->date('due_date');
            $table->json('available_payment_methods');
            $table->unsignedInteger('max_installments');
            $table->json('splits');
            $table->string('status');
            $table->date('paid_at')->nullable();
            $table->date('canceled_at')->nullable();
            $table->date('refunded_at')->nullable();
            $table->bigInteger('refunded_amount')->nullable();
            $table->string('bank_slip_code')->nullable();
            $table->string('pix_code')->nullable();
            $table->foreignUuid('customer_id')
                ->constrained('payment_gateway_customers')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index(['gateway_id', 'gateway_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_invoices');
    }
};
