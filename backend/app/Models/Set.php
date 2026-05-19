<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Set extends Model
{
    protected $fillable = ['game_id', 'name', 'code', 'release_date', 'image_url'];

    protected function casts(): array
    {
        return ['release_date' => 'date'];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
