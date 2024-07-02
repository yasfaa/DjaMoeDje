<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->decimal('total', 8, 2);
            $table->decimal('shipping_cost', 8, 2);
            $table->uuid('transaction_id')->unique();
            $table->string('bsorder_id')->nullable();
            $table->string('waybill_id')->nullable();
            $table->string('status')->default('pending');
            $table->json('courier_details');
            $table->json('items');
            $table->text('payment_link')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
