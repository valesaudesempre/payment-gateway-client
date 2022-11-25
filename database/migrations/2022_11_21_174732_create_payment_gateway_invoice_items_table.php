<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_invoice_items', static function (Blueprint $table) {
            $table->uuid('id');
            $table->string('gateway_id')->nullable();
            $table->string('gateway_slug');
            $table->bigInteger('price');
            $table->unsignedInteger('quantity');
            $table->string('description');
            $table->foreignUuid('invoice_id')
                ->constrained('payment_gateway_invoices')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index(['gateway_id', 'gateway_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_invoice_items');
    }
};
