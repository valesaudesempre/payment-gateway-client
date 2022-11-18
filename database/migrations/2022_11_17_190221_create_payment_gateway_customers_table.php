<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_customers', static function (Blueprint $table) {
            $table->uuid('id');
            $table->string('gateway_id');
            $table->string('gateway_slug');
            $table->string('name');
            $table->string('document_number');
            $table->string('email');
            $table->json('address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('payment_gateway_customers');
    }
};