<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_gateway_recipients', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('gateway_id')->nullable();
            $table->string('gateway_slug');
            $table->string('name');
            $table->string('document_type');
            $table->string('document_number');
            $table->nullableUuidMorphs('owner');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['gateway_id', 'gateway_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_recipients');
    }
};
