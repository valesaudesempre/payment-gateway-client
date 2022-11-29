<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_webhooks', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('gateway_id')->nullable();
            $table->string('gateway_slug');
            $table->string('event')->nullable();
            $table->json('request');
            $table->json('headers');
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at');
            $table->timestamps();
            $table->index(['gateway_id', 'gateway_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_webhooks');
    }
};
