<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_payment_methods', static function (Blueprint $table) {
            $table->uuid('id');
            $table->string('gateway_id')->nullable();
            $table->string('gateway_slug');
            $table->string('description');
            $table->json('card');
            $table->boolean('is_default');
            $table->foreignUuid('customer_id')
                ->constrained('payment_gateway_customers')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index(['gateway_id', 'gateway_slug']);
        });
    }

    public function down(): void
    {
        Schema::drop('payment_gateway_payment_methods');
    }
};
