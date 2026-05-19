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
        // Un "product" = una carta física en venta con condición y precio específico
        // La misma carta puede tener múltiples products (NM $5, LP $3, foil $20)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->enum('condition', ['mint', 'near_mint', 'lightly_played', 'moderately_played', 'heavily_played', 'damaged'])->default('near_mint');
            $table->string('language')->default('en');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock')->default(1);
            $table->boolean('is_foil')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['card_id', 'condition', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
