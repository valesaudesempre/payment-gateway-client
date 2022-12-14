<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_gateway_recipients', static function (Blueprint $table) {
            $table->after('document_number', static function (Blueprint $table) {
                $table->string('representative_name')->nullable();
                $table->string('representative_document_type')->nullable();
                $table->string('representative_document_number')->nullable();
                $table->json('address');
                $table->string('phone');
                $table->json('bank_account');
                $table->boolean('automatic_withdrawal');
                $table->string('status');
                $table->json('gateway_specific_data');
            });
        });
    }
};
