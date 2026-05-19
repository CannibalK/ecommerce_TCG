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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('collector_number')->nullable();  // "237/271"
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'mythic', 'special'])->default('common');
            $table->string('image_url')->nullable();
            // JSON para atributos específicos de cada juego:
            // MTG: {"colors": ["R","G"], "mana_cost": "{2}{R}", "type": "Creature"}
            // Pokémon: {"type": "Fire", "hp": 120, "stage": "Basic"}
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->index(['set_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
