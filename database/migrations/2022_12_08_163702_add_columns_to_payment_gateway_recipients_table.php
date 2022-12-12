<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_gateway_recipients', static function (Blueprint $table) {
            $table->after('document_number', static function (Blueprint $table) {
                $table->json('address');
                $table->string('phone');
                $table->json('bank_account');
                $table->boolean('automatic_withdrawal');
                $table->json('gateway_specific_data');
            });
        });
    }
};
