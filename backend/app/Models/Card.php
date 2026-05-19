<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $fillable = [
        'set_id', 'name', 'slug', 'collector_number',
        'rarity', 'image_url', 'attributes',
    ];

    protected function casts(): array
    {
        return [
            // Laravel convierte el JSON de la DB en array PHP automáticamente
            'attributes' => 'array',
        ];
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
